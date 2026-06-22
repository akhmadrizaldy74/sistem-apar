<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Support\RegisteredRefillUnitSupport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProductExpiryAlertService
{
    public const FILTER_ALL = 'semua';
    public const FILTER_PROBLEM = 'masa-berlaku';
    public const FILTER_EXPIRING = 'hampir-expired';
    public const FILTER_EXPIRED = 'expired';

    private const PREVIEW_LIMIT = 5;

    public function adminDashboard(): array
    {
        $rows = $this->problemRows();
        $previewItems = $rows->take(self::PREVIEW_LIMIT)->values();
        $expiringCount = (int) $rows->where('status_key', 'hampir')->count();
        $expiredCount = (int) $rows->where('status_key', 'expired')->count();
        $totalIssueCount = (int) $rows->count();

        return [
            'hasIssues' => $totalIssueCount > 0,
            'headline' => $totalIssueCount > 0
                ? $totalIssueCount . ' produk APAR perlu perhatian masa berlaku.'
                : 'Belum ada stok APAR yang mendekati masa expired.',
            'summaryText' => $totalIssueCount > 0
                ? $expiringCount . ' produk hampir expired, ' . $expiredCount . ' produk sudah expired.'
                : 'Semua stok APAR masih aman dan siap dipantau seperti biasa.',
            'helperText' => $totalIssueCount > 0
                ? 'Buka daftar stok APAR bermasalah untuk memperbarui masa berlaku.'
                : 'Dashboard ini membantu admin memantau produk APAR yang perlu segera diperbarui masa berlakunya.',
            'safeMessage' => 'Belum ada stok APAR yang mendekati masa expired.',
            'totalIssueCount' => $totalIssueCount,
            'expiringCount' => $expiringCount,
            'expiredCount' => $expiredCount,
            'items' => $previewItems->all(),
            'remainingCount' => max(0, $totalIssueCount - $previewItems->count()),
            'warningFilter' => self::FILTER_PROBLEM,
        ];
    }

    public function stockPage(string $filter = self::FILTER_ALL): array
    {
        $normalizedFilter = $this->normalizeFilter($filter);
        $rows = $this->baseRows();
        $problemRows = $rows->filter(fn (array $row) => $row['has_issue'])->values();

        $filteredRows = match ($normalizedFilter) {
            self::FILTER_PROBLEM => $problemRows,
            self::FILTER_EXPIRING => $problemRows->where('status_key', 'hampir')->values(),
            self::FILTER_EXPIRED => $problemRows->where('status_key', 'expired')->values(),
            default => $rows,
        };

        return [
            'activeFilter' => $normalizedFilter,
            'rows' => $filteredRows->all(),
            'counts' => [
                self::FILTER_ALL => (int) $rows->count(),
                self::FILTER_PROBLEM => (int) $problemRows->count(),
                self::FILTER_EXPIRING => (int) $problemRows->where('status_key', 'hampir')->count(),
                self::FILTER_EXPIRED => (int) $problemRows->where('status_key', 'expired')->count(),
            ],
            'totalPhysicalStock' => (int) $rows->sum('stock_total_qty'),
            'problemStockQty' => (int) $problemRows->sum('stock_total_qty'),
            'problemProducts' => (int) $problemRows->count(),
            'helperText' => $normalizedFilter === self::FILTER_ALL
                ? 'Gunakan filter untuk fokus ke stok APAR yang hampir expired atau sudah expired.'
                : 'Daftar ini menampilkan seluruh produk APAR yang perlu diperbarui masa berlakunya.',
        ];
    }

    public function normalizeFilter(string $filter): string
    {
        $resolved = trim(strtolower($filter));

        if (in_array($resolved, [
            self::FILTER_ALL,
            self::FILTER_PROBLEM,
            self::FILTER_EXPIRING,
            self::FILTER_EXPIRED,
        ], true)) {
            return $resolved;
        }

        return self::FILTER_ALL;
    }

    public function warningLimit(?CarbonInterface $today = null): CarbonInterface
    {
        $baseDate = $today ? $today->copy()->startOfDay() : now()->startOfDay();

        return $baseDate->copy()->addDays(RegisteredRefillUnitSupport::REFILL_WARNING_DAYS);
    }

    public function problemBatchesForProduct(Produk $produk): Collection
    {
        $statusKey = $produk->activeStockStatusKey();

        if (! in_array($statusKey, ['hampir', 'expired'], true)) {
            return collect();
        }

        return $this->positiveBatches($produk);
    }

    private function problemRows(): Collection
    {
        return $this->baseRows()->filter(fn (array $row) => $row['has_issue'])->values();
    }

    private function baseRows(): Collection
    {
        $today = now()->startOfDay();
        $warningLimit = $this->warningLimit($today);

        return Produk::query()
            ->with(['jenisApar', 'stokBatches'])
            ->orderBy('nama')
            ->get()
            ->map(fn (Produk $produk) => $this->mapProductRow($produk, $today, $warningLimit))
            ->sortBy(fn (array $row) => sprintf(
                '%d|%s|%s',
                $row['status_sort_order'],
                $row['masa_berlaku_sort'] ?? '9999-12-31',
                $row['product_name']
            ))
            ->values();
    }

    private function mapProductRow(Produk $produk, CarbonInterface $today, CarbonInterface $warningLimit): array
    {
        $positiveBatches = $this->positiveBatches($produk);
        $referenceBatch = $positiveBatches->first();
        $statusMeta = $produk->activeStockStatusMeta();
        $statusKey = $referenceBatch ? (string) ($statusMeta['status_key'] ?? 'aman') : 'kosong';
        $statusLabel = match ($statusKey) {
            'expired' => 'Expired',
            'hampir' => 'Hampir Expired',
            'aman' => 'Aman',
            default => '-',
        };

        $stockTotalQty = (int) $positiveBatches->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));
        $expiryLabel = $referenceBatch?->tgl_expired?->copy()->locale('id')->isoFormat('D MMMM YYYY') ?? '-';

        $daysUntilExpiry = $statusMeta['days_until_expiry'] ?? null;
        $remainingLabel = $statusMeta['remaining_label'] ?? '-';

        $statusDetail = match ($statusKey) {
            'expired' => 'Sudah expired sejak ' . $expiryLabel,
            'hampir' => 'Sisa masa berlaku: ' . ($daysUntilExpiry === 0 ? 'hari ini' : ($remainingLabel . ' lagi')),
            'aman' => 'Aman, masa berlaku masih lebih dari 7 hari.',
            default => 'Belum ada stok APAR aktif.',
        };

        return [
            'product_id' => (int) $produk->id,
            'name' => (string) ($produk->nama ?: 'Produk APAR'),
            'product_name' => (string) ($produk->nama ?: 'Produk APAR'),
            'brand' => (string) ($produk->merek ?: '-'),
            'jenis_apar' => (string) ($produk->jenisApar?->nama ?: '-'),
            'kapasitas' => (string) ($produk->kapasitas ?: '-'),
            'stock_total_qty' => $stockTotalQty,
            'stock_total_label' => $this->formatStock($stockTotalQty) . ' unit',
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'status_badge_class' => $this->statusBadgeClass($statusKey),
            'status_text_class' => $this->statusTextClass($statusKey),
            'expiry_text_class' => $this->statusTextClass($statusKey),
            'status_sort_order' => match ($statusKey) {
                'expired' => 0,
                'hampir' => 1,
                'aman' => 2,
                default => 3,
            },
            'has_issue' => in_array($statusKey, ['expired', 'hampir'], true),
            'masa_berlaku_label' => $expiryLabel,
            'expired_at_label' => $expiryLabel,
            'masa_berlaku_sort' => $referenceBatch?->tgl_expired?->toDateString(),
            'remaining_label' => $remainingLabel,
            'status_detail' => $statusDetail,
            'primary_batch_id' => (int) ($referenceBatch?->id ?? 0),
            'can_add_stock' => $produk->canAddStockDirectly(),
            'blocked_add_stock_message' => $produk->blockedStockPurchaseMessage(),
            'modal' => [
                'primary_batch_id' => (int) ($referenceBatch?->id ?? 0),
                'product_name' => (string) ($produk->nama ?: 'Produk APAR'),
                'brand' => (string) ($produk->merek ?: '-'),
                'jenis_apar' => (string) ($produk->jenisApar?->nama ?: '-'),
                'kapasitas' => (string) ($produk->kapasitas ?: '-'),
                'stock_label' => $this->formatStock($stockTotalQty) . ' unit',
                'old_expiry_label' => $expiryLabel,
                'status_label' => $statusLabel,
                'status_detail' => $statusDetail,
                'default_refill_date' => $today->toDateString(),
                'preview_expiry_label' => UnitApar::calculateExpiry(
                    $today->toDateString(),
                    $produk->kapasitas,
                    $produk->jenisApar?->nama,
                )->locale('id')->isoFormat('D MMMM YYYY'),
            ],
        ];
    }

    private function positiveBatches(Produk $produk): Collection
    {
        return $produk->stokBatches
            ->filter(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0) > 0)
            ->sortBy(fn (StokBatch $batch) => sprintf(
                '%s|%010d',
                $batch->tgl_expired?->toDateString() ?? '9999-12-31',
                (int) $batch->id,
            ))
            ->values();
    }

    private function statusBadgeClass(string $statusKey): string
    {
        return match ($statusKey) {
            'expired' => 'bg-red-100 text-red-700',
            'hampir' => 'bg-amber-100 text-amber-800',
            'aman' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    private function statusTextClass(string $statusKey): string
    {
        return match ($statusKey) {
            'expired' => 'text-red-700',
            'hampir' => 'text-amber-700',
            'aman' => 'text-emerald-700',
            default => 'text-slate-500',
        };
    }

    private function formatStock(int $stock): string
    {
        return number_format($stock, 0, ',', '.');
    }
}
