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
use App\Services\ServicePackagePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(ServicePackagePricingService $servicePackagePricingService)
    {
        $servicePakets = ServicePaket::with('peralatans')
            ->orderBy('harga')
            ->get()
            ->values();
        $serviceMediaOptions = $servicePackagePricingService->availableMediaOptions();
        $servicePaketCatalog = $servicePackagePricingService->packageCatalog($servicePakets, $serviceMediaOptions);
        $peralatans = Peralatan::orderBy('nama')->get();

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
        $pelanggans = Pelanggan::with(['units.produk.jenisApar'])->orderBy('nama')->get();

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

        $payload = [
            'status' => $validated['status'],
            'service_admin_catatan' => $validated['service_admin_catatan'] ?? $pesanan->service_admin_catatan,
        ];

        if (!is_null($estimasiBiaya)) {
            $payload['service_estimasi_biaya'] = $estimasiBiaya;
        }

        if ($validated['status'] === 'selesai final') {
            $payload['status'] = 'selesai final';
            $pesanan->pelanggan?->update(['status' => 'tetap']);
        }

        $pesanan->update($payload);

        if ($pesanan->status === Pesanan::STATUS_SELESAI_FINAL) {
            app(FinalTransactionStockService::class)->apply($pesanan);
        }

        return back()->with('success', 'Status service APAR berhasil diperbarui.');
    }

    public function create()
    {
        return redirect()->route('admin.service.index');
    }

    public function store(
        Request $request,
        ServicePackagePricingService $servicePackagePricingService
    )
    {
        $request->merge([
            'pelanggan_id' => $request->input('pelanggan_id'),
        ]);

        $request->validate([
            'pelanggan_id'        => 'required|exists:pelanggans,id',
            'unit_apar_id'        => 'nullable|exists:unit_apars,id',
            'service_paket_id'    => 'required|exists:service_pakets,id',
            'jenis_apar'          => 'required|string|max:120',
            'ukuran_apar'         => 'required|string|max:50',
            'jumlah_unit'         => 'required|integer|min:1',
            'tgl_service'         => 'required|date',
            'catatan_admin'       => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required'        => 'Pilih pelanggan terlebih dahulu.',
            'pelanggan_id.exists'          => 'Pelanggan yang dipilih tidak valid.',
            'unit_apar_id.exists'          => 'Unit APAR yang dipilih tidak valid.',
            'service_paket_id.required'    => 'Pilih jenis service terlebih dahulu.',
        ]);

        $paket = ServicePaket::with('peralatans')->findOrFail($request->service_paket_id);
        $jumlahUnit = max(1, (int) $request->jumlah_unit);
        $packageSummary = $servicePackagePricingService->summarizePackageOrder($paket, [[
            'label' => trim('APAR ' . $request->jenis_apar . ' ' . $request->ukuran_apar),
            'media' => (string) $request->jenis_apar,
            'ukuran' => (string) $request->ukuran_apar,
            'qty' => $jumlahUnit,
        ]]);
        $totalBiaya = (float) ($packageSummary['total_price'] ?? 0);
        $teknisi = \App\Models\User::where('role', 'teknisi')->first();
        $peralatanPaket = $packageSummary['peralatan_items'] ?? [];

        if ($totalBiaya <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => 'Harga service untuk kombinasi jenis service, media APAR, dan ukuran yang dipilih belum tersedia.',
                ]);
        }

        foreach (($packageSummary['stock_issues'] ?? []) as $stockIssue) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_paket_id' => "Stok peralatan {$stockIssue['nama']} tidak cukup. Dibutuhkan {$stockIssue['jumlah']} unit, tersedia {$stockIssue['stok']} unit.",
                ]);
        }

        try {
            DB::transaction(function () use ($request, $paket, $totalBiaya, $jumlahUnit, $teknisi, $peralatanPaket) {
            $pelangganId = (int) $request->pelanggan_id;

            // --- Create Pesanan record for tracking ---
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelangganId,
                'tipe' => 'service',
                'service_jenis_layanan' => 'service',
                'service_paket_id' => $paket->id,
                'service_jenis_apar' => (string) $request->jenis_apar,
                'service_ukuran_apar' => $request->ukuran_apar,
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
                    . "1. APAR {$request->jenis_apar} {$request->ukuran_apar} x {$jumlahUnit} unit - Rp" . number_format($totalBiaya, 0, ',', '.') . "\n"
                    . "Catatan Pelanggan: " . (trim((string) $request->catatan_admin) !== '' ? trim((string) $request->catatan_admin) : '-')
                ),
            ]);

            Service::create([
                'pesanan_id' => $pesanan->id,
                'unit_apar_id' => $request->unit_apar_id,
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
            ->with('success', "Service offline berhasil disimpan. Status: Lunas & {$statusLabel}. Stok peralatan akan berkurang saat status Selesai Final.");
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

    public function edit(Service $service)
    {
        if ($service->jenis_service === 'Refill') {
            return redirect()->route('admin.refill.index')->with('success', 'Data refil dikelola dari menu Refil APAR.');
        }

        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])->get();
        $servicePakets = ServicePaket::with('peralatans')
            ->orderBy('harga')
            ->get()
            ->values();

        return view('admin.service.edit', compact('service', 'units', 'servicePakets'));
    }

    public function update(Request $request, Service $service, ServicePackagePricingService $servicePackagePricingService)
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

        $paket = ServicePaket::with('peralatans')->findOrFail($request->service_paket_id);
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

            return back()->with('success', 'Service dikonfirmasi selesai. Stok peralatan sudah dikurangi saat transaksi offline disimpan.');
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
