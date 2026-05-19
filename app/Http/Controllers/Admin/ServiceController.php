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
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $servicePakets = ServicePaket::with('peralatans')
            ->orderBy('harga')
            ->get()
            ->reject(fn (ServicePaket $servicePaket) => $servicePaket->isLegacyTemplate())
            ->values();
        $peralatans = Peralatan::orderBy('nama')->get();

        $serviceLogs = Service::with(['unitApar.pelanggan', 'unitApar.produk', 'pesanan.pelanggan', 'servicePaket'])
            ->where(function ($query) {
                $query->whereNull('jenis_service')
                    ->orWhere(function ($q) {
                        $q->where('jenis_service', 'not like', '%Refill%')
                          ->where('jenis_service', '!=', 'Refill');
                    });
            })
            ->latest('tgl_service')
            ->get();

        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])->get();
        $ukuranAparOptions = $this->buildUkuranAparOptions();
        $pelanggans = Pelanggan::with(['units.produk.jenisApar'])->orderBy('nama')->get();

        $requestServices = Pesanan::with(['pelanggan', 'teknisi', 'servicePaket', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where(function ($query) {
                $query->whereNull('service_jenis_layanan')
                    ->orWhere('service_jenis_layanan', 'service');
            })
            ->whereNotIn('status', ['selesai final', 'ditolak'])
            ->latest()
            ->get();

        $selesaiTeknisi = Pesanan::with(['pelanggan', 'teknisi'])
            ->where('tipe', 'service')
            ->where(function ($query) {
                $query->whereNull('service_jenis_layanan')
                    ->orWhere('service_jenis_layanan', 'service');
            })
            ->whereIn('status', ['selesai oleh teknisi', 'dikonfirmasi admin'])
            ->latest('teknisi_selesai_at')
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

        return back()->with('success', 'Status service APAR berhasil diperbarui.');
    }

    public function create()
    {
        return redirect()->route('admin.service.index');
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $request->merge([
            'new_pelanggan_no_wa' => $this->normalizePhone($request->input('new_pelanggan_no_wa')),
        ]);

        $request->validate([
            'new_pelanggan_nama'  => 'required|string|max:255',
            'new_pelanggan_no_wa' => 'required|string|max:20',
            'new_pelanggan_alamat' => 'nullable|string|max:1000',
            'service_paket_id'    => 'required|exists:service_pakets,id',
            'ukuran_apar'         => 'required|string|max:50',
            'jumlah_unit'         => 'required|integer|min:1',
            'tgl_service'         => 'required|date',
            'catatan_admin'       => 'nullable|string|max:1000',
        ], [
            'new_pelanggan_nama.required'  => 'Nama pelanggan wajib diisi.',
            'new_pelanggan_no_wa.required' => 'Nomor telepon pelanggan wajib diisi.',
            'service_paket_id.required'    => 'Pilih paket service terlebih dahulu.',
        ]);

        $paket = ServicePaket::with('peralatans')->findOrFail($request->service_paket_id);
        $jumlahUnit = max(1, (int) $request->jumlah_unit);
        $totalBiaya = (float) $paket->harga * $jumlahUnit;
        $teknisi = \App\Models\User::where('role', 'teknisi')->first();
        $peralatanPaket = [];

        foreach ($paket->peralatans as $peralatan) {
            $jumlahPakai = ((int) $peralatan->pivot->jumlah_estimasi) * $jumlahUnit;

            if ($jumlahPakai <= 0) {
                continue;
            }

            if ((float) $peralatan->stok < $jumlahPakai) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'service_paket_id' => "Stok peralatan {$peralatan->nama} tidak cukup. Dibutuhkan {$jumlahPakai} unit, tersedia {$peralatan->stok} unit.",
                    ]);
            }

            $peralatanPaket[] = [
                'peralatan_id' => (int) $peralatan->id,
                'nama' => (string) $peralatan->nama,
                'jumlah' => $jumlahPakai,
                'jumlah_per_unit' => (int) $peralatan->pivot->jumlah_estimasi,
            ];
        }

        try {
            DB::transaction(function () use ($request, $paket, $totalBiaya, $jumlahUnit, $teknisi, $inventoryService, $peralatanPaket) {
            // --- Resolve pelanggan ---
            $normalizedNoWa = (string) $request->new_pelanggan_no_wa;
            $existingPelanggan = Pelanggan::where('no_wa', $normalizedNoWa)->first();

            if ($existingPelanggan) {
                $existingPelanggan->update([
                    'nama' => (string) $request->new_pelanggan_nama,
                    'alamat' => filled($request->new_pelanggan_alamat)
                        ? (string) $request->new_pelanggan_alamat
                        : $existingPelanggan->alamat,
                    'status' => 'tetap',
                    'sumber_data' => $existingPelanggan->sumber_data ?: 'manual',
                ]);
                $pelangganId = (int) $existingPelanggan->id;
            } else {
                $pelangganBaru = Pelanggan::create([
                    'nama' => (string) $request->new_pelanggan_nama,
                    'no_wa' => $normalizedNoWa,
                    'alamat' => $request->new_pelanggan_alamat,
                    'status' => 'tetap',
                    'sumber_data' => 'manual',
                    'kategori_pelanggan' => 'baru_manual',
                ]);
                $pelangganId = (int) $pelangganBaru->id;
            }

            // --- Create Pesanan record for tracking ---
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelangganId,
                'tipe' => 'service',
                'service_jenis_layanan' => 'service',
                'service_paket_id' => $paket->id,
                'service_jenis_apar' => 'APAR ' . $request->ukuran_apar,
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
            ]);

            $history = [];

            foreach ($peralatanPaket as $item) {
                $peralatan = Peralatan::find($item['peralatan_id']);
                if (!$peralatan) {
                    continue;
                }

                $stokSebelum = (float) $peralatan->stok;

                $inventoryService->decreasePeralatanStock(
                    peralatan: $peralatan,
                    qty: (float) $item['jumlah'],
                    sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                    reference: $pesanan,
                    keterangan: "Service offline {$paket->nama} - {$item['nama']} ({$item['jumlah']} unit)",
                    tanggal: $request->tgl_service,
                );

                $history[] = [
                    'peralatan_id' => $peralatan->id,
                    'nama' => $peralatan->nama,
                    'jumlah' => (int) $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => (float) $peralatan->fresh()->stok,
                ];
            }

            Service::create([
                'pesanan_id' => $pesanan->id,
                'service_paket_id' => $paket->id,
                'jenis_service' => $paket->nama,
                'rincian_layanan' => $paket->rincian_layanan,
                'tgl_service' => $request->tgl_service,
                'keterangan' => $request->catatan_admin,
                'biaya' => $totalBiaya,
                'estimasi_peralatan_json' => json_encode($peralatanPaket),
                'actual_peralatan_json' => json_encode($peralatanPaket),
                'status_konfirmasi' => 'pending',
                'stok_kurang_history_json' => json_encode($history),
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
            ->with('success', "Service offline berhasil disimpan. Status: Lunas & {$statusLabel}.");
    }

    private function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') return '';
        if (str_starts_with($digits, '62')) return '0' . substr($digits, 2);
        if (str_starts_with($digits, '8')) return '0' . $digits;
        return $digits;
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
            ->reject(fn (ServicePaket $servicePaket) => $servicePaket->isLegacyTemplate())
            ->values();

        return view('admin.service.edit', compact('service', 'units', 'servicePakets'));
    }

    public function update(Request $request, Service $service)
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

        $estimasiPeralatan = [];
        foreach ($paket->peralatans as $peralatan) {
            $estimasiPeralatan[] = [
                'peralatan_id' => $peralatan->id,
                'nama' => $peralatan->nama,
                'jumlah' => $peralatan->pivot->jumlah_estimasi,
            ];
        }

        $service->update([
            'unit_apar_id' => $request->unit_apar_id,
            'service_paket_id' => $paket->id,
            'jenis_service' => $paket->nama,
            'rincian_layanan' => $paket->rincian_layanan,
            'tgl_service' => $request->tgl_service,
            'keterangan' => $request->keterangan,
            'biaya' => $paket->harga,
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

        if (!empty($service->stok_kurang_history)) {
            $service->update([
                'tgl_selesai_admin' => now(),
                'status_konfirmasi' => 'confirmed',
            ]);

            if ($service->unitApar) {
                $service->unitApar->update(['tgl_service_terakhir' => now()->toDateString()]);
            }

            return back()->with('success', 'Service dikonfirmasi selesai. Stok peralatan sudah dikurangi saat transaksi offline disimpan.');
        }

        $actualPeralatan = $service->actual_peralatan;
        $history = [];

        try {
            DB::transaction(function () use ($service, $actualPeralatan, &$history, $inventoryService) {
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
                    keterangan: "Pemakaian {$peralatan->nama} untuk service #{$service->id}",
                    tanggal: now(),
                );

                $history[] = [
                    'peralatan_id' => $peralatan->id,
                    'nama' => $peralatan->nama,
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $peralatan->fresh()->stok,
                ];

                /*
                    'kategori' => 'Service - ' . $service->jenis_service,
                    'keterangan' => "Pengurangan stok {$peralatan->nama} ({$jumlah} pcs) untuk Service ID {$service->id} — {$service->unitApar?->pelanggan?->nama}",
                    'nominal' => $peralatan->harga_standar * $jumlah,
                    'tanggal' => now()->toDateString(),
                */
            }

            $service->update([
                'tgl_selesai_admin' => now(),
                'status_konfirmasi' => 'confirmed',
                'stok_kurang_history_json' => json_encode($history),
            ]);

            if ($service->unitApar) {
                $service->unitApar->update(['tgl_service_terakhir' => now()->toDateString()]);
            }
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
