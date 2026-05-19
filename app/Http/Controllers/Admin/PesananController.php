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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
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
        $subtotalBarang = (float) $pesanan->details()->sum('subtotal');
        $ongkir = (float) ($pesanan->ongkir ?? 0);

        if (!is_null($pesanan->total) && (float) $pesanan->total > 0) {
            return (float) $pesanan->total;
        }

        return $subtotalBarang + $ongkir;
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
        if (!$pesanan->isPackageServiceOrder()) {
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
                keterangan: "Pemakaian {$peralatan->nama} untuk request service #{$pesanan->id}",
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
            && in_array((string) $pesanan->status, ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin', 'selesai final', 'selesai'], true);
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
            $p->reduceStock();
        }

        $this->syncMissingUnitApars();
        $processPelangganId = request()->integer('process_pelanggan');
        $openNego = request()->boolean('open_nego');

        $pesanans = Pesanan::with(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk'])
            ->where('tipe', 'produk')
            ->latest('tanggal')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $targetNegoPesanan = null;
        if ($processPelangganId > 0) {
            $targetNegoPesanan = Pesanan::with(['details.produk'])
                ->where('tipe', 'produk')
                ->where('pelanggan_id', $processPelangganId)
                ->whereIn('status', ['menunggu', 'menunggu persetujuan', 'pending', 'diproses'])
                ->whereNull('kode_nego')
                ->latest('tanggal')
                ->first();
        }

        $autoOpenNegoId = ($openNego && $targetNegoPesanan) ? (int) $targetNegoPesanan->id : null;
        $prefillItems = old('items');
        if (!$prefillItems && $targetNegoPesanan && $targetNegoPesanan->details->count()) {
            $prefillItems = $targetNegoPesanan->details->map(function ($detail) {
                return [
                    'produk_id' => (int) $detail->produk_id,
                    'kapasitas' => (string) ($detail->kapasitas ?? ($detail->produk?->kapasitas ?? '')),
                    'merek' => (string) ($detail->merek ?? ($detail->produk?->merek ?? '')),
                    'jumlah' => (int) $detail->jumlah,
                ];
            })->values()->all();
        }
        if (!$prefillItems) {
            $prefillItems = [['produk_id' => '', 'kapasitas' => '', 'merek' => '', 'jumlah' => 1]];
        }

        $pelanggans = Pelanggan::orderBy('nama')->get();
        $produks = Produk::with(['jenisApar', 'stokBatches'])->orderBy('nama')->get();
        $produkCatalog = $produks->map(fn ($produk) => [
            'id' => $produk->id,
            'nama' => $produk->nama,
            'kapasitas' => $produk->kapasitas,
            'merek' => $produk->merek,
            'harga' => (int) $produk->harga,
            'jenis' => $produk->jenisApar?->nama ?: 'Tanpa Jenis',
            'stok' => (int) $produk->stok_tersedia,
        ])->values();

        $teknisis = \App\Models\User::where('role', 'teknisi')->orderBy('name')->get();

        return view('admin.pesanan.index', compact(
            'pesanans',
            'pelanggans',
            'produks',
            'produkCatalog',
            'processPelangganId',
            'prefillItems',
            'targetNegoPesanan',
            'autoOpenNegoId',
            'teknisis'
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
        return redirect()->route('admin.pesanan.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn ($item) => collect($item)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
                ->values()
                ->all(),
            'new_pelanggan_nama' => trim((string) $request->input('new_pelanggan_nama')) ?: null,
            'new_pelanggan_no_wa' => $this->normalizeCustomerPhone($request->input('new_pelanggan_no_wa')),
            'catatan_admin' => trim((string) $request->input('catatan_admin')) ?: null,
        ]);

        $validated = $request->validate([
            'tipe' => 'required|in:produk',
            'new_pelanggan_nama' => 'required|string|max:255',
            'new_pelanggan_no_wa' => 'required|string|max:20',
            'new_pelanggan_alamat' => 'nullable|string|max:1000',
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
            'new_pelanggan_nama.required' => 'Nama pelanggan wajib diisi.',
            'new_pelanggan_no_wa.required' => 'Nomor telepon pelanggan wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            // --- Resolve pelanggan: find by phone or create new ---
            $normalizedNoWa = (string) ($validated['new_pelanggan_no_wa'] ?? '');
            $existingPelanggan = Pelanggan::where('no_wa', $normalizedNoWa)->first();

            if ($existingPelanggan) {
                $existingPelanggan->update([
                    'nama' => (string) $validated['new_pelanggan_nama'],
                    'alamat' => filled($validated['new_pelanggan_alamat'] ?? null)
                        ? (string) $validated['new_pelanggan_alamat']
                        : $existingPelanggan->alamat,
                    'status' => 'tetap',
                    'sumber_data' => $existingPelanggan->sumber_data ?: 'manual',
                ]);
                $pelangganId = (int) $existingPelanggan->id;
            } else {
                $pelangganBaru = Pelanggan::create([
                    'nama' => (string) $validated['new_pelanggan_nama'],
                    'no_wa' => $normalizedNoWa,
                    'alamat' => $validated['new_pelanggan_alamat'] ?? null,
                    'status' => 'tetap',
                    'sumber_data' => 'manual',
                    'kategori_pelanggan' => 'baru_manual',
                ]);
                $pelangganId = (int) $pelangganBaru->id;
            }

            // --- Create pesanan with offline defaults ---
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelangganId,
                'tipe' => 'produk',
                'sumber_pesanan' => 'datang_langsung',
                'is_pesanan_lama' => false,
                'tanggal' => $validated['tanggal'],
                'total' => 0,
                'status' => 'diproses', // skip pending, langsung lunas & diproses
                'is_nego' => false,
                'tipe_harga' => 'normal',
                'metode_pengiriman' => 'pickup',
                'ongkir' => 0,
                'metode_pembayaran' => 'cash',
                'pembayaran_terkonfirmasi_at' => now(),
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

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

                $detail = $pesanan->details()->create([
                    'produk_id' => $produk->id,
                    'merek' => $produk->merek,
                    'kapasitas' => $produk->kapasitas ?? '-',
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);

                $this->createUnitAparsFromDetail($pesanan, $produk, $detail->jumlah);

                $total += $subtotal;
            }

            $pesanan->update([
                'total' => $total,
                'total_harga' => $total,
            ]);

            // Langsung kurangi stok karena offline = lunas
            $pesanan->reduceStock();
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

        $this->syncUnitAparsForPesanan($pesanan->loadMissing('details.produk.jenisApar', 'unitApars'));
        $pesanan->load(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk']);

        return view('admin.pesanan.show', compact('pesanan'));
    }

    public function update(Request $request, Pesanan $pesanan)
    {
        $validated = $request->validate([
            'status'          => 'required|in:menunggu,menunggu persetujuan,pending,diproses,selesai,ditolak,menunggu diproses admin,ditugaskan ke teknisi,dikerjakan teknisi,selesai oleh teknisi,dikonfirmasi admin,selesai final,permintaan masuk,direview admin,menunggu penjadwalan,menunggu persetujuan biaya,disetujui',
            'terima_negosiasi'=> 'nullable|boolean',
        ]);

        if ($request->has('terima_negosiasi') && $request->terima_negosiasi) {
            $pesanan->total = $pesanan->harga_usulan ?? $pesanan->total;
            $pesanan->status = 'diproses';
        } else {
            $pesanan->status = $validated['status'];
        }

        if ($pesanan->isDirty('status') && in_array($pesanan->status, ['diproses', 'selesai', 'selesai final', 'ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin'], true) && $pesanan->tipe === 'produk') {
            $pesanan->reduceStock();
        }

        $pesanan->save();

        // Broadcast status terbaru ke admin dan teknisi
        broadcast(new StatusPesananDiperbarui($pesanan))->toOthers();

        if (in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true)) {
            $pesanan->pelanggan?->update(['status' => 'tetap']);
        }

        if (in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true) && $pesanan->tipe === 'produk') {
            $this->syncUnitAparsForPesanan($pesanan);
        }

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * ACC atau TOLAK negosiasi (dipanggil dari modal detail di halaman index).
     */
    public function negoAction(Request $request, Pesanan $pesanan)
    {
        $action = $request->input('action'); // 'acc' | 'tolak'
        $isManualOrder = $this->isManualOrder($pesanan);

        if ($action === 'acc') {
            $request->merge([
                'harga_final' => $this->normalizeMoneyInput($request->input('harga_final')),
            ]);

            $request->validate([
                'harga_final'  => 'required|numeric|min:0',
            ]);

            $kodeNego = $this->generateNegoCode();
            $hargaFinal = (float) $request->harga_final;
            $totalNormal = $this->getNormalOrderTotal($pesanan);

            if ($hargaFinal > $totalNormal) {
                return back()
                    ->withInput($request->all() + ['nego_modal_id' => $pesanan->id])
                    ->withErrors(['harga_final' => 'Harga deal tidak boleh lebih besar dari total akhir normal (termasuk ongkir).']);
            }

            if ($isManualOrder) {
                $pesanan->update([
                    'status'      => 'pending',
                    'is_nego'     => true,
                    'kode_nego'   => null,
                    'kode_nego_terpakai_at' => null,
                    'harga_usulan'=> $hargaFinal,
                    'harga_penawaran_pelanggan' => $pesanan->harga_penawaran_pelanggan,
                    'total_harga' => $hargaFinal,
                    'tipe_harga'  => 'deal',
                ]);

                return redirect()->back()->with('success', 'Harga deal offline di-ACC. Pesanan tidak membutuhkan kode negosiasi.');
            }

            $pesanan->update([
                'status'      => 'pending',
                'is_nego'     => true,
                'kode_nego'   => $kodeNego,
                'kode_nego_terpakai_at' => null,
                'harga_usulan'=> $hargaFinal,
                'harga_penawaran_pelanggan' => $pesanan->harga_penawaran_pelanggan ?: $pesanan->harga_usulan,
                'total_harga' => $hargaFinal,
                'tipe_harga'  => 'deal',
            ]);

            $waNumber = preg_replace('/^0/', '62', (string) ($pesanan->pelanggan?->no_wa ?? ''));
            $waMessage = "Halo {$pesanan->pelanggan?->nama}, negosiasi Anda sudah di-ACC.\n"
                . "Kode Negosiasi: {$kodeNego}\n"
                . "Harga Deal: Rp " . number_format($hargaFinal, 0, ',', '.') . "\n"
                . "Status: Menunggu pembayaran.\n"
                . "Silakan masukkan kode di website untuk lanjut transaksi.";
            $waUrl = $waNumber ? ('https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage)) : null;

            $redirect = redirect()->back()
                ->with('success', 'Negosiasi di-ACC. Kode: ' . $kodeNego)
                ->with('wa_title', 'Kode negosiasi berhasil dibuat.')
                ->with('wa_description', 'Kirim kode ke pelanggan agar bisa dipakai di form website.')
                ->with('wa_button', 'Kirim Kode via WhatsApp');
            if ($waUrl) {
                $redirect->with('wa_url', $waUrl);
            }

            return $redirect;
        }

        if ($action === 'tolak') {
            $pesanan->update(['status' => 'ditolak']);
            return redirect()->back()->with('success', 'Negosiasi pesanan #' . $pesanan->id . ' ditolak.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenali.');
    }

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

        $waUrl = 'https://wa.me/' . $noWa . '?text=' . rawurlencode($message);

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

        $pesanan->update([
            'status' => 'diproses',
            'metode_pembayaran' => $pesanan->metode_pembayaran ?: 'transfer',
            'total_harga' => $pesanan->total_harga ?: $pesanan->total,
            'pembayaran_terkonfirmasi_at' => now(),
        ]);

        $pesanan->reduceStock();

        $pesanan->pelanggan?->update(['status' => 'tetap']);
        $this->syncUnitAparsForPesanan($pesanan->fresh(['details.produk.jenisApar', 'unitApars']));

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi. Pesanan siap di-assign.');
    }

    public function assignTeknisi(Request $request, Pesanan $pesanan)
    {
        if (!$this->canAssignTeknisi($pesanan)) {
            return back()->with('error', 'Assign teknisi tersedia setelah pembayaran dikonfirmasi.');
        }

        $teknisi = User::where('role', 'teknisi')->first();
        if (!$teknisi) {
            return back()->with('error', 'Data teknisi belum tersedia.');
        }

        $pesanan->update([
            'teknisi_id' => $teknisi->id,
            'status' => 'ditugaskan ke teknisi',
            'teknisi_selesai_at' => null,
            'teknisi_catatan' => null,
        ]);

        // Broadcast ke admin dan teknisi yang di-assign
        broadcast(new TugasTeknisiDiperbarui($pesanan->fresh()))->toOthers();

        return back()->with('success', 'Tugas berhasil dikirim ke Teknisi: ' . $teknisi->name . '.');
    }

    public function konfirmasiKePelanggan(Pesanan $pesanan)
    {
        if (!$pesanan->teknisi_selesai_at) {
            return back()->with('error', 'Tugas belum selesai oleh teknisi.');
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
        $message = "{$sapaan}, Bapak/Ibu {$nama}. Kami dari PD. Anugrah Utama ingin menginformasikan bahwa pesanan atau pekerjaan Anda telah selesai ditangani oleh tim kami. Silakan dicek kembali. Jika ada kendala, silakan balas pesan ini. Terima kasih.";

        $pesanan->update([
            'status' => 'dikonfirmasi admin',
        ]);

        return back()
            ->with('success', 'Konfirmasi pelanggan siap dikirim lewat WhatsApp.')
            ->with('wa_url', 'https://wa.me/' . $noWa . '?text=' . rawurlencode($message));
    }

    public function selesaiFinal(Pesanan $pesanan)
    {
        if (!in_array((string) $pesanan->status, ['selesai oleh teknisi', 'dikonfirmasi admin'], true)) {
            return back()->with('error', 'Status pesanan belum siap untuk finalisasi.');
        }

        try {
            DB::transaction(function () use ($pesanan) {
                $inventoryService = app(InventoryService::class);

                if ($pesanan->tipe === 'service') {
                    // Untuk service paket (bukan refill), konfirmasi inventory
                    if ($pesanan->isPackageServiceOrder()) {
                        $this->confirmServicePackageInventory($pesanan, $inventoryService);
                    }

                    // Untuk refill, buat record Refill di tabel refills
                    if ($pesanan->service_jenis_layanan === 'refill' && $pesanan->service_jenis_refill_id) {
                        $unitAparId = null;

                        // Cek apakah ada unit APAR yang dipilih untuk service ini
                        $firstUnit = $pesanan->unitApars()->first();
                        if ($firstUnit) {
                            $unitAparId = $firstUnit->id;
                        } else {
                            // Fallback: cari unit APAR dari riwayat pembelian pelanggan
                            // berdasarkan ukuran dan jenis APAR
                            $unitApar = UnitApar::where('pelanggan_id', $pesanan->pelanggan_id)
                                ->where(function ($q) use ($pesanan) {
                                    $q->where('ukuran', $pesanan->service_ukuran_apar)
                                        ->orWhereRaw("CONCAT(ukuran, '') LIKE ?", ['%' . ($pesanan->service_ukuran_apar ?? '') . '%']);
                                })
                                ->orderByDesc('tgl_beli')
                                ->first();

                            if (!$unitApar) {
                                // Buat unit APAR baru jika tidak ada yang cocok
                                $produkKecil = Produk::where('kapasitas', $pesanan->service_ukuran_apar)->first();
                                if ($produkKecil) {
                                    $unitApar = UnitApar::create([
                                        'pelanggan_id' => $pesanan->pelanggan_id,
                                        'pesanan_id' => $pesanan->id,
                                        'produk_id' => $produkKecil->id,
                                        'no_seri' => 'AUTO-' . $pesanan->id . '-' . time(),
                                        'tgl_beli' => $pesanan->tanggal,
                                        'tgl_produksi' => $pesanan->tanggal,
                                        'ukuran' => $pesanan->service_ukuran_apar,
                                        'bahan' => $pesanan->service_jenis_apar ?? 'APAR',
                                        'tgl_expired' => now()->addYear(),
                                    ]);
                                }
                            }

                            $unitAparId = $unitApar?->id;
                        }

                        if ($unitAparId) {
                            $service = Service::updateOrCreate(
                                ['pesanan_id' => $pesanan->id],
                                [
                                    'unit_apar_id' => $unitAparId,
                                    'jenis_service' => 'Refill APAR',
                                    'tgl_service' => $pesanan->tanggal,
                                    'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                                    'status_konfirmasi' => 'confirmed',
                                ]
                            );

                            Refill::updateOrCreate(
                                ['service_id' => $service->id],
                                [
                                    'unit_apar_id' => $unitAparId,
                                    'jenis_refill_id' => $pesanan->service_jenis_refill_id,
                                    'tgl_refill' => $pesanan->tanggal,
                                    'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                                ]
                            );

                            // Update tanggal expired unit APAR
                            $unitApar = UnitApar::find($unitAparId);
                            if ($unitApar && ($unitApar->tgl_expired === null || $unitApar->tgl_expired->isPast())) {
                                $unitApar->update(['tgl_expired' => now()->addYear()]);
                            }
                        }
                    }
                }

                $pesanan->update([
                    'status' => 'selesai final',
                ]);

                if ($pesanan->tipe === 'produk') {
                    $this->syncUnitAparsForPesanan($pesanan);
                }

                $pesanan->pelanggan?->update([
                    'status' => 'tetap',
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Pesanan berhasil diselesaikan final oleh admin.');
    }

    public function destroy(Request $request, Pesanan $pesanan)
    {
        $fallbackRoute = $pesanan->tipe === 'service'
            ? route('admin.service.index')
            : route('admin.pesanan.index');

        $pesanan->delete();

        $previousUrl = $request->headers->get('referer');

        if ($previousUrl && str_starts_with($previousUrl, url('/'))) {
            return redirect()->to($previousUrl)->with('success', 'Pesanan berhasil dihapus.');
        }

        return redirect()->to($fallbackRoute)->with('success', 'Pesanan berhasil dihapus.');
    }

    public function invoicePdf(Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'produk') {
            return redirect()
                ->route('admin.service.index')
                ->with('success', 'Invoice pesanan sekarang khusus pembelian produk.');
        }

        $this->syncUnitAparsForPesanan($pesanan->loadMissing('details.produk.jenisApar', 'unitApars'));
        $pesanan->load(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk']);

        return Pdf::loadView('admin.pesanan.pdf.invoice', compact('pesanan'))
            ->download('invoice-pesanan-'.$pesanan->id.'.pdf');
    }

    protected function syncMissingUnitApars(): void
    {
        Pesanan::with(['details.produk.jenisApar', 'unitApars'])
            ->where('tipe', 'produk')
            ->get()
            ->each(fn (Pesanan $pesanan) => $this->syncUnitAparsForPesanan($pesanan));
    }

    protected function syncUnitAparsForPesanan(Pesanan $pesanan): void
    {
        if ($pesanan->tipe !== 'produk' || !in_array($pesanan->status, ['diproses', 'selesai', 'selesai final'], true)) {
            return;
        }

        $pesanan->loadMissing(['details.produk.jenisApar', 'unitApars']);

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
        for ($i = 0; $i < 2000; $i++) {
            $candidate = 'ANUTA-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            if (!Pesanan::where('kode_nego', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal generate kode negosiasi unik. Silakan coba lagi.');
    }
}
