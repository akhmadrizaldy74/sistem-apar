<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UnitApar;
use App\Support\RegisteredRefillUnitSupport;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UnitAparController extends Controller
{
    protected const NEAR_EXPIRY_DAYS = 30;
    protected const DEFAULT_PER_PAGE = 10;
    protected const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    protected function generateUnitSerial(Pelanggan $pelanggan, Produk $produk, string $tanggalBeli): string
    {
        return UnitApar::generateSerialNumber($pelanggan, $tanggalBeli);
    }

    protected function normalizeDateInput(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        foreach (['Y-m-d', 'm/d/Y', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return $raw;
        }
    }

    protected function resolveUnitStatus(?CarbonInterface $expiredAt): string
    {
        if (! $expiredAt) {
            return 'aktif';
        }

        $today = now()->startOfDay();
        $expiredDate = Carbon::parse($expiredAt)->startOfDay();

        if ($expiredDate->lte($today)) {
            return 'expired';
        }

        if ($today->diffInDays($expiredDate, false) <= self::NEAR_EXPIRY_DAYS) {
            return 'hampir';
        }

        return 'aktif';
    }

    protected function resolveUnitStatusMeta(?CarbonInterface $expiredAt): array
    {
        $status = $this->resolveUnitStatus($expiredAt);
        $label = match ($status) {
            'expired' => 'Expired',
            'hampir' => 'Hampir Expired',
            default => 'Aktif',
        };

        $badgeClass = match ($status) {
            'expired' => 'bg-red-50 text-red-700 border border-red-200',
            'hampir' => 'bg-amber-50 text-amber-700 border border-amber-200',
            default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };

        $noticeClass = match ($status) {
            'expired' => 'bg-red-50 border-red-200 text-red-700',
            'hampir' => 'bg-amber-50 border-amber-200 text-amber-700',
            default => 'bg-emerald-50 border-emerald-200 text-emerald-700',
        };

        $dateClass = match ($status) {
            'expired' => 'text-red-700',
            'hampir' => 'text-amber-700',
            default => 'text-slate-900',
        };

        $noticeText = 'Masa berlaku unit dipantau otomatis oleh sistem.';
        if ($expiredAt) {
            $expiredLabel = Carbon::parse($expiredAt)->format('d M Y');

            if ($status === 'expired') {
                $noticeText = 'Unit ini sudah melewati masa berlaku pada ' . $expiredLabel . '.';
            } elseif ($status === 'hampir') {
                $daysLeft = now()->startOfDay()->diffInDays(Carbon::parse($expiredAt)->startOfDay(), false);
                $noticeText = 'Masa berlaku akan habis dalam ' . $daysLeft . ' hari, pada ' . $expiredLabel . '.';
            } else {
                $noticeText = 'Masa berlaku unit masih aktif sampai ' . $expiredLabel . '.';
            }
        }

        return [
            'key' => $status,
            'label' => $label,
            'badge_class' => $badgeClass,
            'date_class' => $dateClass,
            'notice_class' => $noticeClass,
            'notice_text' => $noticeText,
        ];
    }

    protected function resolveUnitBaseDate(UnitApar $unit): ?CarbonInterface
    {
        return $unit->tgl_beli ?? $unit->tgl_produksi;
    }

    protected function unitHistoryOrders(UnitApar $unit): Collection
    {
        return Pesanan::query()
            ->with([
                'service.servicePaket',
                'service.refill.jenisRefill',
                'serviceJenisRefill',
                'servicePaket',
            ])
            ->where('pelanggan_id', $unit->pelanggan_id)
            ->where('tipe', 'service')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (Pesanan $pesanan) => RegisteredRefillUnitSupport::orderReferencesUnit($pesanan, $unit))
            ->values();
    }

    protected function resolveUnitHistoryTimestamp(Pesanan $pesanan): ?CarbonInterface
    {
        $timezone = config('app.timezone');

        foreach ([
            $pesanan->service?->tgl_selesai_admin,
            $pesanan->teknisi_selesai_at,
            $pesanan->updated_at,
            $pesanan->created_at,
        ] as $candidate) {
            if ($candidate) {
                return $candidate->copy()->timezone($timezone);
            }
        }

        return null;
    }

    protected function buildUnitHistorySummary(Pesanan $pesanan, string $actionType): string
    {
        $title = trim((string) (
            $actionType === 'refill'
                ? ($pesanan->service?->refill?->jenisRefill?->nama_label ?: $pesanan->serviceJenisRefill?->nama_label ?: 'Refill APAR')
                : ($pesanan->service?->jenis_service ?: $pesanan->servicePaket?->nama ?: 'Service APAR')
        ));

        $source = trim((string) ($pesanan->service_keluhan ?: $pesanan->service?->keterangan ?: $pesanan->keterangan ?: ''));
        $lines = collect(preg_split('/\r\n|\r|\n/', $source) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->reject(fn ($line) => Str::startsWith(mb_strtolower($line), 'catatan pelanggan:'))
            ->take(3)
            ->implode(' | ');

        return $lines !== '' ? $lines : ($title !== '' ? $title : 'Tidak ada ringkasan layanan.');
    }

    protected function buildUnitHistoryEntry(Pesanan $pesanan, string $actionType): array
    {
        $timezone = config('app.timezone');
        $updatedAt = $this->resolveUnitHistoryTimestamp($pesanan);
        $createdAt = $pesanan->created_at?->copy()->timezone($timezone);
        $finishedAt = $pesanan->service?->tgl_selesai_admin?->copy()->timezone($timezone)
            ?: $pesanan->teknisi_selesai_at?->copy()->timezone($timezone);
        $serviceDate = $pesanan->service?->tgl_service?->copy()->timezone($timezone);
        $title = trim((string) (
            $actionType === 'refill'
                ? ($pesanan->service?->refill?->jenisRefill?->nama_label ?: $pesanan->serviceJenisRefill?->nama_label ?: 'Refill APAR')
                : ($pesanan->service?->jenis_service ?: $pesanan->servicePaket?->nama ?: 'Service APAR')
        ));

        return [
            'action_type' => $actionType,
            'action_label' => $actionType === 'refill' ? 'Refill' : 'Service',
            'title' => $title !== '' ? $title : ($actionType === 'refill' ? 'Refill APAR' : 'Service APAR'),
            'summary' => $this->buildUnitHistorySummary($pesanan, $actionType),
            'order_code' => $pesanan->orderCode(),
            'status_label' => $pesanan->publicStatusLabel(),
            'biaya' => (float) ($pesanan->service_estimasi_biaya ?: $pesanan->total_harga ?: $pesanan->total ?: 0),
            'updated_date_label' => $updatedAt?->format('d M Y') ?? '-',
            'updated_time_label' => $updatedAt?->format('H:i') ?? '-',
            'service_date_label' => $serviceDate?->format('d M Y') ?? '-',
            'created_at_label' => $createdAt?->format('d M Y, H:i') ?? '-',
            'finished_at_label' => $finishedAt?->format('d M Y, H:i') ?? '-',
        ];
    }

    protected function transformUnitForIndex(UnitApar $unit): array
    {
        $statusMeta = $this->resolveUnitStatusMeta($unit->tgl_expired);
        $tanggalMasuk = $this->resolveUnitBaseDate($unit);

        return [
            'id' => (int) $unit->id,
            'pelanggan_nama' => (string) ($unit->pelanggan?->nama ?? '-'),
            'no_seri' => (string) ($unit->no_seri ?: '-'),
            'produk_nama' => (string) ($unit->produk?->nama ?? '-'),
            'ukuran' => (string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: '-'),
            'bahan' => (string) ($unit->bahan ?: $unit->produk?->jenisApar?->nama ?: '-'),
            'tgl_masuk_label' => optional($tanggalMasuk)->format('d M Y') ?: '-',
            'tgl_expired_label' => optional($unit->tgl_expired)->format('d M Y') ?: '-',
            'status_label' => $statusMeta['label'],
            'status_badge_class' => $statusMeta['badge_class'],
            'expired_text_class' => $statusMeta['date_class'],
        ];
    }

    protected function normalizeBaseDateInput(Request $request): ?string
    {
        $tanggalDasar = $request->input('tanggal_dasar_masa_berlaku')
            ?: $request->input('tgl_produksi')
            ?: $request->input('tgl_beli')
            ?: $request->input('tanggal_beli');

        return $this->normalizeDateInput($tanggalDasar);
    }

    protected function normalizeKondisiAwal(?string $value, string $fallback = 'layak'): string
    {
        return match (trim((string) $value)) {
            'tidak_layak', 'tidak_aktif' => 'tidak_aktif',
            'perlu_servis' => 'perlu_servis',
            'layak' => 'layak',
            default => $fallback,
        };
    }

    protected function buildSummary(Collection $units): array
    {
        return [
            'total' => $units->count(),
            'aktif' => $units->filter(fn (UnitApar $unit) => $this->resolveUnitStatus($unit->tgl_expired) === 'aktif')->count(),
            'hampir' => $units->filter(fn (UnitApar $unit) => $this->resolveUnitStatus($unit->tgl_expired) === 'hampir')->count(),
            'expired' => $units->filter(fn (UnitApar $unit) => $this->resolveUnitStatus($unit->tgl_expired) === 'expired')->count(),
        ];
    }

    protected function visibleCustomerQuery(): Builder
    {
        return Pelanggan::query()
            ->visibleInDirectory()
            ->orderBy('nama');
    }

    protected function visibleProductQuery(): Builder
    {
        return Produk::query()
            ->whereHas('units', function (Builder $unitQuery) {
                $unitQuery->visible()
                    ->whereHas('pelanggan', fn (Builder $pelangganQuery) => $pelangganQuery->visibleInDirectory());
            })
            ->orderBy('nama');
    }

    protected function perPageOptions(): array
    {
        return self::PER_PAGE_OPTIONS;
    }

    protected function normalizePerPage(mixed $value): int
    {
        $perPage = (int) $value;

        return in_array($perPage, $this->perPageOptions(), true)
            ? $perPage
            : self::DEFAULT_PER_PAGE;
    }

    protected function statusOptions(): array
    {
        return [
            'semua' => 'Semua',
            'aktif' => 'Aktif',
            'hampir' => 'Hampir Expired',
            'expired' => 'Expired',
        ];
    }

    protected function normalizeStatusFilter(mixed $value): string
    {
        $status = trim((string) $value);

        return array_key_exists($status, $this->statusOptions())
            ? $status
            : 'semua';
    }

    protected function normalizeProductFilter(mixed $value): ?int
    {
        $produkId = (int) $value;

        return $produkId > 0 ? $produkId : null;
    }

    protected function normalizeDateMode(Request $request): string
    {
        $mode = trim((string) $request->input('tanggal_mode', ''));

        if (in_array($mode, ['all', 'single', 'range'], true)) {
            return $mode;
        }

        if ($request->filled('tanggal_mulai') || $request->filled('tanggal_selesai')) {
            return 'range';
        }

        if ($request->filled('tanggal')) {
            return 'single';
        }

        return 'all';
    }

    protected function normalizeIndexFilters(Request $request): array
    {
        $tanggalMode = $this->normalizeDateMode($request);
        $tanggal = $this->normalizeDateInput($request->input('tanggal'));
        $tanggalMulai = $this->normalizeDateInput($request->input('tanggal_mulai'));
        $tanggalSelesai = $this->normalizeDateInput($request->input('tanggal_selesai'));

        if ($tanggalMode === 'range' && $tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        return [
            'search' => trim((string) $request->input('search', '')),
            'status' => $this->normalizeStatusFilter($request->input('status')),
            'produk_id' => $this->normalizeProductFilter($request->input('produk_id')),
            'per_page' => $this->normalizePerPage($request->input('per_page')),
            'tanggal_mode' => $tanggalMode,
            'tanggal' => $tanggal,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ];
    }

    protected function baseUnitQuery(): Builder
    {
        return UnitApar::query()
            ->visible()
            ->with(['pelanggan', 'produk.jenisApar'])
            ->whereHas('pelanggan', fn (Builder $query) => $query->visibleInDirectory());
    }

    protected function applyBaseDateFilter(Builder $query, array $filters): void
    {
        if ($filters['tanggal_mode'] === 'single' && $filters['tanggal']) {
            $tanggal = $filters['tanggal'];

            $query->where(function (Builder $innerQuery) use ($tanggal) {
                $innerQuery->whereDate('tgl_beli', $tanggal)
                    ->orWhere(function (Builder $fallbackQuery) use ($tanggal) {
                        $fallbackQuery->whereNull('tgl_beli')
                            ->whereDate('tgl_produksi', $tanggal);
                    });
            });

            return;
        }

        if ($filters['tanggal_mode'] !== 'range') {
            return;
        }

        if ($filters['tanggal_mulai']) {
            $tanggalMulai = $filters['tanggal_mulai'];

            $query->where(function (Builder $innerQuery) use ($tanggalMulai) {
                $innerQuery->whereDate('tgl_beli', '>=', $tanggalMulai)
                    ->orWhere(function (Builder $fallbackQuery) use ($tanggalMulai) {
                        $fallbackQuery->whereNull('tgl_beli')
                            ->whereDate('tgl_produksi', '>=', $tanggalMulai);
                    });
            });
        }

        if ($filters['tanggal_selesai']) {
            $tanggalSelesai = $filters['tanggal_selesai'];

            $query->where(function (Builder $innerQuery) use ($tanggalSelesai) {
                $innerQuery->whereDate('tgl_beli', '<=', $tanggalSelesai)
                    ->orWhere(function (Builder $fallbackQuery) use ($tanggalSelesai) {
                        $fallbackQuery->whereNull('tgl_beli')
                            ->whereDate('tgl_produksi', '<=', $tanggalSelesai);
                    });
            });
        }
    }

    protected function applyIndexFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $keyword = $filters['search'];

            $query->where(function (Builder $innerQuery) use ($keyword) {
                $innerQuery->where('no_seri', 'like', '%' . $keyword . '%')
                    ->orWhereHas('pelanggan', fn (Builder $pelangganQuery) => $pelangganQuery->where('nama', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('produk', fn (Builder $produkQuery) => $produkQuery->where('nama', 'like', '%' . $keyword . '%'));
            });
        }

        if ($filters['produk_id']) {
            $query->where('produk_id', $filters['produk_id']);
        }

        $today = now()->startOfDay()->toDateString();
        $nearExpiryLimit = now()->startOfDay()->addDays(self::NEAR_EXPIRY_DAYS)->toDateString();

        match ($filters['status']) {
            'aktif' => $query->where(function (Builder $innerQuery) use ($nearExpiryLimit) {
                $innerQuery->whereNull('tgl_expired')
                    ->orWhereDate('tgl_expired', '>', $nearExpiryLimit);
            }),
            'hampir' => $query
                ->whereDate('tgl_expired', '>', $today)
                ->whereDate('tgl_expired', '<=', $nearExpiryLimit),
            'expired' => $query->whereDate('tgl_expired', '<=', $today),
            default => null,
        };

        $this->applyBaseDateFilter($query, $filters);
    }

    protected function applyIndexOrdering(Builder $query): void
    {
        $tanggalMasukSql = 'COALESCE(unit_apars.tgl_beli, unit_apars.tgl_produksi)';

        $query
            ->orderByRaw($tanggalMasukSql . ' desc')
            ->orderByDesc('unit_apars.id');
    }

    protected function formatFilterDateLabel(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->format('d M Y');
    }

    protected function buildActiveFilters(array $filters, Collection $produks): array
    {
        $activeFilters = [];

        if ($filters['search'] !== '') {
            $activeFilters[] = [
                'label' => 'Cari',
                'value' => $filters['search'],
            ];
        }

        if ($filters['status'] !== 'semua') {
            $activeFilters[] = [
                'label' => 'Status',
                'value' => $this->statusOptions()[$filters['status']] ?? 'Semua',
            ];
        }

        if ($filters['produk_id']) {
            $produkNama = $produks->firstWhere('id', $filters['produk_id'])?->nama;

            if ($produkNama) {
                $activeFilters[] = [
                    'label' => 'Produk',
                    'value' => $produkNama,
                ];
            }
        }

        if ($filters['tanggal_mode'] === 'single' && $filters['tanggal']) {
            $activeFilters[] = [
                'label' => 'Tanggal',
                'value' => $this->formatFilterDateLabel($filters['tanggal']),
            ];
        }

        if ($filters['tanggal_mode'] === 'range' && ($filters['tanggal_mulai'] || $filters['tanggal_selesai'])) {
            $activeFilters[] = [
                'label' => 'Tanggal',
                'value' => match (true) {
                    $filters['tanggal_mulai'] && $filters['tanggal_selesai'] => $this->formatFilterDateLabel($filters['tanggal_mulai']) . ' - ' . $this->formatFilterDateLabel($filters['tanggal_selesai']),
                    $filters['tanggal_mulai'] => 'Mulai ' . $this->formatFilterDateLabel($filters['tanggal_mulai']),
                    default => 'Sampai ' . $this->formatFilterDateLabel($filters['tanggal_selesai']),
                },
            ];
        }

        return $activeFilters;
    }

    protected function ensureUnitVisible(UnitApar $unitApar): void
    {
        abort_if($unitApar->isHiddenFromListings(), 404);
    }

    public function index(Request $request)
    {
        $filters = $this->normalizeIndexFilters($request);
        $produks = $this->visibleProductQuery()->get(['id', 'nama']);

        $baseQuery = $this->baseUnitQuery();
        $visibleUnitCount = (clone $baseQuery)->count('unit_apars.id');
        $this->applyIndexFilters($baseQuery, $filters);

        $summary = $this->buildSummary(
            (clone $baseQuery)->get(['unit_apars.id', 'unit_apars.tgl_expired'])
        );

        $units = clone $baseQuery;
        $this->applyIndexOrdering($units);

        $units = $units->paginate($filters['per_page'])->withQueryString();
        $units->setCollection(
            $units->getCollection()
                ->map(fn (UnitApar $unit) => $this->transformUnitForIndex($unit))
                ->values()
        );

        return view('admin.unit-apar.index', [
            'summary' => $summary,
            'filters' => $filters,
            'produks' => $produks,
            'statusOptions' => $this->statusOptions(),
            'perPageOptions' => $this->perPageOptions(),
            'units' => $units,
            'filteredUnitCount' => $units->total(),
            'visibleUnitCount' => $visibleUnitCount,
            'activeFilters' => $this->buildActiveFilters($filters, $produks),
        ]);
    }

    public function show(UnitApar $unitApar)
    {
        $this->ensureUnitVisible($unitApar);

        $unit = $unitApar->load([
            'pelanggan',
            'produk.jenisApar',
        ]);
        $statusMeta = $this->resolveUnitStatusMeta($unit->tgl_expired);
        $unitHistories = $this->unitHistoryOrders($unit)
            ->map(function (Pesanan $pesanan) {
                $actionType = $pesanan->service_jenis_layanan === 'refill' ? 'refill' : 'service';

                return $this->buildUnitHistoryEntry($pesanan, $actionType);
            })
            ->values();
        $refillHistories = $unitHistories->where('action_type', 'refill')->values();
        $serviceHistories = $unitHistories->where('action_type', 'service')->values();

        return view('admin.unit-apar.show', compact('unit', 'statusMeta', 'refillHistories', 'serviceHistories'));
    }

    public function create()
    {
        return redirect()->route('admin.unit-apar.index');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('admin.unit-apar.index')
            ->with('error', 'Registrasi manual unit APAR dinonaktifkan. Unit dibuat otomatis dari transaksi pelanggan setelah pembayaran valid, lalu ditampilkan penuh saat transaksi selesai final.');
    }

    public function edit(UnitApar $unitApar)
    {
        $this->ensureUnitVisible($unitApar);

        $pelanggans = $this->visibleCustomerQuery()->get();
        $produks = Produk::all();

        return view('admin.unit-apar.edit', compact('unitApar', 'pelanggans', 'produks'));
    }

    public function update(Request $request, UnitApar $unitApar)
    {
        $this->ensureUnitVisible($unitApar);

        $produkId = $request->input('produk_id') ?: $request->input('model_produk_id');
        $noSeri = $request->input('no_seri') ?: $request->input('kode_unit') ?: $request->input('nomor_seri_unit');
        $tanggalDasar = $this->normalizeBaseDateInput($request) ?: optional($unitApar->tgl_produksi ?? $unitApar->tgl_beli)->toDateString();
        $kondisiAwal = $request->input('kondisi_awal') ?: $request->input('kondisi_awal_unit');

        $request->merge([
            'produk_id' => $produkId,
            'no_seri' => trim((string) $noSeri) ?: null,
            'tanggal_dasar_masa_berlaku' => $tanggalDasar,
            'tgl_beli' => $tanggalDasar,
            'tgl_produksi' => $tanggalDasar,
            'kondisi_awal' => $this->normalizeKondisiAwal($kondisiAwal, $unitApar->kondisi_awal ?: 'layak'),
        ]);

        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'produk_id' => 'required|exists:produks,id',
            'no_seri' => 'nullable|unique:unit_apars,no_seri,' . $unitApar->id,
            'tanggal_dasar_masa_berlaku' => 'required|date',
            'kondisi_awal' => 'required|in:layak,tidak_aktif,perlu_servis',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'produk_id.required' => 'Produk wajib dipilih.',
            'tanggal_dasar_masa_berlaku.required' => 'Tanggal dasar masa berlaku wajib diisi.',
            'tanggal_dasar_masa_berlaku.date' => 'Format tanggal dasar masa berlaku tidak valid.',
            'kondisi_awal.required' => 'Kondisi awal wajib dipilih.',
            'no_seri.unique' => 'Nomor unit sudah digunakan.',
        ]);

        $data = $request->only([
            'pelanggan_id',
            'produk_id',
            'no_seri',
            'tgl_beli',
            'tgl_produksi',
            'kondisi_awal',
        ]);

        $produk = Produk::with('jenisApar')->findOrFail($request->produk_id);
        $pelanggan = $this->visibleCustomerQuery()->findOrFail($request->pelanggan_id);
        $effectiveDate = (string) $request->input('tanggal_dasar_masa_berlaku');

        $data['ukuran'] = $produk->kapasitas ?? '-';
        $data['bahan'] = $produk->jenisApar?->nama ?? '-';
        $data['tgl_beli'] = $effectiveDate;
        $data['tgl_produksi'] = $effectiveDate;
        $data['kondisi_awal'] = $this->normalizeKondisiAwal($request->input('kondisi_awal'), $unitApar->kondisi_awal ?: 'layak');
        $data['no_seri'] = trim((string) ($data['no_seri'] ?? '')) !== ''
            ? $data['no_seri']
            : ($unitApar->no_seri ?: $this->generateUnitSerial($pelanggan, $produk, $effectiveDate));
        $data['tgl_expired'] = UnitApar::calculateExpiry($effectiveDate, $data['ukuran'], $data['bahan']);

        $unitApar->update($data);

        return redirect()->route('admin.unit-apar.index')->with('success', 'Unit APAR berhasil diperbarui.');
    }

    public function destroy(UnitApar $unitApar)
    {
        if (! $unitApar->hideFromListings()) {
            return redirect()
                ->route('admin.unit-apar.index')
                ->with('error', 'Unit APAR belum bisa disembunyikan. Jalankan migrasi terbaru terlebih dahulu.');
        }

        return redirect()
            ->route('admin.unit-apar.index')
            ->with('success', 'Unit APAR berhasil disembunyikan dari daftar.');
    }
}
