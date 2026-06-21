<?php

namespace App\Services;

use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Support\ServiceMasterCatalog;
use Illuminate\Support\Collection;

class StockAlertService
{
    private const PRODUCT_LOW_STOCK_THRESHOLD = 5;
    private const GENERIC_LOW_STOCK_THRESHOLD = 5;
    private const PREVIEW_LIMIT = 3;

    public function adminDashboard(): array
    {
        return $this->buildDashboardSummary([
            $this->buildProductGroup(),
            $this->buildRefillGroup(),
            $this->buildEquipmentGroup(),
        ], 'admin');
    }

    public function teknisiDashboard(): array
    {
        return $this->buildDashboardSummary([
            $this->buildRefillGroup(),
            $this->buildEquipmentGroup(),
        ], 'teknisi');
    }

    private function buildDashboardSummary(array $groups, string $audience): array
    {
        $groupCollection = collect($groups)
            ->filter(fn (array $group) => (int) ($group['issueCount'] ?? 0) > 0)
            ->values();

        $totalIssueCount = (int) $groupCollection->sum('issueCount');
        $totalEmptyCount = (int) $groupCollection->sum('emptyCount');
        $totalLowCount = (int) $groupCollection->sum('lowCount');

        return [
            'audience' => $audience,
            'hasIssues' => $groupCollection->isNotEmpty(),
            'headline' => $groupCollection->isNotEmpty()
                ? $this->buildHeadline($totalIssueCount, $totalEmptyCount, $totalLowCount)
                : 'Semua stok dalam kondisi aman',
            'safeMessage' => 'Semua stok dalam kondisi aman',
            'helperText' => $audience === 'admin'
                ? 'Cek Manajemen Stok untuk tindak lanjut pembelian atau penyesuaian stok.'
                : 'Koordinasikan ke admin bila ada stok kosong atau menipis sebelum pekerjaan berikutnya.',
            'totalIssueCount' => $totalIssueCount,
            'totalEmptyCount' => $totalEmptyCount,
            'totalLowCount' => $totalLowCount,
            'groups' => $groupCollection->all(),
        ];
    }

    private function buildProductGroup(): array
    {
        $threshold = self::PRODUCT_LOW_STOCK_THRESHOLD;

        $items = Produk::query()
            ->with('stokBatches')
            ->orderBy('nama')
            ->get()
            ->map(function (Produk $produk) use ($threshold) {
                $stock = (int) ($produk->stok_tersedia ?? 0);
                $status = $this->resolveStatus($stock, $threshold);

                if (! $status) {
                    return null;
                }

                return [
                    'name' => (string) ($produk->nama ?: 'Produk APAR'),
                    'meta' => $this->joinMeta([
                        (string) ($produk->merek ?? ''),
                        (string) ($produk->kapasitas ?? ''),
                    ]),
                    'stock' => $stock,
                    'stockLabel' => $this->formatStock($stock, 'unit'),
                    'status' => $status,
                    'statusLabel' => $status === 'empty' ? 'Kosong' : 'Menipis',
                    'sortOrder' => $status === 'empty' ? 0 : 1,
                ];
            })
            ->filter()
            ->sortBy(fn (array $item) => sprintf('%d|%012.2f|%s', $item['sortOrder'], (float) $item['stock'], $item['name']))
            ->values();

        return $this->buildGroup(
            key: 'products',
            label: 'Produk APAR',
            description: 'Stok jual dari batch APAR aktif.',
            tab: 'apar',
            items: $items
        );
    }

    private function buildRefillGroup(): array
    {
        $items = JenisRefill::query()
            ->orderBy('nama')
            ->get()
            ->map(function (JenisRefill $jenisRefill) {
                $stock = (float) ($jenisRefill->stok ?? 0);
                $threshold = $this->resolveThreshold($jenisRefill->stok_minimum);
                $status = $this->resolveStatus($stock, $threshold);

                if (! $status) {
                    return null;
                }

                return [
                    'name' => (string) ($jenisRefill->nama_label ?: $jenisRefill->nama ?: 'Refill'),
                    'meta' => 'Batas minimum ' . $this->formatStock($threshold, $jenisRefill->satuan_label),
                    'stock' => $stock,
                    'stockLabel' => $this->formatStock($stock, $jenisRefill->satuan_label),
                    'status' => $status,
                    'statusLabel' => $status === 'empty' ? 'Kosong' : 'Menipis',
                    'sortOrder' => $status === 'empty' ? 0 : 1,
                ];
            })
            ->filter()
            ->sortBy(fn (array $item) => sprintf('%d|%012.2f|%s', $item['sortOrder'], (float) $item['stock'], $item['name']))
            ->values();

        return $this->buildGroup(
            key: 'refills',
            label: 'Refill APAR',
            description: 'Bahan refill yang dipakai untuk transaksi service dan refill.',
            tab: 'refill',
            items: $items
        );
    }

