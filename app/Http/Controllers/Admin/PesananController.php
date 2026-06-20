<?php

namespace App\Http\Controllers\Admin;

use App\Events\StatusPesananDiperbarui;
use App\Events\TugasTeknisiDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Service;
use App\Models\Refill;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\FinalTransactionStockService;
use App\Services\OrderPricingService;
use App\Services\PaidOrderStockService;
use App\Support\AdminPesananData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    private function safelyBroadcast(object $event): void
    {
        try {
            $pending = broadcast($event)->toOthers();
            unset($pending);
        } catch (\Throwable) {
            // Abaikan kegagalan broadcast agar alur transaksi lokal tetap berjalan normal.
        }
    }

    private function greetingByTime(): string
    {
        $hour = (int) now()->setTimezone('Asia/Jakarta')->format('H');
        if ($hour >= 4 && $hour < 11) {
            return 'Selamat pagi';
        }
        if ($hour >= 11 && $hour < 15) {
            return 'Selamat siang';
        }
        if ($hour >= 15 && $hour < 19) {
            return 'Selamat sore';
        }
        return 'Selamat malam';
    }

    private function getNormalOrderTotal(Pesanan $pesanan): float
    {
        return (float) app(OrderPricingService::class)->summarizePesanan($pesanan)['totalPembayaran'];
    }

    private function normalizeMoneyInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/[^\d]/', '', $raw);
        if ($digits === '') {
            return null;
        }

        return (string) (int) $digits;
    }

    private function normalizeCustomerPhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === null) {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    private function normalizeMetodePengiriman(mixed $value): string
    {
        $raw = strtolower(trim((string) $value));

        // Backward compatibility: nilai lama "diantar" sekarang dipetakan ke "diantar_internal".
        if ($raw === 'diantar') {
            $raw = 'diantar_internal';
        }

        $allowed = ['pickup', 'diantar_internal'];
        if (!in_array($raw, $allowed, true)) {
            return 'pickup';
        }

        return $raw;
    }

    private function combineAddress(string $maps, string $detail): string
    {
        $maps = trim($maps);
        $detail = trim($detail);

        if ($maps === '' && $detail === '') {
            return '';
        }

        if ($maps === '') {
            return $detail;
        }

        if ($detail === '') {
            return $maps;
        }

        return $maps . ' | Detail: ' . $detail;
    }

    private function normalizeWhatsappNumber(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        if (!str_starts_with($digits, '62')) {
            return null;
        }

        return $digits;
    }

    private function buildOrderCode(Pesanan $pesanan): string
    {
        return 'TNTI' . $pesanan->tanggal->format('dmY') . 'AJ' . str_pad((string) $pesanan->id, 3, '0', STR_PAD_LEFT);
    }

    private function bankAccounts(): array
    {
        return [
            'bca' => [
                'nama_bank' => 'Bank BCA',
                'no_rekening' => '1234567890',
                'pemilik' => 'PD. Anugrah Utama',
            ],
            'bri' => [
                'nama_bank' => 'Bank BRI',
                'no_rekening' => '0987654321',
                'pemilik' => 'PD. Anugrah Utama',
            ],
            'mandiri' => [
                'nama_bank' => 'Bank Mandiri',
                'no_rekening' => '8877665544',
                'pemilik' => 'PD. Anugrah Utama',
            ],
        ];
    }

    private function syncServiceLogForPackageOrder(Pesanan $pesanan, array $overrides = []): ?Service
    {
        if (!$pesanan->isPackageServiceOrder()) {
            return null;
        }

        $pesanan->loadMissing(['servicePaket.peralatans', 'service']);
        $existingService = $pesanan->service;

        return Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            $pesanan->serviceLogPayload(array_merge([
                'status_konfirmasi' => $existingService?->status_konfirmasi ?: 'pending',
                'actual_peralatan_json' => $existingService?->actual_peralatan_json,
                'catatan_teknisi' => $existingService?->catatan_teknisi,
                'laporan_foto' => $existingService?->laporan_foto,
                'tgl_selesai_admin' => $existingService?->tgl_selesai_admin,
                'stok_kurang_history_json' => $existingService?->stok_kurang_history_json,
            ], $overrides)),
        );
    }

    private function confirmServicePackageInventory(Pesanan $pesanan, InventoryService $inventoryService): void
    {
        if (!$pesanan->isPackageServiceOrder() || (string) $pesanan->status !== Pesanan::STATUS_SELESAI_FINAL) {
            return;
        }

        $pesanan->loadMissing(['servicePaket.peralatans', 'service']);
        $existingService = $pesanan->service;

        if ($existingService?->status_konfirmasi === 'confirmed') {
            return;
        }

        $peralatanItems = $existingService?->effective_peralatan ?: $pesanan->estimatedServicePeralatan();
        $normalizedItems = collect($peralatanItems)
            ->map(function ($item) {
                return [
                    'peralatan_id' => (int) ($item['peralatan_id'] ?? $item['id'] ?? 0),
                    'nama' => (string) ($item['nama'] ?? ''),
                    'jumlah' => (int) ($item['jumlah'] ?? 0),
                ];
            })
            ->filter(fn (array $item) => $item['peralatan_id'] > 0 && $item['jumlah'] > 0)
            ->values()
            ->all();

        if (!empty($existingService?->stok_kurang_history)) {
            $this->syncServiceLogForPackageOrder($pesanan, [
                'actual_peralatan_json' => $existingService?->actual_peralatan_json ?: json_encode($normalizedItems),
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => $existingService?->tgl_selesai_admin ?: now(),
                'stok_kurang_history_json' => $existingService?->stok_kurang_history_json,
            ]);

            return;
        }

        $history = [];

        foreach ($normalizedItems as $item) {
            $peralatan = Peralatan::find($item['peralatan_id']);
            if (!$peralatan) {
                continue;
            }

            $stokSebelum = (float) $peralatan->stok;
            $jumlah = (float) $item['jumlah'];

            $inventoryService->decreasePeralatanStock(
                peralatan: $peralatan,
                qty: $jumlah,
                sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Service APAR - ' . ($pesanan->pelanggan?->nama ?: 'Pelanggan tidak diketahui'),
                tanggal: now(),
            );

            $history[] = [
                'peralatan_id' => $peralatan->id,
                'nama' => $peralatan->nama,
                'jumlah' => (int) $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => (float) $peralatan->fresh()->stok,
            ];
        }

        $this->syncServiceLogForPackageOrder($pesanan, [
            'actual_peralatan_json' => json_encode($normalizedItems),
            'status_konfirmasi' => 'confirmed',
            'tgl_selesai_admin' => now(),
            'stok_kurang_history_json' => json_encode($history),
        ]);
    }

    private function isManualOrder(Pesanan $pesanan): bool
    {
        return in_array((string) $pesanan->sumber_pesanan, ['whatsapp', 'telepon', 'datang_langsung', 'input_admin', 'offline'], true);
    }

    private function isPaymentConfirmed(Pesanan $pesanan): bool
    {
        if (!is_null($pesanan->pembayaran_terkonfirmasi_at)) {
            return true;
        }

        return $pesanan->metode_pembayaran === 'cash'
            && in_array((string) $pesanan->status, ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin', 'siap dikirim', 'selesai final', 'selesai'], true);
    }

    private function canAssignTeknisi(Pesanan $pesanan): bool
    {
        $statusSiapAssign = in_array((string) $pesanan->status, [
            'diproses',
            'disetujui',
            'menunggu diproses admin',
            Pesanan::STATUS_MENUNGGU_PENGAMBILAN,
            Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT,
        ], true);
        if (!$statusSiapAssign || !is_null($pesanan->teknisi_id)) {
            return false;
        }

        if ($this->isManualOrder($pesanan) && !$this->isPaymentConfirmed($pesanan)) {
            return false;
        }

        return true;
    }

    private function linkedPelangganSelection()
    {
        return Pelanggan::query()
            ->visibleInDirectory()
            ->with('user')
            ->orderBy('nama');
    }

    public function index()
    {
        $pendingPaidWeb = Pesanan::query()
            ->where('tipe', 'produk')
            ->where('sumber_pesanan', 'website')
            ->where('status', 'pending')
            ->whereNotNull('bukti_pembayaran')
            ->get();

        foreach ($pendingPaidWeb as $p) {
            $p->update(['status' => 'diproses']);
        }

        $revenuePeriod = AdminPesananData::normalizeRevenuePeriod(request('revenue_period'));
        $pesanans = AdminPesananData::query()->get();
        $pesananDetailData = AdminPesananData::detailData($pesanans);
        $summary = AdminPesananData::summary($revenuePeriod);
        $splitPesanan = AdminPesananData::split($pesanans);
        $pesananAktif = $splitPesanan['aktif'];
        $pesananRiwayat = $splitPesanan['riwayat'];

        return view('admin.pesanan.index', compact(
            'pesanans',
            'summary',
            'revenuePeriod',
            'pesananAktif',
            'pesananRiwayat',
            'pesananDetailData'
        ));
    }

    public function paymentNotifications(Request $request)
    {
        $since = trim((string) $request->query('since', ''));
        $sinceAt = null;

        if ($since !== '') {
            try {
                $sinceAt = Carbon::parse($since);
            } catch (\Throwable) {
                $sinceAt = null;
            }
        }

        $query = Pesanan::query()
            ->with('pelanggan:id,nama,no_wa')
            ->where('tipe', 'produk')
            ->whereNotNull('bukti_pembayaran');

        if ($sinceAt) {
            $query->where('updated_at', '>', $sinceAt);
        }

        $orders = $query
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'server_time' => now()->toIso8601String(),
            'new_count' => $orders->count(),
            'orders' => $orders->map(function (Pesanan $pesanan) {
                return [
                    'id' => (int) $pesanan->id,
                    'kode' => 'TNTI' . $pesanan->tanggal->format('dmY') . 'AJ' . str_pad((string) $pesanan->id, 3, '0', STR_PAD_LEFT),
                    'pelanggan' => (string) ($pesanan->pelanggan?->nama ?? '-'),
                    'total' => (float) ($pesanan->total_harga ?: $pesanan->total),
                    'tipe_harga' => (string) ($pesanan->tipe_harga ?: 'normal'),
                    'updated_at' => optional($pesanan->updated_at)?->toIso8601String(),
                ];
            })->values(),
            'paid_today' => Pesanan::query()
                ->where('tipe', 'produk')
                ->whereNotNull('bukti_pembayaran')
                ->whereDate('updated_at', today())
                ->count(),
        ]);
    }

    public function create()
    {
        return redirect()
            ->route('admin.pesanan.index')
            ->with('error', 'Input manual pembelian unit sudah dinonaktifkan. Transaksi baru harus dibuat pelanggan melalui sistem.');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('admin.pesanan.index')
            ->with('error', 'Input manual pembelian unit sudah dinonaktifkan. Transaksi baru harus dibuat pelanggan melalui sistem.');

        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
                ->values()
                ->all(),
            'pelanggan_id' => $request->input('pelanggan_id'),
            'catatan_admin' => trim((string) $request->input('catatan_admin')) ?: null,
        ]);

        $validated = $request->validate([
            'tipe' => 'required|in:produk',
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'tanggal' => 'required|date',
            'catatan_admin' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produks,id',
            'items.*.kapasitas' => 'required|string|max:100',
            'items.*.merek' => 'required|string|max:100',
            'items.*.jumlah' => 'required|integer|min:1',
        ], [
            'items.required' => 'Tambahkan minimal satu produk sebelum menyimpan pesanan.',
            'items.min' => 'Tambahkan minimal satu produk sebelum menyimpan pesanan.',
            'items.*.produk_id.required' => 'Setiap item produk harus dipilih dengan lengkap.',
            'items.*.kapasitas.required' => 'Kapasitas pada item produk belum dipilih.',
            'items.*.merek.required' => 'Merek pada item produk belum dipilih.',
            'items.*.jumlah.required' => 'Jumlah pada item produk belum diisi.',
            'pelanggan_id.required' => 'Pilih pelanggan terlebih dahulu.',
            'pelanggan_id.exists' => 'Pelanggan yang dipilih tidak valid.',
        ]);

        $pelanggan = Pelanggan::query()
            ->visibleInDirectory()
            ->with('user')
            ->find((int) $validated['pelanggan_id']);

        if (! $pelanggan) {
            throw ValidationException::withMessages([
                'pelanggan_id' => 'Pelanggan belum memiliki akun. Silakan buat akun pelanggan terlebih dahulu melalui menu Manajemen Akun.',
            ]);
        }

        DB::transaction(function () use ($validated, $pelanggan) {
            $pelangganId = (int) $pelanggan->id;

            // --- Create pesanan with offline defaults ---
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelangganId,
                'user_id' => $pelanggan->user_id,
                'nama_penerima' => $pelanggan->nama,
                'nomor_wa_penerima' => $pelanggan->no_wa,
                'alamat_pengiriman' => $pelanggan->alamat,
                'tipe' => 'produk',
                'sumber_pesanan' => 'datang_langsung',
                'is_pesanan_lama' => false,
                'tanggal' => $validated['tanggal'],
                'total' => 0,
                'status' => 'diproses', // skip pending, langsung lunas & diproses
                'tipe_harga' => 'normal',
                'metode_pengiriman' => 'pickup',
                'ongkir' => 0,
                'metode_pembayaran' => 'cash',
                'pembayaran_terkonfirmasi_at' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ] + Pesanan::purchasePriceAttributes([
                'status' => null,
                'requested_price' => null,
                'final_price' => null,
                'customer_note' => null,
                'admin_note' => null,
                'used' => false,
            ]));

            $total = 0;

            foreach ($validated['items'] as $item) {
                $produk = Produk::with('jenisApar')->findOrFail($item['produk_id']);

                if ($produk->kapasitas !== $item['kapasitas'] || $produk->merek !== $item['merek']) {
                    throw ValidationException::withMessages([
                        'items' => 'Pilihan produk, kapasitas, atau merek tidak valid.',
                    ]);
                }

                $jumlah = (int) $item['jumlah'];
                $stokTersedia = (int) $produk->stok_tersedia;

                if ($stokTersedia < $jumlah) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok siap jual untuk produk "' . $produk->nama . '" tidak mencukupi. Tersedia: ' . $stokTersedia,
                    ]);
                }

                $harga = (int) $produk->harga;
                $subtotal = $harga * $jumlah;

                $pesanan->details()->create([
                    'produk_id' => $produk->id,
                    'merek' => $produk->merek,
                    'kapasitas' => $produk->kapasitas ?? '-',
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $pesanan->update([
                'total' => $total,
                'total_harga' => $total,
            ]);

            app(PaidOrderStockService::class)->apply($pesanan->fresh(['details.produk.jenisApar', 'pelanggan', 'unitApars']));

        });

        return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan offline berhasil disimpan. Status: Lunas & Diproses.');
    }

    public function show(Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return redirect()
                ->route('admin.service.index')
                ->with('success', 'Data service dan refill sekarang dikelola dari menu terpisah.');
        }

        $pesanan->load(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk']);
        $showAssignAction = $this->canAssignTeknisi($pesanan);
        $showReadyToShipAction = $pesanan->canMarkReadyToShip();
        $showFinalizeAction = $pesanan->canFinalizeDirectlyByAdmin();

        return view('admin.pesanan.show', compact('pesanan', 'showAssignAction', 'showReadyToShipAction', 'showFinalizeAction'));
    }

    public function approvePurchasePriceRequest(Request $request, Pesanan $pesanan)
    {
        if (!$pesanan->isProductOrder()) {
            return back()->with('error', 'Pengajuan Harga Pembelian hanya berlaku untuk pesanan produk APAR.');
        }

        $pesanan->loadMissing(['details.produk', 'pelanggan']);

        if (!$pesanan->hasPendingPurchasePriceRequest()) {
            return back()->with('error', 'Pengajuan Harga Pembelian ini tidak sedang menunggu persetujuan.');
        }

        $hargaFinal = $this->normalizeMoneyInput($request->input('harga_final'));
        if (is_null($hargaFinal)) {
            $hargaFinal = $pesanan->requestedPurchasePrice();
        }
        $catatanAdmin = trim((string) $request->input('catatan_admin')) ?: null;
        $pricingSummary = app(OrderPricingService::class)->summarizeProductItems($pesanan->details, (float) ($pesanan->ongkir ?? 0));
        $maksimalHargaFinal = (float) ($pricingSummary['totalSetelahPromo'] ?? $pricingSummary['subtotalProduk'] ?? 0);

        if (is_null($hargaFinal) || (float) $hargaFinal <= 0) {
            throw ValidationException::withMessages([
                'harga_final' => 'Harga Final wajib diisi dengan angka yang valid.',
            ]);
        }

        if ((float) $hargaFinal > $maksimalHargaFinal) {
            throw ValidationException::withMessages([
                'harga_final' => 'Harga Final tidak boleh lebih besar dari total setelah promo otomatis.',
            ]);
        }

        $grandTotal = max(0, (float) round((float) $hargaFinal + (float) ($pesanan->ongkir ?? 0), 0));

        $pesanan->update(array_merge([
            'status' => Pesanan::STATUS_DISETUJUI,
            'tipe_harga' => 'deal',
            'catatan_admin' => $catatanAdmin,
            'total' => $grandTotal,
            'total_harga' => $grandTotal,
        ], Pesanan::purchasePriceAttributes([
            'status' => Pesanan::PRICE_REQUEST_APPROVED,
            'final_price' => (float) $hargaFinal,
            'admin_note' => $catatanAdmin,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'normal_subtotal' => (float) ($pricingSummary['subtotalProduk'] ?? 0),
            'discounted_total' => (float) ($pricingSummary['totalSetelahPromo'] ?? 0),
            'initial_total' => (float) ($pricingSummary['totalPembayaran'] ?? 0),
            'used' => false,
        ])));

        $this->safelyBroadcast(new StatusPesananDiperbarui($pesanan->fresh()));

        return back()->with('success', 'Pengajuan Harga Pembelian berhasil di-ACC.');
    }

    public function rejectPurchasePriceRequest(Request $request, Pesanan $pesanan)
    {
        if (!$pesanan->isProductOrder()) {
            return back()->with('error', 'Pengajuan Harga Pembelian hanya berlaku untuk pesanan produk APAR.');
        }

        $pesanan->loadMissing(['details.produk', 'pelanggan']);

        if (!$pesanan->hasPendingPurchasePriceRequest()) {
            return back()->with('error', 'Pengajuan Harga Pembelian ini tidak sedang menunggu persetujuan.');
        }

        $catatanAdmin = trim((string) $request->input('catatan_admin')) ?: null;
        $pricingSummary = app(OrderPricingService::class)->summarizeProductItems($pesanan->details, (float) ($pesanan->ongkir ?? 0));

        $pesanan->update(array_merge([
            'status' => Pesanan::STATUS_PENDING,
            'tipe_harga' => 'normal',
            'catatan_admin' => $catatanAdmin,
            'total' => (float) ($pricingSummary['totalPembayaran'] ?? 0),
            'total_harga' => (float) ($pricingSummary['totalPembayaran'] ?? 0),
        ], Pesanan::purchasePriceAttributes([
            'status' => Pesanan::PRICE_REQUEST_REJECTED,
            'final_price' => null,
            'admin_note' => $catatanAdmin,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'normal_subtotal' => (float) ($pricingSummary['subtotalProduk'] ?? 0),
            'discounted_total' => (float) ($pricingSummary['totalSetelahPromo'] ?? 0),
            'initial_total' => (float) ($pricingSummary['totalPembayaran'] ?? 0),
            'used' => false,
        ])));

        $this->safelyBroadcast(new StatusPesananDiperbarui($pesanan->fresh()));

        return back()->with('success', 'Pengajuan Harga Pembelian berhasil ditolak. Pelanggan dapat melanjutkan transaksi dengan harga normal atau promo otomatis.');
    }

    public function update(Request $request, Pesanan $pesanan)
    {
        $validated = $request->validate([
            'status'          => 'required|in:menunggu,pending,diproses,selesai,ditolak,menunggu diproses admin,ditugaskan ke teknisi,dikerjakan teknisi,selesai oleh teknisi,dikonfirmasi admin,siap dikirim,selesai final,permintaan masuk,direview admin,menunggu penjadwalan,menunggu persetujuan biaya,disetujui',
        ]);

        $pesanan->status = $validated['status'];

        $becameFinal = $pesanan->isDirty('status') && $pesanan->status === Pesanan::STATUS_SELESAI_FINAL;
        $pesanan->save();

        if ($becameFinal) {
            app(FinalTransactionStockService::class)->apply($pesanan);
        } elseif ($pesanan->isPaymentConfirmed() && !$pesanan->stok_dikurangi) {
            app(PaidOrderStockService::class)->apply($pesanan);
        }

        // Broadcast status terbaru ke admin dan teknisi
        $this->safelyBroadcast(new StatusPesananDiperbarui($pesanan));

        if (in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true)) {
            $pesanan->pelanggan?->update(['status' => 'tetap']);
        }

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    // negoAction removed

    public function kirimLinkPembayaran(Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return back()->with('error', 'Link pembayaran hanya tersedia untuk pesanan produk.');
        }

        if (!$this->isManualOrder($pesanan)) {
            return back()->with('error', 'Aksi ini hanya untuk pesanan input admin atau offline.');
        }

        if ($this->isPaymentConfirmed($pesanan)) {
            return back()->with('error', 'Pembayaran pesanan ini sudah terkonfirmasi.');
        }

        if (!empty($pesanan->bukti_pembayaran)) {
            return back()->with('error', 'Bukti pembayaran sudah masuk. Lanjut verifikasi pembayaran.');
        }

        $noWa = $this->normalizeWhatsappNumber($pesanan->pelanggan?->no_wa);
        if ($noWa === null) {
            return back()->with('error', 'Nomor WhatsApp pelanggan belum valid.');
        }

        $pesanan->update([
            'status' => 'pending',
            'link_pembayaran_terkirim_at' => now(),
        ]);

        $namaPelanggan = (string) ($pesanan->pelanggan?->nama ?? 'Pelanggan');
        $kodePesanan = $this->buildOrderCode($pesanan);
        $totalTagihan = number_format($this->getNormalOrderTotal($pesanan), 0, ',', '.');
        $banks = $this->bankAccounts();
        $bankLines = collect($banks)->map(function (array $bank) {
            return "- {$bank['nama_bank']}: {$bank['no_rekening']} a.n. {$bank['pemilik']}";
        })->implode("\n");

        $message = "Halo Bapak/Ibu {$namaPelanggan}, berikut detail pembayaran untuk pesanan Anda.\n\n"
            . "Nomor Pesanan: {$kodePesanan}\n"
            . "Total Tagihan: Rp {$totalTagihan}\n"
            . "Silakan transfer ke salah satu rekening berikut:\n{$bankLines}\n\n"
            . "Setelah transfer, mohon kirimkan bukti pembayaran agar pesanan dapat kami verifikasi dan diproses lebih lanjut.\nTerima kasih.";

        $waUrl = \App\Support\WhatsApp::customerLink($noWa, $message);

        return back()
            ->with('success', 'Detail pembayaran siap dikirim ke pelanggan.')
            ->with('wa_url', $waUrl)
            ->with('wa_title', 'Detail pembayaran berhasil disiapkan.')
            ->with('wa_description', 'Kirim pesan ini ke pelanggan agar bisa lanjut transfer.')
            ->with('wa_button', 'Kirim Detail Pembayaran');
    }

    public function inputBuktiPembayaranManual(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return back()->with('error', 'Input bukti pembayaran hanya tersedia untuk pesanan produk.');
        }

        if (!$this->isManualOrder($pesanan)) {
            return back()->with('error', 'Aksi ini hanya untuk pesanan input admin atau offline.');
        }

        if (is_null($pesanan->link_pembayaran_terkirim_at)) {
            return back()->with('error', 'Kirim detail pembayaran ke pelanggan terlebih dahulu.');
        }

        $validator = validator($request->all(), [
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first('bukti_pembayaran'));
        }

        $proofPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $pesanan->update([
            'status' => 'pending',
            'metode_pembayaran' => 'transfer',
            'bukti_pembayaran' => $proofPath,
            'pembayaran_terkonfirmasi_at' => null,
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diinput. Status menunggu verifikasi pembayaran.');
    }

    public function konfirmasiPembayaranManual(Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return back()->with('error', 'Konfirmasi pembayaran offline hanya untuk pesanan produk.');
        }

        if (!$this->isManualOrder($pesanan)) {
            return back()->with('error', 'Aksi ini hanya untuk pesanan input admin atau offline.');
        }

        if ($this->isPaymentConfirmed($pesanan)) {
            return back()->with('success', 'Pembayaran pesanan ini sudah terkonfirmasi sebelumnya.');
        }

        if (empty($pesanan->bukti_pembayaran)) {
            return back()->with('error', 'Tandai lunas tersedia setelah bukti pembayaran diinput.');
        }

        try {
            $pesanan->update([
                'status' => 'diproses',
                'metode_pembayaran' => $pesanan->metode_pembayaran ?: 'transfer',
                'total_harga' => $pesanan->total_harga ?: $pesanan->total,
                'pembayaran_terkonfirmasi_at' => now(),
            ]);

            app(PaidOrderStockService::class)->apply($pesanan->fresh());
            $pesanan->pelanggan?->update(['status' => 'tetap']);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi. Pesanan siap di-assign.');
    }

    public function assignTeknisi(Request $request, Pesanan $pesanan)
    {
        if (!$this->canAssignTeknisi($pesanan)) {
            return back()->with('error', 'Assign teknisi tersedia setelah pembayaran dikonfirmasi.');
        }

        // Auto-select teknisi: prioritize teknisi with least active assignments
        $teknisi = User::where('role', 'teknisi')
            ->get()
            ->sortBy(function ($tek) {
                return $tek->pesanans()->whereIn('status', ['ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
            })
            ->first();

        if (!$teknisi) {
            return back()->with('error', 'Belum ada teknisi aktif yang bisa ditugaskan.');
        }

        $pesanan->update([
            'teknisi_id' => $teknisi->id,
            'status' => 'ditugaskan ke teknisi',
            'teknisi_selesai_at' => null,
            'teknisi_catatan' => null,
        ]);

        // Broadcast ke admin dan teknisi yang di-assign bila kanal realtime tersedia.
        $this->safelyBroadcast(new TugasTeknisiDiperbarui($pesanan->fresh()));

        return back()->with('success', 'Berhasil ditugaskan ke teknisi: ' . $teknisi->name . '.');
    }

    public function konfirmasiKePelanggan(Pesanan $pesanan)
    {
        if (!$pesanan->canMarkReadyToShip()) {
            return back()->with('error', 'Pesanan belum siap diubah ke status Siap Dikirim.');
        }

        $noWa = preg_replace('/\D/', '', (string) ($pesanan->pelanggan?->no_wa ?? ''));
        if ($noWa === '') {
            return back()->with('error', 'Nomor WhatsApp pelanggan tidak tersedia.');
        }
        if (str_starts_with($noWa, '0')) {
            $noWa = '62' . substr($noWa, 1);
        }

        $sapaan = $this->greetingByTime();
        $nama = (string) ($pesanan->pelanggan?->nama ?? 'Pelanggan');
        $message = "{$sapaan}, Bapak/Ibu {$nama}. Pesanan atau pekerjaan Anda sudah siap dikirim. Setelah barang atau unit diterima, silakan buka riwayat transaksi Anda dan klik tombol Konfirmasi Pesanan Diterima. Terima kasih.";

        $pesanan->update([
            'status' => Pesanan::STATUS_SIAP_DIKIRIM,
        ]);

        return back()
            ->with('success', 'Status pesanan diubah ke Siap Dikirim dan pesan WhatsApp siap dikirim.')
            ->with('wa_url', \App\Support\WhatsApp::customerLink($noWa, $message));
    }

    public function selesaiFinal(Pesanan $pesanan)
    {
        if ($pesanan->requiresCustomerDeliveryConfirmation()) {
            return back()->with('error', 'Pesanan yang perlu dikirim ke pelanggan harus melalui status Siap Dikirim dan konfirmasi diterima oleh pelanggan.');
        }

        if (!$pesanan->canFinalizeDirectlyByAdmin()) {
            return back()->with('error', 'Status pesanan belum siap untuk finalisasi.');
        }

        try {
            DB::transaction(function () use ($pesanan) {
                $pesanan->update([
                    'status' => 'selesai final',
                ]);

                app(FinalTransactionStockService::class)->apply($pesanan);

                $pesanan->pelanggan?->update([
                    'status' => 'tetap',
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Pesanan berhasil diselesaikan final oleh admin.');
    }

    private function resolveAdminPesananRedirect(Request $request): string
    {
        $fallbackRoute = route('admin.pesanan.index');
        $previousUrl = $request->headers->get('referer');

        if ($previousUrl && str_starts_with($previousUrl, url('/'))) {
            return $previousUrl;
        }

        return $fallbackRoute;
    }

    private function deleteStoredOrderFiles(Pesanan $pesanan): void
    {
        $files = collect([
            $pesanan->bukti_pembayaran,
            $pesanan->service_foto,
        ])->filter();

        foreach ($files as $file) {
            Storage::disk('public')->delete(ltrim((string) $file, '/'));
        }
    }

    public function hideHistoryOrder(Request $request, string $jenis, Pesanan $pesanan)
    {
        $redirectTarget = $this->resolveAdminPesananRedirect($request);

        if (!$pesanan->matchesAdminDestroyType($jenis)) {
            return redirect()->to($redirectTarget);
        }

        if ($pesanan->isActiveOrder()) {
            return redirect()->to($redirectTarget)->with('error', 'Transaksi aktif harus dihapus melalui aksi Hapus Pesanan Aktif.');
        }

        if (!$pesanan->hideFromAdminPesananMenu()) {
            return redirect()->to($redirectTarget)->with('error', 'Fitur sembunyikan transaksi belum siap digunakan.');
        }

        return redirect()->to($redirectTarget)->with('success', 'Riwayat transaksi berhasil disembunyikan dari menu Pesanan.');
    }

    public function deleteActiveOrder(Request $request, string $jenis, Pesanan $pesanan)
    {
        $redirectTarget = $this->resolveAdminPesananRedirect($request);

        if (!$pesanan->matchesAdminDestroyType($jenis)) {
            return redirect()->to($redirectTarget);
        }

        if (!$pesanan->isActiveOrder()) {
            return redirect()->to($redirectTarget)->with('error', 'Riwayat transaksi tidak dihapus penuh. Gunakan aksi Sembunyikan Riwayat.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->loadMissing(['complain', 'details.produk', 'service.refill', 'unitApars.produk']);

            if ($pesanan->stok_dikurangi) {
                app(PaidOrderStockService::class)->rollback($pesanan);
                $pesanan->refresh()->loadMissing(['complain', 'details.produk', 'service.refill', 'unitApars.produk']);
            }

            $this->deleteStoredOrderFiles($pesanan);

            $pesanan->unitApars()->delete();
            $pesanan->complain?->delete();
            $pesanan->details()->delete();

            if ($pesanan->service) {
                $pesanan->service->refill()?->delete();
                $pesanan->service->delete();
            }

            $pesanan->delete();
        });

        return redirect()->to($redirectTarget)->with('success', 'Pesanan aktif berhasil dibatalkan dan dihapus.');
    }

    public function hide(Request $request, string $jenis, Pesanan $pesanan)
    {
        return $this->hideHistoryOrder($request, $jenis, $pesanan);
    }

    public function destroyTyped(Request $request, string $jenis, Pesanan $pesanan)
    {
        return $this->deleteActiveOrder($request, $jenis, $pesanan);
    }

    public function destroy(Request $request, Pesanan $pesanan)
    {
        $slug = $pesanan->adminDestroyTypeSlug();

        return $pesanan->isActiveOrder()
            ? $this->deleteActiveOrder($request, $slug, $pesanan)
            : $this->hideHistoryOrder($request, $slug, $pesanan);
    }

    public function invoicePdf(Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return redirect()
                ->route('admin.service.index')
                ->with('success', 'Invoice pesanan sekarang khusus pembelian produk.');
        }

        $pesanan->load(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk']);

        return Pdf::loadView('admin.pesanan.pdf.invoice', compact('pesanan'))
            ->download('invoice-pesanan-'.$pesanan->id.'.pdf');
    }

    protected function syncMissingUnitApars(): void
    {
        Pesanan::with(['details.produk.jenisApar', 'unitApars'])
            ->where('tipe', 'produk')
            ->where('status', 'selesai final')
            ->get()
            ->each(fn (Pesanan $pesanan) => $this->syncUnitAparsForPesanan($pesanan));
    }

    protected function syncUnitAparsForPesanan(Pesanan $pesanan): void
    {
        if ($pesanan->tipe !== 'produk' || !in_array($pesanan->status, ['selesai final'], true)) {
            return;
        }

        $pesanan->loadMissing(['details.produk.jenisApar', 'unitApars']);

        if (UnitApar::supportsDatabaseColumn('hidden_at')) {
            $pesanan->unitApars()->whereNotNull('hidden_at')->update(['hidden_at' => null]);
            $pesanan->load('unitApars');
        }

        foreach ($pesanan->details as $detail) {
            $existingCount = $pesanan->unitApars
                ->where('produk_id', $detail->produk_id)
                ->count();

            $missingCount = max(0, ((int) $detail->jumlah) - $existingCount);

            if ($missingCount <= 0 || ! $detail->produk) {
                continue;
            }

            $this->createUnitAparsFromDetail($pesanan, $detail->produk, $missingCount, $existingCount + 1);
            $pesanan->load('unitApars');
        }
    }

    protected function createUnitAparsFromDetail(Pesanan $pesanan, Produk $produk, int $jumlah, int $startFrom = 1): void
    {
        for ($urutan = $startFrom; $urutan < $startFrom + $jumlah; $urutan++) {
            $serial = $this->generateSerialNumber($pesanan, $produk, $urutan);

            UnitApar::create([
                'pelanggan_id' => $pesanan->pelanggan_id,
                'pesanan_id' => $pesanan->id,
                'produk_id' => $produk->id,
                'no_seri' => $serial,
                'tgl_beli' => $pesanan->tanggal,
                'tgl_produksi' => $pesanan->tanggal,
                'ukuran' => $produk->kapasitas ?? '-',
                'bahan' => $produk->jenisApar?->nama ?? '-',
                'tgl_expired' => UnitApar::calculateExpiry($pesanan->tanggal, $produk->kapasitas ?? '-', $produk->jenisApar?->nama ?? '-'),
            ]);
        }
    }

    protected function generateSerialNumber(Pesanan $pesanan, Produk $produk, int $urutan): string
    {
        $pesanan->loadMissing('pelanggan');
        return UnitApar::generateSerialNumber($pesanan->pelanggan, $pesanan->tanggal);
    }

    protected function buildJenisAparCode(?string $jenisApar): string
    {
        $jenis = strtolower((string) $jenisApar);

        if (str_contains($jenis, 'co2') || str_contains($jenis, 'carbon')) {
            return 'CO2';
        }
        if (str_contains($jenis, 'powder')) {
            return 'PWD';
        }
        if (str_contains($jenis, 'foam')) {
            return 'FOM';
        }

        $fallback = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $jenisApar) ?: 'APR');
        return substr($fallback, 0, 3);
    }

    protected function buildUkuranCode(?string $kapasitas): string
    {
        $ukuran = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $kapasitas) ?: '');
        return $ukuran !== '' ? $ukuran : 'UNK';
    }

    protected function generateNegoCode(): string
    {
        if (!Pesanan::supportsDatabaseColumn('kode_nego')) {
            return 'ANUTA-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        }

        for ($i = 0; $i < 2000; $i++) {
            $candidate = 'ANUTA-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            if (!Pesanan::where('kode_nego', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal generate kode negosiasi unik. Silakan coba lagi.');
    }
}
