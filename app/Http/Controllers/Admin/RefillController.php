<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\FinalTransactionStockService;
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

        $refills = Refill::with(['unitApar.pelanggan', 'unitApar.produk', 'jenisRefill', 'service.pesanan'])
            ->orderByDesc('tgl_refill')
            ->orderByDesc('created_at')
            ->get();
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $pelanggans = \App\Models\Pelanggan::with(['units.produk.jenisApar'])->orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $ukuranAparOptions = $this->buildUkuranAparOptions();
        $refillPackages = $this->refillPackages();

        $requestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill', 'service.unitApar.pelanggan', 'service.unitApar.produk'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereNotIn('status', ['selesai', 'selesai final', 'ditolak'])
            ->latest()
            ->get();

        $completedRequestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill', 'service.unitApar.pelanggan', 'service.unitApar.produk'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereIn('status', ['selesai', 'selesai final'])
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
            'pelanggans',
            'refillStatusFlow'
        ));
    }

    public function create()
    {
        return redirect()->route('admin.refill.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'pelanggan_id' => $request->input('pelanggan_id'),
        ]);

        $request->validate([
            'pelanggan_id'       => 'required|exists:pelanggans,id',
            'unit_apar_id'       => 'nullable|exists:unit_apars,id',
            'jenis_refill_id'    => 'required|exists:jenis_refills,id',
            'ukuran_apar'        => 'required|string|max:50',
            'jumlah_unit'        => 'required|integer|min:1',
            'tgl_refill'         => 'required|date',
            'catatan_admin'      => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_id.exists'   => 'Pelanggan yang dipilih tidak valid.',
            'unit_apar_id.exists'   => 'Unit APAR yang dipilih tidak valid.',
            'jenis_refill_id.required' => 'Jenis refill wajib dipilih.',
            'jenis_refill_id.exists'   => 'Jenis refill yang dipilih tidak valid.',
            'ukuran_apar.required'     => 'Ukuran APAR wajib dipilih.',
            'jumlah_unit.required'     => 'Jumlah unit wajib diisi.',
            'jumlah_unit.min'          => 'Jumlah unit minimal 1.',
            'tgl_refill.required'      => 'Tanggal refill wajib diisi.',
            'tgl_refill.date'         => 'Format tanggal refill tidak valid.',
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

        DB::transaction(function () use ($request, $jenisRefill, $jumlahPakai, $jumlahUnit, $totalBiaya, $teknisi) {
            $pelangganId = (int) $request->pelanggan_id;

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

            // --- Resolve unit_apar_id: use existing or create new ---
            $pelanggan = \App\Models\Pelanggan::findOrFail($pelangganId);
            $tanggalRefill = \Carbon\Carbon::parse($request->tgl_refill);

            if ($request->unit_apar_id) {
                // APAR terdaftar - use existing unit
                $unitAparId = (int) $request->unit_apar_id;
            } else {
                // APAR belum terdaftar - create new unit automatically
                $bahanLabel = $jenisRefill->nama_label;
                $bahan = match (strtolower($bahanLabel)) {
                    'powder' => 'Powder',
                    'co2' => 'CO2',
                    'foam' => 'Foam',
                    default => $bahanLabel,
                };

                // Find produk based on ukuran and bahan
                $produk = Produk::where('kapasitas', $request->ukuran_apar)
                    ->whereHas('jenisApar', fn ($q) => $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($bahan) . '%']))
                    ->first();

                // Fallback: find any produk matching ukuran
                if (!$produk) {
                    $produk = Produk::where('kapasitas', $request->ukuran_apar)->first();
                }

                $noSeri = UnitApar::generateSerialNumber($pelanggan, $tanggalRefill);
                $tglProduksi = $tanggalRefill;
                $tglExpired = UnitApar::calculateExpiry($tanggalRefill, $request->ukuran_apar, $bahan);

                $unitApar = UnitApar::create([
                    'pelanggan_id' => $pelangganId,
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $produk?->id,
                    'no_seri' => $noSeri,
                    'tgl_beli' => $tanggalRefill,
                    'tgl_produksi' => $tglProduksi,
                    'ukuran' => $request->ukuran_apar,
                    'bahan' => $bahan,
                    'kondisi_awal' => 'layak',
                    'catatan_unit' => 'Unit dibuat otomatis dari refill offline/manual - ' . $jumlahUnit . ' unit',
                    'tgl_expired' => $tglExpired,
                ]);

                if (!$unitApar || !$unitApar->id) {
                    throw new \Exception('Gagal membuat Unit APAR baru untuk refill offline.');
                }

                $unitAparId = $unitApar->id;
            }

            // --- Create Service and Refill records ---
            $service = \App\Models\Service::create([
                'pesanan_id' => $pesanan->id,
                'unit_apar_id' => $unitAparId,
                'jenis_service' => 'Refill APAR',
                'tgl_service' => $request->tgl_refill,
                'biaya' => $totalBiaya,
                'status_konfirmasi' => 'pending',
            ]);

            Refill::create([
                'service_id' => $service->id,
                'unit_apar_id' => $unitAparId,
                'jenis_refill_id' => $jenisRefill->id,
                'tgl_refill' => $request->tgl_refill,
                'biaya' => $totalBiaya,
            ]);
        });

        $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';

        return redirect()
            ->route('admin.refill.index')
            ->with('success', "Refill offline berhasil disimpan. Status: Lunas & {$statusLabel}. Stok akan berkurang saat status Selesai Final.");
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
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
        ]);

        return back()->with('success', 'Berhasil ditugaskan ke teknisi: ' . $teknisi->name . '.');
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

        if ($pesanan->status === Pesanan::STATUS_SELESAI_FINAL) {
            app(FinalTransactionStockService::class)->apply($pesanan);
        }

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
