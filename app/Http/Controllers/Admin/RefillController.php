<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefillController extends Controller
{
    public function index()
    {
        // Self-heal orphan refills that have null service_id
        $orphanRefills = Refill::whereNull('service_id')->get();
        foreach ($orphanRefills as $orphan) {
            $pelangganId = $orphan->unitApar?->pelanggan_id;
            if ($pelangganId) {
                $pesanan = Pesanan::where('tipe', 'service')
                    ->where('service_jenis_layanan', 'refill')
                    ->where('pelanggan_id', $pelangganId)
                    ->where('total', $orphan->biaya)
                    ->first();
                if ($pesanan) {
                    $service = \App\Models\Service::updateOrCreate(
                        ['pesanan_id' => $pesanan->id],
                        [
                            'unit_apar_id' => $orphan->unit_apar_id,
                            'jenis_service' => 'Refill APAR',
                            'tgl_service' => $orphan->tgl_refill,
                            'biaya' => $orphan->biaya,
                            'status_konfirmasi' => 'confirmed',
                        ]
                    );
                    $orphan->update(['service_id' => $service->id]);
                }
            }
        }

        $refills = Refill::with(['unitApar.pelanggan', 'unitApar.produk', 'jenisRefill', 'service.pesanan'])->latest('tgl_refill')->get();
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $ukuranAparOptions = $this->buildUkuranAparOptions();
        $refillPackages = $this->refillPackages();

        $requestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereNotIn('status', ['selesai final', 'ditolak'])
            ->latest()
            ->get();

        $completedRequestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereIn('status', ['selesai final'])
            ->latest()
            ->get();

        $teknisis = User::where('role', 'teknisi')->orderBy('name')->get();

        $refillStatusFlow = [
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

        return view('admin.refill.index', compact(
            'refills',
            'units',
            'jenisRefills',
            'ukuranAparOptions',
            'refillPackages',
            'requestRefills',
            'completedRequestRefills',
            'teknisis',
            'refillStatusFlow'
        ));
    }

    public function create()
    {
        return redirect()->route('admin.refill.index');
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
            'jenis_refill_id'    => 'required|exists:jenis_refills,id',
            'ukuran_apar'        => 'required|string|max:50',
            'jumlah_unit'        => 'required|integer|min:1',
            'tgl_refill'         => 'required|date',
            'catatan_admin'      => 'nullable|string|max:1000',
        ], [
            'new_pelanggan_nama.required' => 'Nama pelanggan wajib diisi.',
            'new_pelanggan_no_wa.required' => 'Nomor telepon pelanggan wajib diisi.',
        ]);

        $jenisRefill = JenisRefill::findOrFail($request->jenis_refill_id);

        $hargaPerUnit = $this->resolveOfflineRefillPrice($jenisRefill, (string) $request->ukuran_apar);
        if (is_null($hargaPerUnit) || $hargaPerUnit <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_refill_id' => 'Harga standar refill untuk jenis dan ukuran ini belum tersedia.',
                ]);
        }

        $jumlahUnit = (int) $request->jumlah_unit;
        $totalBiaya = $hargaPerUnit * $jumlahUnit;

        // Calculate material usage based on ukuran
        $angka = (float) filter_var($request->ukuran_apar, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $jumlahPakai = ($angka > 0 ? $angka : 1.0) * $jumlahUnit;
        $satuanLabel = $jenisRefill->satuan_label;

        if ($jenisRefill->stok < $jumlahPakai) {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_refill_id' => "Stok bahan {$jenisRefill->nama} tidak cukup. Dibutuhkan {$jumlahPakai} {$satuanLabel}, tersedia {$jenisRefill->stok} {$satuanLabel}.",
                ]);
        }

        $teknisi = User::where('role', 'teknisi')->first();

        DB::transaction(function () use ($request, $inventoryService, $jenisRefill, $jumlahPakai, $jumlahUnit, $totalBiaya, $hargaPerUnit, $satuanLabel, $teknisi) {
            // --- Resolve pelanggan ---
            $normalizedNoWa = (string) $request->new_pelanggan_no_wa;
            $existingPelanggan = \App\Models\Pelanggan::where('no_wa', $normalizedNoWa)->first();

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
                $pelangganBaru = \App\Models\Pelanggan::create([
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
                'service_jenis_layanan' => 'refill',
                'service_jenis_refill_id' => $jenisRefill->id,
                'service_jenis_apar' => 'APAR ' . $request->ukuran_apar,
                'service_ukuran_apar' => $request->ukuran_apar,
                'service_jumlah_unit' => $jumlahUnit,
                'service_total_kg' => $jumlahPakai,
                'service_metode_penanganan' => 'antar sendiri',
                'sumber_pesanan' => 'datang_langsung',
                'tanggal' => $request->tgl_refill,
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

            // --- Reduce refill stock ---
            $inventoryService->decreaseRefillStock(
                jenisRefill: $jenisRefill,
                qty: $jumlahPakai,
                sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
                reference: $pesanan,
                keterangan: "Refill offline - {$jumlahUnit} unit x {$request->ukuran_apar}",
                tanggal: $request->tgl_refill,
            );
        });

        $stokTerbaru = (float) $jenisRefill->fresh()->stok;
        $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';

        return redirect()
            ->route('admin.refill.index')
            ->with('success', "Refill offline berhasil disimpan. Status: Lunas & {$statusLabel}. Pemakaian {$jumlahPakai} {$satuanLabel}. Stok {$jenisRefill->nama} sekarang {$stokTerbaru} {$satuanLabel}.");
    }

    private function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') return '';
        if (str_starts_with($digits, '62')) return '0' . substr($digits, 2);
        if (str_starts_with($digits, '8')) return '0' . $digits;
        return $digits;
    }

    public function show(Refill $refill)
    {
        return redirect()->route('admin.refill.edit', $refill);
    }

    public function edit(Refill $refill)
    {
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $refillPackages = $this->refillPackages();

        return view('admin.refill.edit', compact('refill', 'units', 'jenisRefills', 'refillPackages'));
    }

    public function update(Request $request, Refill $refill)
    {
        $request->validate([
            'unit_apar_id' => 'required|exists:unit_apars,id',
            'jenis_refill_id' => 'required|exists:jenis_refills,id',
            'tgl_refill' => 'required|date',
            'biaya' => 'required|numeric|min:0',
        ]);

        $refill->update($request->only('unit_apar_id', 'jenis_refill_id', 'tgl_refill', 'biaya'));

        return redirect()->route('admin.refill.index')->with('success', 'Data refil APAR berhasil diperbarui.');
    }

    public function destroy(Refill $refill)
    {
        $refill->delete();

        return redirect()->route('admin.refill.index')->with('success', 'Data refil APAR berhasil dihapus.');
    }

    public function assignTeknisi(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->service_jenis_layanan !== 'refill') {
            return back()->with('error', 'Data ini bukan refil APAR.');
        }

        $teknisi = User::where('role', 'teknisi')->first();
        if (!$teknisi) {
            return back()->with('error', 'Data teknisi belum tersedia.');
        }

        $pesanan->update([
            'teknisi_id' => $teknisi->id,
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan ke data refil.');
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->service_jenis_layanan !== 'refill') {
            return back()->with('error', 'Data ini bukan refil APAR.');
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

        $pesanan->update($payload);

        return back()->with('success', 'Status refil APAR berhasil diperbarui.');
    }

    protected function refillPackages(): array
    {
        return [
            'Powder' => 200000,
            'CO2' => 275000,
            'Foam' => 250000,
        ];
    }

    protected function extractUnitUsage(UnitApar $unit): float
    {
        $kapasitasRaw = (string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: '');
        $angka = (float) filter_var($kapasitasRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $angka > 0 ? $angka : 1.0;
    }

    protected function resolveOfflineRefillPrice(JenisRefill $jenisRefill, string $ukuran): ?float
    {
        $rules = collect($jenisRefill->service_price_rules_json ?? [])
            ->filter(fn ($rule) => filled($rule['ukuran'] ?? null) && (float) ($rule['harga'] ?? 0) > 0)
            ->values();

        if ($rules->isNotEmpty()) {
            $matchedPrice = $jenisRefill->resolveServicePrice($ukuran);

            return $matchedPrice && $matchedPrice > 0
                ? (float) $matchedPrice
                : null;
        }

        $hargaDefault = (float) ($jenisRefill->harga ?? 0);

        return $hargaDefault > 0 ? $hargaDefault : null;
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
