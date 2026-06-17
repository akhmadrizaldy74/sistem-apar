<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\UnitApar;
use App\Models\Peralatan;
use App\Models\StockMovement;
use App\Services\FinalTransactionStockService;
use App\Services\InventoryService;
use App\Services\ServiceMasterSyncService;
use App\Services\ServicePackagePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    private function linkedPelangganSelection()
    {
        return Pelanggan::query()
            ->visibleInDirectory()
            ->with(['user', 'units.produk.jenisApar'])
            ->orderBy('nama');
    }

    public function index(
        ServicePackagePricingService $servicePackagePricingService,
        ServiceMasterSyncService $serviceMasterSyncService
    )
    {
        $servicePakets = $serviceMasterSyncService->visibleServicePakets(['peralatans']);
        $serviceMediaOptions = $servicePackagePricingService->availableMediaOptions();
        $servicePaketCatalog = $servicePackagePricingService->packageCatalog($servicePakets, $serviceMediaOptions);
        $peralatans = $serviceMasterSyncService->visiblePeralatans();

        $serviceLogs = Service::with(['unitApar.pelanggan', 'unitApar.produk', 'pesanan.pelanggan', 'servicePaket'])
            ->where(function ($query) {
                $query->whereNull('jenis_service')
                    ->orWhere(function ($q) {
                        $q->where('jenis_service', 'not like', '%Refill%')
                          ->where('jenis_service', '!=', 'Refill');
                    });
            })
            ->where('status_konfirmasi', '!=', 'pending')
            ->orderByDesc('tgl_service')
            ->orderByDesc('created_at')
            ->get();

        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])->get();
        $ukuranAparOptions = $this->buildUkuranAparOptions();
        $pelanggans = $this->linkedPelangganSelection()->get();

        $requestServices = Pesanan::with(['pelanggan', 'teknisi', 'servicePaket', 'serviceJenisRefill', 'service.unitApar.pelanggan', 'service.unitApar.produk'])
            ->where('tipe', 'service')
            ->where(function ($query) {
                $query->whereNull('service_jenis_layanan')
                    ->orWhere('service_jenis_layanan', 'service');
            })
            ->whereNotIn('status', ['selesai', 'selesai final', 'ditolak'])
            ->latest()
            ->get();

        $selesaiTeknisi = Pesanan::with(['pelanggan', 'teknisi'])
            ->where('tipe', 'service')
            ->where(function ($query) {
                $query->whereNull('service_jenis_layanan')
                    ->orWhere('service_jenis_layanan', 'service');
            })
            ->whereIn('status', ['selesai', 'selesai final'])
            ->orderByDesc('teknisi_selesai_at')
            ->orderByDesc('created_at')
            ->get();

        $teknisis = \App\Models\User::where('role', 'teknisi')->orderBy('name')->get();

        $serviceStatusFlow = [
            Pesanan::STATUS_PENDING,
            Pesanan::STATUS_PERMINTAAN_MASUK,
            Pesanan::STATUS_DIREVIEW_ADMIN,
            Pesanan::STATUS_MENUNGGU_PENJADWALAN,
            Pesanan::STATUS_MENUNGGU_PERSETUJUAN_BIAYA,
            Pesanan::STATUS_DISETUJUI,
            Pesanan::STATUS_MENUNGGU_PENGAMBILAN,
            Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT,
            Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            Pesanan::STATUS_SELESAI_FINAL,
            Pesanan::STATUS_DITOLAK,
        ];

        return view('admin.service.index', compact(
            'serviceLogs',
            'units',
            'ukuranAparOptions',
            'pelanggans',
            'servicePakets',
            'serviceMediaOptions',
            'servicePaketCatalog',
            'requestServices',
            'peralatans',
            'teknisis',
            'selesaiTeknisi',
            'serviceStatusFlow'
        ));
    }

    public function updateRequestStatus(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->tipe !== 'service') {
            return back()->with('error', 'Data ini bukan service APAR.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,permintaan masuk,direview admin,menunggu penjadwalan,menunggu persetujuan biaya,disetujui,menunggu pengambilan,menunggu kedatangan unit,ditugaskan ke teknisi,dikerjakan teknisi,selesai oleh teknisi,dikonfirmasi admin,selesai final,ditolak',
            'service_estimasi_biaya' => 'nullable|string|max:30',
            'service_admin_catatan' => 'nullable|string|max:1000',
        ]);

        $estimasiRaw = preg_replace('/[^\d]/', '', (string) ($validated['service_estimasi_biaya'] ?? ''));
        $estimasiBiaya = $estimasiRaw !== '' ? (float) $estimasiRaw : null;

        try {
            DB::transaction(function () use ($pesanan, $validated, $estimasiBiaya) {
                $payload = [
                    'status' => $validated['status'],
                    'service_admin_catatan' => $validated['service_admin_catatan'] ?? $pesanan->service_admin_catatan,
                ];

                if (!is_null($estimasiBiaya)) {
                    $payload['service_estimasi_biaya'] = $estimasiBiaya;
                }

                if ($validated['status'] === Pesanan::STATUS_SELESAI_FINAL) {
                    $payload['status'] = Pesanan::STATUS_SELESAI_FINAL;
                }

                $previousStatus = (string) $pesanan->status;
                $pesanan->update($payload);

                if (
                    $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
                    && ($previousStatus !== Pesanan::STATUS_SELESAI_FINAL || empty($pesanan->service?->stok_kurang_history))
                ) {
                    app(FinalTransactionStockService::class)->apply($pesanan->fresh());
                    $pesanan->pelanggan?->update(['status' => 'tetap']);
                }
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Status service APAR berhasil diperbarui.');
    }

    public function create()
    {
        return redirect()->route('admin.service.index');
    }

    public function store(
        Request $request,
        ServicePackagePricingService $servicePackagePricingService,
        ServiceMasterSyncService $serviceMasterSyncService
    )
    {
        $serviceMasterSyncService->sync();

        $request->merge([
            'pelanggan_id' => $request->input('pelanggan_id'),
            'status_unit' => $request->input('status_unit', $request->filled('unit_apar_id') ? 'terdaftar' : 'belum_terdaftar'),
        ]);

        $request->validate([
            'pelanggan_id'        => 'required|exists:pelanggans,id',
            'status_unit'         => 'required|in:terdaftar,belum_terdaftar',
            'unit_apar_id'        => 'nullable|exists:unit_apars,id',
            'unit_apar_ids'       => 'nullable|array',
            'unit_apar_ids.*'     => 'integer|exists:unit_apars,id',
            'service_paket_id'    => 'required|exists:service_pakets,id',
            'jenis_apar'          => 'nullable|string|max:120',
            'ukuran_apar'         => 'nullable|string|max:50',
            'jumlah_unit'         => 'nullable|integer|min:1',
            'tgl_service'         => 'required|date',
            'catatan_admin'       => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required'        => 'Pilih pelanggan terlebih dahulu.',
            'pelanggan_id.exists'          => 'Pelanggan yang dipilih tidak valid.',
            'status_unit.required'         => 'Status unit APAR wajib dipilih.',
            'unit_apar_id.exists'          => 'Unit APAR yang dipilih tidak valid.',
            'unit_apar_ids.*.exists'       => 'Ada unit APAR terdaftar yang tidak valid.',
            'service_paket_id.required'    => 'Pilih jenis service terlebih dahulu.',
        ]);

        $pelanggan = Pelanggan::query()
            ->visibleInDirectory()
            ->with('user')
            ->find((int) $request->pelanggan_id);

        if (! $pelanggan) {
            return back()
                ->withInput()
                ->withErrors([
                    'pelanggan_id' => 'Pelanggan belum memiliki akun. Silakan buat akun pelanggan terlebih dahulu melalui menu Manajemen Akun.',
                ]);
        }

        $paket = $serviceMasterSyncService
            ->visibleServicePakets(['peralatans'])
            ->firstWhere('id', (int) $request->service_paket_id);

        if (! $paket) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => 'Jenis service yang dipilih tidak tersedia pada master service aktif.',
                ]);
        }
        $statusUnit = (string) $request->input('status_unit', 'belum_terdaftar');
        $teknisi = \App\Models\User::where('role', 'teknisi')->first();

        if ($statusUnit === 'terdaftar') {
            $selectedUnitIds = collect((array) $request->input('unit_apar_ids', []))
                ->merge($request->filled('unit_apar_id') ? [$request->input('unit_apar_id')] : [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            if ($selectedUnitIds->isEmpty()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'unit_apar_ids' => 'Minimal satu Unit APAR terdaftar wajib dicentang.',
                    ]);
            }

            $selectedUnits = UnitApar::query()
                ->with(['produk.jenisApar'])
                ->where('pelanggan_id', $pelanggan->id)
                ->whereIn('id', $selectedUnitIds->all())
                ->get()
                ->sortBy(fn (UnitApar $unit) => $selectedUnitIds->search((int) $unit->id))
                ->values();

            if ($selectedUnits->count() !== $selectedUnitIds->count()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'unit_apar_ids' => 'Ada unit APAR terdaftar yang tidak valid atau bukan milik pelanggan terpilih.',
                    ]);
            }

            $registeredPayloads = $selectedUnits->map(function (UnitApar $unit) use ($paket, $servicePackagePricingService) {
                $ukuran = trim((string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: ''));
                $media = trim((string) ($unit->produk?->jenisApar?->nama ?: $unit->bahan ?: ''));

                if ($ukuran === '' || $media === '') {
                    return ['error' => 'Ada unit APAR terdaftar yang belum memiliki jenis atau ukuran lengkap.'];
                }

                $summary = $servicePackagePricingService->summarizePackageOrder($paket, [[
                    'label' => trim(($unit->no_seri ?: ('UNIT-' . $unit->id)) . ' - APAR ' . $media . ' ' . $ukuran),
                    'media' => $media,
                    'ukuran' => $ukuran,
                    'qty' => 1,
                ]]);

                if ((float) ($summary['total_price'] ?? 0) <= 0) {
                    return ['error' => "Harga service untuk unit {$unit->no_seri} belum tersedia."];
                }

                return [
                    'unit' => $unit,
                    'ukuran' => $ukuran,
                    'media' => $media,
                    'total' => (float) ($summary['total_price'] ?? 0),
                    'peralatan' => $summary['peralatan_items'] ?? [],
                ];
            });

            if ($error = $registeredPayloads->firstWhere('error')) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'service_paket_id' => $error['error'],
                    ]);
            }

            try {
                DB::transaction(function () use ($registeredPayloads, $paket, $teknisi, $pelanggan, $request) {
                    foreach ($registeredPayloads as $payload) {
                        /** @var UnitApar $unit */
                        $unit = $payload['unit'];
                        $biaya = (float) $payload['total'];
                        $catatan = trim((string) $request->catatan_admin) !== '' ? trim((string) $request->catatan_admin) : '-';

                        $pesanan = Pesanan::create([
                            'pelanggan_id' => $pelanggan->id,
                            'user_id' => $pelanggan->user_id,
                            'nama_penerima' => $pelanggan->nama,
                            'nomor_wa_penerima' => $pelanggan->no_wa,
                            'alamat_pengiriman' => $pelanggan->alamat,
                            'tipe' => 'service',
                            'service_jenis_layanan' => 'service',
                            'service_paket_id' => $paket->id,
                            'service_jenis_apar' => $payload['media'],
                            'service_ukuran_apar' => $payload['ukuran'],
                            'service_jumlah_unit' => 1,
                            'service_metode_penanganan' => 'antar sendiri',
                            'sumber_pesanan' => 'datang_langsung',
                            'tanggal' => $request->tgl_service,
                            'total' => $biaya,
                            'total_harga' => $biaya,
                            'service_estimasi_biaya' => $biaya,
                            'status' => $teknisi
                                ? Pesanan::STATUS_DITUGASKAN_KE_TEKNISI
                                : Pesanan::STATUS_DIPROSES,
                            'teknisi_id' => $teknisi?->id,
                            'metode_pembayaran' => 'cash',
                            'metode_pengiriman' => 'pickup',
                            'ongkir' => 0,
                            'pembayaran_terkonfirmasi_at' => now(),
                            'catatan_admin' => $request->catatan_admin,
                            'service_keluhan' => trim(
                                "Rincian Service " . ($paket->label ?: 'Paket') . " - {$paket->nama}\n"
                                . "1. " . ($unit->no_seri ?: ('UNIT-' . $unit->id)) . " - APAR {$payload['media']} {$payload['ukuran']} - Rp" . number_format($biaya, 0, ',', '.') . "\n"
                                . "Catatan Pelanggan: {$catatan}"
                            ),
                            'keterangan' => 'Status Unit: APAR Terdaftar | Unit: ' . ($unit->no_seri ?: ('UNIT-' . $unit->id)),
                        ]);

                        Service::create([
                            'pesanan_id' => $pesanan->id,
                            'unit_apar_id' => $unit->id,
                            'service_paket_id' => $paket->id,
                            'jenis_service' => $paket->nama,
                            'rincian_layanan' => $paket->rincian_layanan,
                            'tgl_service' => $request->tgl_service,
                            'keterangan' => $request->catatan_admin,
                            'biaya' => $biaya,
                            'estimasi_peralatan_json' => json_encode($payload['peralatan']),
                            'actual_peralatan_json' => json_encode($payload['peralatan']),
                            'status_konfirmasi' => 'pending',
                        ]);
                    }
                });
            } catch (\RuntimeException $exception) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'service_paket_id' => $exception->getMessage(),
                    ]);
            }

            $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';
            $jumlahTransaksi = $registeredPayloads->count();
            $totalBiaya = (float) $registeredPayloads->sum('total');

            return redirect()
                ->route('admin.service.index')
                ->with(
                    'success',
                    "Service offline untuk {$jumlahTransaksi} unit APAR terdaftar berhasil disimpan. Total Rp "
                    . number_format($totalBiaya, 0, ',', '.')
                    . ". Status: Lunas & {$statusLabel}. Stok peralatan akan berkurang saat status Selesai Final."
                );
        }

        $jenisApar = trim((string) $request->input('jenis_apar', ''));
        $ukuranApar = trim((string) $request->input('ukuran_apar', ''));
        $jumlahUnit = (int) $request->input('jumlah_unit', 0);

        if ($jenisApar === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_apar' => 'Jenis APAR wajib dipilih untuk APAR tidak terdaftar.',
                ]);
        }

        if ($ukuranApar === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'ukuran_apar' => 'Ukuran APAR wajib dipilih untuk APAR tidak terdaftar.',
                ]);
        }

        if ($jumlahUnit < 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'jumlah_unit' => 'Jumlah unit wajib diisi minimal 1.',
                ]);
        }

        $packageSummary = $servicePackagePricingService->summarizePackageOrder($paket, [[
            'label' => trim('APAR ' . $jenisApar . ' ' . $ukuranApar),
            'media' => $jenisApar,
            'ukuran' => $ukuranApar,
            'qty' => $jumlahUnit,
        ]]);
        $totalBiaya = (float) ($packageSummary['total_price'] ?? 0);
        $peralatanPaket = $packageSummary['peralatan_items'] ?? [];
        $produkUntukUnitBaru = $this->resolveOfflineUnitProduct($ukuranApar, $jenisApar);

        if (! $produkUntukUnitBaru) {
            return back()
                ->withInput()
                ->withErrors([
                    'ukuran_apar' => 'Produk APAR dengan media dan ukuran yang dipilih belum tersedia. Tambahkan produk yang sesuai terlebih dahulu agar unit APAR otomatis bisa dibuat saat finalisasi.',
                ]);
        }

        if ($totalBiaya <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => 'Harga standar service untuk jenis service yang dipilih belum tersedia.',
                ]);
        }

        try {
            DB::transaction(function () use ($request, $paket, $totalBiaya, $jumlahUnit, $teknisi, $peralatanPaket, $pelanggan, $jenisApar, $ukuranApar) {
                $catatan = trim((string) $request->catatan_admin) !== '' ? trim((string) $request->catatan_admin) : '-';

                $pesanan = Pesanan::create([
                    'pelanggan_id' => (int) $pelanggan->id,
                    'user_id' => $pelanggan->user_id,
                    'nama_penerima' => $pelanggan->nama,
                    'nomor_wa_penerima' => $pelanggan->no_wa,
                    'alamat_pengiriman' => $pelanggan->alamat,
                    'tipe' => 'service',
                    'service_jenis_layanan' => 'service',
                    'service_paket_id' => $paket->id,
                    'service_jenis_apar' => $jenisApar,
                    'service_ukuran_apar' => $ukuranApar,
                    'service_jumlah_unit' => $jumlahUnit,
                    'service_metode_penanganan' => 'antar sendiri',
                    'sumber_pesanan' => 'datang_langsung',
                    'tanggal' => $request->tgl_service,
                    'total' => $totalBiaya,
                    'total_harga' => $totalBiaya,
                    'service_estimasi_biaya' => $totalBiaya,
                    'status' => $teknisi
                        ? Pesanan::STATUS_DITUGASKAN_KE_TEKNISI
                        : Pesanan::STATUS_DIPROSES,
                    'teknisi_id' => $teknisi?->id,
                    'metode_pembayaran' => 'cash',
                    'metode_pengiriman' => 'pickup',
                    'ongkir' => 0,
                    'pembayaran_terkonfirmasi_at' => now(),
                    'catatan_admin' => $request->catatan_admin,
                    'service_keluhan' => trim(
                        "Rincian Service " . ($paket->label ?: 'Paket') . " - {$paket->nama}\n"
                        . "1. APAR {$jenisApar} {$ukuranApar} x {$jumlahUnit} unit - Rp" . number_format($totalBiaya, 0, ',', '.') . "\n"
                        . "Catatan Pelanggan: {$catatan}"
                    ),
                    'keterangan' => "Status Unit: APAR Tidak Terdaftar | Detail: APAR {$jenisApar} {$ukuranApar} | Jumlah Unit: {$jumlahUnit} unit",
                ]);

                Service::create([
                    'pesanan_id' => $pesanan->id,
                    'unit_apar_id' => null,
                    'service_paket_id' => $paket->id,
                    'jenis_service' => $paket->nama,
                    'rincian_layanan' => $paket->rincian_layanan,
                    'tgl_service' => $request->tgl_service,
                    'keterangan' => $request->catatan_admin,
                    'biaya' => $totalBiaya,
                    'estimasi_peralatan_json' => json_encode($peralatanPaket),
                    'actual_peralatan_json' => json_encode($peralatanPaket),
                    'status_konfirmasi' => 'pending',
                ]);
            });
        } catch (\RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => $exception->getMessage(),
                ]);
        }

        $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';

        return redirect()
            ->route('admin.service.index')
            ->with('success', "Service offline berhasil disimpan. Status: Lunas & {$statusLabel}. Unit APAR untuk transaksi tidak terdaftar akan dibuat saat status Selesai Final, dan stok peralatan akan berkurang pada tahap tersebut.");
    }

    private function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') return '';
        if (str_starts_with($digits, '62')) return '0' . substr($digits, 2);
        if (str_starts_with($digits, '8')) return '0' . $digits;
        return $digits;
    }

    public function show(Service $service)
    {
        return redirect()->route('admin.service.edit', $service);
    }

    public function edit(Service $service, ServiceMasterSyncService $serviceMasterSyncService)
    {
        if ($service->jenis_service === 'Refill') {
            return redirect()->route('admin.refill.index')->with('success', 'Data refil dikelola dari menu Refil APAR.');
        }

        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])->get();
        $servicePakets = $serviceMasterSyncService->visibleServicePakets(['peralatans']);

        return view('admin.service.edit', compact('service', 'units', 'servicePakets'));
    }

    public function update(
        Request $request,
        Service $service,
        ServicePackagePricingService $servicePackagePricingService,
        ServiceMasterSyncService $serviceMasterSyncService
    )
    {
        $request->validate([
            'unit_apar_id' => 'required|exists:unit_apars,id',
            'service_paket_id' => 'nullable|exists:service_pakets,id',
            'jenis_service' => 'nullable|required_without:service_paket_id|string|max:255',
            'tgl_service' => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
            'biaya' => 'nullable|required_without:service_paket_id|numeric|min:0',
        ]);

        if (! $request->filled('service_paket_id')) {
            $service->update([
                'unit_apar_id' => $request->unit_apar_id,
                'service_paket_id' => null,
                'jenis_service' => $request->jenis_service,
                'rincian_layanan' => $request->keterangan,
                'tgl_service' => $request->tgl_service,
                'keterangan' => $request->keterangan,
                'biaya' => $request->biaya,
            ]);

            return redirect()->route('admin.service.index')->with('success', 'Data service berhasil diperbarui.');
        }

        $paket = $serviceMasterSyncService
            ->visibleServicePakets(['peralatans'])
            ->firstWhere('id', (int) $request->service_paket_id);

        if (! $paket) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => 'Jenis service yang dipilih tidak tersedia pada master service aktif.',
                ]);
        }
        $unitApar = UnitApar::with('produk.jenisApar')->findOrFail($request->unit_apar_id);
        $ukuran = trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: ''));
        $media = trim((string) ($unitApar->produk?->jenisApar?->nama ?: $unitApar->bahan ?: ''));
        $estimasiPeralatan = $servicePackagePricingService->resolveEstimatedPeralatan($paket, 1);
        $biaya = $servicePackagePricingService->resolvePackagePrice($paket, $media, $ukuran);

        $service->update([
            'unit_apar_id' => $request->unit_apar_id,
            'service_paket_id' => $paket->id,
            'jenis_service' => $paket->nama,
            'rincian_layanan' => $paket->rincian_layanan,
            'tgl_service' => $request->tgl_service,
            'keterangan' => $request->keterangan,
            'biaya' => $biaya,
            'estimasi_peralatan_json' => json_encode($estimasiPeralatan),
        ]);

        return redirect()->route('admin.service.index')->with('success', 'Data service berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->status_konfirmasi === 'confirmed') {
            return back()->with('error', 'Service yang sudah dikonfirmasi tidak bisa dihapus.');
        }
        $service->delete();

        return redirect()->route('admin.service.index')->with('success', 'Data service berhasil dihapus.');
    }

    public function konfirmasiSelesai(Request $request, Service $service, InventoryService $inventoryService)
    {
        if ($service->status_konfirmasi === 'confirmed') {
            return back()->with('error', 'Service ini sudah dikonfirmasi.');
        }

        if (!$service->pesanan || (string) $service->pesanan->status !== Pesanan::STATUS_SELESAI_FINAL) {
            return back()->with('error', 'Stok service hanya bisa dikurangi setelah transaksi berstatus Selesai Final.');
        }

        if (!empty($service->stok_kurang_history)) {
            $service->update([
                'tgl_selesai_admin' => now(),
                'status_konfirmasi' => 'confirmed',
            ]);

            return back()->with('success', 'Service dikonfirmasi selesai. Stok peralatan sudah diproses pada tahap selesai final.');
        }

        $actualPeralatan = $service->actual_peralatan;
        $history = [];
        $customerName = (string) ($service->pesanan?->pelanggan?->nama
            ?: $service->unitApar?->pelanggan?->nama
            ?: 'Pelanggan tidak diketahui');

        try {
            DB::transaction(function () use ($service, $actualPeralatan, &$history, $inventoryService, $customerName) {
            foreach ($actualPeralatan as $item) {
                $peralatan = Peralatan::find($item['peralatan_id'] ?? $item['id']);
                if (!$peralatan) continue;

                $stokSebelum = (float) $peralatan->stok;
                $jumlah = (float) ($item['jumlah'] ?? 1);

                $inventoryService->decreasePeralatanStock(
                    peralatan: $peralatan,
                    qty: $jumlah,
                    sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                    reference: $service,
                    keterangan: 'Service APAR - ' . $customerName,
                    tanggal: now(),
                );

                $history[] = [
                    'peralatan_id' => $peralatan->id,
                    'nama' => $peralatan->nama,
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $peralatan->fresh()->stok,
                ];
            }

            $service->update([
                'tgl_selesai_admin' => now(),
                'status_konfirmasi' => 'confirmed',
                'stok_kurang_history_json' => json_encode($history),
            ]);

            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Data service dikonfirmasi selesai. Stok peralatan telah diperbarui.');
    }

    public function tolakService(Service $service)
    {
        if ($service->status_konfirmasi === 'confirmed') {
            return back()->with('error', 'Service yang sudah dikonfirmasi tidak bisa ditolak.');
        }

        $service->update([
            'status_konfirmasi' => 'rejected',
            'tgl_selesai_admin' => now(),
        ]);

        return back()->with('success', 'Data service ditolak.');
    }

    protected function resolveOfflineUnitProduct(string $ukuran, string $media): ?Produk
    {
        $ukuran = trim($ukuran);
        $media = trim($media);

        return Produk::query()
            ->where('kapasitas', $ukuran)
            ->whereHas('jenisApar', fn ($query) => $query->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($media) . '%']))
            ->first()
            ?? Produk::query()
                ->where('kapasitas', $ukuran)
                ->first();
    }

    protected function buildUkuranAparOptions(): array
    {
        $ukuranList = Produk::query()
            ->whereNotNull('kapasitas')
            ->pluck('kapasitas')
            ->merge(
                UnitApar::query()
                    ->whereNotNull('ukuran')
                    ->pluck('ukuran')
            )
            ->map(fn ($ukuran) => trim((string) $ukuran))
            ->filter()
            ->unique(fn ($ukuran) => mb_strtolower($ukuran))
            ->values()
            ->all();

        usort($ukuranList, function (string $a, string $b) {
            preg_match('/(\d+(?:[.,]\d+)?)/', $a, $matchA);
            preg_match('/(\d+(?:[.,]\d+)?)/', $b, $matchB);

            $numberA = isset($matchA[1]) ? (float) str_replace(',', '.', $matchA[1]) : INF;
            $numberB = isset($matchB[1]) ? (float) str_replace(',', '.', $matchB[1]) : INF;

            return $numberA <=> $numberB ?: strnatcasecmp($a, $b);
        });

        return $ukuranList;
    }
}
