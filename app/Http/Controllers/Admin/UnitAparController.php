<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\UnitApar;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UnitAparController extends Controller
{
    protected const NEAR_EXPIRY_DAYS = 30;

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
            'expired' => 'Masa Berlaku Habis',
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
            'notice_class' => $noticeClass,
            'notice_text' => $noticeText,
        ];
    }

    protected function transformUnitForIndex(UnitApar $unit): array
    {
        $statusMeta = $this->resolveUnitStatusMeta($unit->tgl_expired);
        $tanggalDasar = $unit->tgl_produksi ?? $unit->tgl_beli;

        return [
            'id' => (int) $unit->id,
            'pelanggan_id' => (string) $unit->pelanggan_id,
            'pelanggan_nama' => (string) ($unit->pelanggan?->nama ?? '-'),
            'no_seri' => (string) ($unit->no_seri ?: '-'),
            'produk_nama' => (string) ($unit->produk?->nama ?? '-'),
            'ukuran' => (string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: '-'),
            'bahan' => (string) ($unit->bahan ?: $unit->produk?->jenisApar?->nama ?: '-'),
            'tgl_dasar_label' => optional($tanggalDasar)->format('d M Y') ?: '-',
            'tgl_expired_label' => optional($unit->tgl_expired)->format('d M Y') ?: '-',
            'status' => $statusMeta['key'],
            'status_label' => $statusMeta['label'],
            'search_text' => strtolower(trim(($unit->pelanggan?->nama ?? '') . ' ' . ($unit->no_seri ?? ''))),
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

    protected function buildSummary(Collection $unitItems): array
    {
        return [
            'total' => $unitItems->count(),
            'aktif' => $unitItems->where('status', 'aktif')->count(),
            'hampir' => $unitItems->where('status', 'hampir')->count(),
            'expired' => $unitItems->where('status', 'expired')->count(),
        ];
    }

    protected function visibleCustomerQuery(): Builder
    {
        return Pelanggan::query()
            ->visibleInDirectory()
            ->orderBy('nama');
    }

    protected function ensureUnitVisible(UnitApar $unitApar): void
    {
        abort_if($unitApar->isHiddenFromListings(), 404);
    }

    public function index()
    {
        $units = UnitApar::query()
            ->visible()
            ->with(['pelanggan', 'produk.jenisApar'])
            ->whereHas('pelanggan', fn (Builder $query) => $query->visibleInDirectory())
            ->latest('tgl_beli')
            ->get();

        $unitItems = $units
            ->map(fn (UnitApar $unit) => $this->transformUnitForIndex($unit))
            ->values();

        $pelanggans = $this->visibleCustomerQuery()
            ->get(['id', 'nama']);

        $summary = $this->buildSummary($unitItems);

        return view('admin.unit-apar.index', compact('unitItems', 'summary', 'pelanggans'));
    }

    public function show(UnitApar $unitApar)
    {
        $this->ensureUnitVisible($unitApar);

        $unit = $unitApar->load(['pelanggan', 'produk.jenisApar']);
        $statusMeta = $this->resolveUnitStatusMeta($unit->tgl_expired);

        return view('admin.unit-apar.show', compact('unit', 'statusMeta'));
    }

    public function create()
    {
        return redirect()->route('admin.unit-apar.index');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('admin.unit-apar.index')
            ->with('error', 'Registrasi manual unit APAR dinonaktifkan. Unit dibuat otomatis dari transaksi pelanggan yang selesai final.');
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