    private function buildEquipmentGroup(): array
    {
        $peralatans = Peralatan::query()
            ->where('nama', 'not like', 'Arsip %')
            ->orderBy('nama')
            ->get();

        $items = collect(ServiceMasterCatalog::peralatanDefinitions())
            ->map(function (array $definition) use ($peralatans) {
                $canonicalName = (string) ($definition['name'] ?? 'Peralatan');
                $aliases = (array) ($definition['aliases'] ?? []);
                $matchedItems = $peralatans
                    ->filter(fn (Peralatan $peralatan) => ServiceMasterCatalog::matchesNameOrAlias($peralatan->nama, $canonicalName, $aliases))
                    ->values();

                if ($matchedItems->isEmpty()) {
                    return null;
                }

                $stock = (int) $matchedItems->sum(fn (Peralatan $peralatan) => (int) ($peralatan->stok ?? 0));
                $storedMinimum = (float) $matchedItems->max(fn (Peralatan $peralatan) => (float) ($peralatan->stok_minimum ?? 0));
                $threshold = max(
                    $this->resolveThreshold($definition['stok_minimum'] ?? 0),
                    $storedMinimum > 0 ? $storedMinimum : 0
                );
                $status = $this->resolveStatus($stock, $threshold);

                if (! $status) {
                    return null;
                }

                $mergedAliasCount = max(0, $matchedItems->count() - 1);
                $metaParts = [
                    'Batas minimum ' . $this->formatStock($threshold, 'unit'),
                ];

                if ($mergedAliasCount > 0) {
                    $metaParts[] = 'Gabungan ' . $mergedAliasCount . ' alias lama';
                }

                return [
                    'name' => $canonicalName,
                    'meta' => implode(' • ', $metaParts),
                    'stock' => $stock,
                    'stockLabel' => $this->formatStock($stock, 'unit'),
                    'status' => $status,
                    'statusLabel' => $status === 'empty' ? 'Kosong' : 'Menipis',
                    'sortOrder' => $status === 'empty' ? 0 : 1,
                ];
            })
            ->filter()
            ->sortBy(fn (array $item) => sprintf('%d|%012.2f|%s', $item['sortOrder'], (float) $item['stock'], $item['name']))
            ->values();

        return $this->buildGroup(
            key: 'equipment',
            label: 'Peralatan Service',
            description: 'Peralatan kerja yang dipakai teknisi untuk service APAR.',
            tab: 'peralatan',
            items: $items
        );
    }

    private function buildGroup(string $key, string $label, string $description, string $tab, Collection $items): array
    {
        $previewItems = $items->take(self::PREVIEW_LIMIT)->values();
        $emptyCount = (int) $items->where('status', 'empty')->count();
        $lowCount = (int) $items->where('status', 'low')->count();

        return [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'tab' => $tab,
            'issueCount' => (int) $items->count(),
            'emptyCount' => $emptyCount,
            'lowCount' => $lowCount,
            'summary' => $this->buildGroupSummary($emptyCount, $lowCount),
            'items' => $previewItems->all(),
            'remainingCount' => max(0, $items->count() - $previewItems->count()),
        ];
    }

    private function resolveThreshold($minimum): float
    {
        $resolvedMinimum = (float) ($minimum ?? 0);

        if ($resolvedMinimum > 0) {
            return $resolvedMinimum;
        }

        return self::GENERIC_LOW_STOCK_THRESHOLD;
    }

    private function resolveStatus(float|int $stock, float|int $threshold): ?string
    {
        if ($stock <= 0) {
            return 'empty';
        }

        if ($stock <= $threshold) {
            return 'low';
        }

        return null;
    }

    private function buildHeadline(int $totalIssueCount, int $totalEmptyCount, int $totalLowCount): string
    {
        $parts = [];

        if ($totalEmptyCount > 0) {
            $parts[] = $totalEmptyCount . ' kosong';
        }

        if ($totalLowCount > 0) {
            $parts[] = $totalLowCount . ' menipis';
        }

        return $totalIssueCount . ' item stok perlu perhatian: ' . implode(', ', $parts) . '.';
    }

    private function buildGroupSummary(int $emptyCount, int $lowCount): string
    {
        $parts = [];

        if ($emptyCount > 0) {
            $parts[] = $emptyCount . ' kosong';
        }

        if ($lowCount > 0) {
            $parts[] = $lowCount . ' menipis';
        }

        return implode(', ', $parts);
    }

    private function formatStock(float|int $stock, string $unit): string
    {
        $formatted = floor((float) $stock) === (float) $stock
            ? number_format((float) $stock, 0, ',', '.')
            : rtrim(rtrim(number_format((float) $stock, 2, ',', '.'), '0'), ',');

        return trim($formatted . ' ' . $unit);
    }

    private function joinMeta(array $parts): ?string
    {
        $filtered = collect($parts)
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->values()
            ->all();

        if ($filtered === []) {
            return null;
        }

        return implode(' • ', $filtered);
    }
}
