<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
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
    private function linkedPelangganSelection()
    {
        return Pelanggan::query()
            ->visibleInDirectory()
            ->with([
                'user',
                'units' => fn ($query) => $query->visible()->with('produk.jenisApar'),
            ])
            ->orderBy('nama');
    }

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
        $units = UnitApar::query()->visible()->with(['pelanggan', 'produk'])->get();
        $pelanggans = $this->linkedPelangganSelection()->get();
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
        return redirect()
            ->route('admin.refill.index')
            ->with('error', 'Input manual refill sudah dinonaktifkan. Permintaan baru harus diajukan pelanggan melalui sistem.');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('admin.refill.index')
            ->with('error', 'Input manual refill sudah dinonaktifkan. Permintaan baru harus diajukan pelanggan melalui sistem.');

        $request->merge([
            'pelanggan_id' => $request->input('pelanggan_id'),
            'status_unit' => $request->input('status_unit', $request->filled('unit_apar_id') ? 'terdaftar' : 'belum_terdaftar'),
        ]);

        $request->validate([
            'pelanggan_id'       => 'required|exists:pelanggans,id',
            'status_unit'        => 'required|in:terdaftar,belum_terdaftar',
            'unit_apar_id'       => 'nullable|exists:unit_apars,id',
            'unit_apar_ids'      => 'nullable|array',
            'unit_apar_ids.*'    => 'integer|exists:unit_apars,id',
            'jenis_refill_id'    => 'required|exists:jenis_refills,id',
            'ukuran_apar'        => 'nullable|string|max:50',
            'jumlah_unit'        => 'nullable|integer|min:1',
            'tgl_refill'         => 'required|date',
            'catatan_admin'      => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_id.exists'   => 'Pelanggan yang dipilih tidak valid.',
            'status_unit.required'  => 'Status unit APAR wajib dipilih.',
            'unit_apar_id.exists'   => 'Unit APAR yang dipilih tidak valid.',
            'unit_apar_ids.*.exists' => 'Ada unit APAR terdaftar yang tidak valid.',
            'jenis_refill_id.required' => 'Jenis refill wajib dipilih.',
            'jenis_refill_id.exists'   => 'Jenis refill yang dipilih tidak valid.',
            'tgl_refill.required'      => 'Tanggal refill wajib diisi.',
            'tgl_refill.date'         => 'Format tanggal refill tidak valid.',
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

        $jenisRefill = JenisRefill::findOrFail($request->jenis_refill_id);
        $statusUnit = (string) $request->input('status_unit', 'belum_terdaftar');
        $teknisi = User::where('role', 'teknisi')->first();
        $tanggalRefill = \Carbon\Carbon::parse($request->tgl_refill);

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
                ->visible()
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

            $registeredPayloads = $selectedUnits->map(function (UnitApar $unit) use ($jenisRefill) {
                $ukuran = trim((string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: ''));
                if ($ukuran === '') {
                    return ['error' => 'Ada unit APAR terdaftar yang belum memiliki ukuran.'];
                }

                $harga = $this->resolveOfflineRefillPrice($jenisRefill, $ukuran);
                if (is_null($harga) || $harga <= 0) {
                    return ['error' => "Harga refill {$jenisRefill->nama_label} untuk ukuran {$ukuran} belum tersedia."];
                }

                return [
                    'unit' => $unit,
                    'ukuran' => $ukuran,
                    'apar_label' => $this->normalizeOfflineRefillBahan(
                        (string) ($unit->produk?->jenisApar?->nama ?: $unit->bahan ?: $jenisRefill->nama_label)
                    ),
                    'usage' => $this->extractUnitUsage($unit),
                    'price' => (float) $harga,
                ];
            });

            if ($error = $registeredPayloads->firstWhere('error')) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'unit_apar_ids' => $error['error'],
                    ]);
            }

            $totalUsage = (float) $registeredPayloads->sum('usage');
            if ($jenisRefill->stok < $totalUsage) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'jenis_refill_id' => "Stok bahan {$jenisRefill->nama} tidak cukup. Dibutuhkan {$totalUsage} {$jenisRefill->satuan_label}, tersedia {$jenisRefill->stok} {$jenisRefill->satuan_label}.",
                    ]);
            }

            DB::transaction(function () use ($registeredPayloads, $pelanggan, $jenisRefill, $teknisi, $request) {
                foreach ($registeredPayloads as $payload) {
                    /** @var UnitApar $unit */
                    $unit = $payload['unit'];
                    $biaya = (float) $payload['price'];

                    $pesanan = Pesanan::create([
                        'pelanggan_id' => $pelanggan->id,
                        'user_id' => $pelanggan->user_id,
                        'nama_penerima' => $pelanggan->nama,
                        'nomor_wa_penerima' => $pelanggan->no_wa,
                        'alamat_pengiriman' => $pelanggan->alamat,
                        'tipe' => 'service',
                        'service_jenis_layanan' => 'refill',
                        'service_jenis_refill_id' => $jenisRefill->id,
                        'service_jenis_apar' => $payload['apar_label'],
                        'service_ukuran_apar' => $payload['ukuran'],
                        'service_jumlah_unit' => 1,
                        'service_total_kg' => (float) $payload['usage'],
                        'service_metode_penanganan' => 'antar sendiri',
                        'sumber_pesanan' => 'datang_langsung',
                        'tanggal' => $request->tgl_refill,
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
                        'keterangan' => 'Status Unit: APAR Terdaftar | Unit: ' . ($unit->no_seri ?: ('UNIT-' . $unit->id)),
                    ]);

                    $service = \App\Models\Service::create([
                        'pesanan_id' => $pesanan->id,
                        'unit_apar_id' => $unit->id,
                        'jenis_service' => 'Refill APAR',
                        'tgl_service' => $request->tgl_refill,
                        'keterangan' => $request->catatan_admin,
                        'biaya' => $biaya,
                        'status_konfirmasi' => 'pending',
                    ]);

                    Refill::create([
                        'service_id' => $service->id,
                        'unit_apar_id' => $unit->id,
                        'jenis_refill_id' => $jenisRefill->id,
                        'tgl_refill' => $request->tgl_refill,
                        'biaya' => $biaya,
                    ]);
                }
            });

            $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';
            $jumlahTransaksi = $registeredPayloads->count();
            $totalBiaya = (float) $registeredPayloads->sum('price');

            return redirect()
                ->route('admin.refill.index')
                ->with(
                    'success',
                    "Refill offline untuk {$jumlahTransaksi} unit APAR terdaftar berhasil disimpan. Total Rp "
                    . number_format($totalBiaya, 0, ',', '.')
                    . ". Status: Lunas & {$statusLabel}. Stok akan berkurang saat status Selesai Final."
                );
        }

        $ukuranApar = trim((string) $request->input('ukuran_apar', ''));
        $jumlahUnit = (int) $request->input('jumlah_unit', 0);

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

        $hargaPerUnit = $this->resolveOfflineRefillPrice($jenisRefill, $ukuranApar);
        if (is_null($hargaPerUnit) || $hargaPerUnit <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_refill_id' => 'Harga standar refill untuk jenis dan ukuran ini belum tersedia.',
                ]);
        }

        $totalBiaya = $hargaPerUnit * $jumlahUnit;
        $angka = (float) filter_var($ukuranApar, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $jumlahPakai = ($angka > 0 ? $angka : 1.0) * $jumlahUnit;
        $bahanRefill = $this->normalizeOfflineRefillBahan($jenisRefill->nama_label);
        $produkUntukUnitBaru = $this->resolveOfflineUnitProduct($ukuranApar, $bahanRefill);

        if (! $produkUntukUnitBaru) {
            return back()
                ->withInput()
                ->withErrors([
                    'ukuran_apar' => 'Produk APAR dengan media dan ukuran yang dipilih belum tersedia. Tambahkan produk yang sesuai terlebih dahulu agar unit APAR otomatis bisa dibuat saat finalisasi.',
                ]);
        }

        if ($jenisRefill->stok < $jumlahPakai) {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_refill_id' => "Stok bahan {$jenisRefill->nama} tidak cukup. Dibutuhkan {$jumlahPakai} {$jenisRefill->satuan_label}, tersedia {$jenisRefill->stok} {$jenisRefill->satuan_label}.",
                ]);
        }

        DB::transaction(function () use ($request, $jenisRefill, $jumlahPakai, $jumlahUnit, $totalBiaya, $teknisi, $pelanggan, $bahanRefill, $ukuranApar, $tanggalRefill) {
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelanggan->id,
                'user_id' => $pelanggan->user_id,
                'nama_penerima' => $pelanggan->nama,
                'nomor_wa_penerima' => $pelanggan->no_wa,
                'alamat_pengiriman' => $pelanggan->alamat,
                'tipe' => 'service',
                'service_jenis_layanan' => 'refill',
                'service_jenis_refill_id' => $jenisRefill->id,
                'service_jenis_apar' => $bahanRefill,
                'service_ukuran_apar' => $ukuranApar,
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
                'keterangan' => "Status Unit: APAR Tidak Terdaftar | Detail: APAR {$bahanRefill} {$ukuranApar} | Jumlah Unit: {$jumlahUnit} unit",
            ]);

            $service = \App\Models\Service::create([
                'pesanan_id' => $pesanan->id,
                'unit_apar_id' => null,
                'jenis_service' => 'Refill APAR',
                'tgl_service' => $tanggalRefill,
                'keterangan' => $request->catatan_admin,
                'biaya' => $totalBiaya,
                'status_konfirmasi' => 'pending',
            ]);

            // Untuk APAR tidak terdaftar, Unit APAR baru belum ada pada tahap input awal.
            // Log refill akan dibentuk aman saat status transaksi mencapai Selesai Final.
        });

        $statusLabel = $teknisi ? 'Ditugaskan ke Teknisi' : 'Diproses';

        return redirect()
            ->route('admin.refill.index')
            ->with('success', "Refill offline berhasil disimpan. Status: Lunas & {$statusLabel}. Unit APAR untuk transaksi tidak terdaftar akan dibuat saat status Selesai Final, dan stok akan berkurang pada tahap tersebut.");
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
        $units = UnitApar::query()->visible()->with(['pelanggan', 'produk'])->get();
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

        return redirect()->route('admin.refill.index')->with('success', 'Data refill APAR berhasil diperbarui.');
    }

    public function destroy(Refill $refill)
    {
        $refill->delete();

        return redirect()->route('admin.refill.index')->with('success', 'Data refill APAR berhasil dihapus.');
    }

    public function assignTeknisi(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->service_jenis_layanan !== 'refill') {
            return back()->with('error', 'Data ini bukan refill APAR.');
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
            return back()->with('error', 'Data ini bukan refill APAR.');
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

        $previousStatus = (string) $pesanan->status;
        $pesanan->update($payload);

        if (
            $pesanan->status === Pesanan::STATUS_SELESAI_FINAL
            && ($previousStatus !== Pesanan::STATUS_SELESAI_FINAL || ! $pesanan->stok_dikurangi)
        ) {
            app(FinalTransactionStockService::class)->apply($pesanan);
        }

        return back()->with('success', 'Status refill APAR berhasil diperbarui.');
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

    protected function normalizeOfflineRefillBahan(string $label): string
    {
        return match (strtolower(trim($label))) {
            'powder' => 'Powder',
            'co2' => 'CO2',
            'foam' => 'Foam',
            default => trim($label),
        };
    }

    protected function resolveOfflineUnitProduct(string $ukuran, string $bahan): ?Produk
    {
        $ukuran = trim($ukuran);
        $bahan = trim($bahan);

        return Produk::query()
            ->where('kapasitas', $ukuran)
            ->whereHas('jenisApar', fn ($query) => $query->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($bahan) . '%']))
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
                    ->visible()
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
