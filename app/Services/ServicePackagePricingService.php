<?php

namespace App\Services;

use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\ServicePaket;
use App\Models\UnitApar;
use Illuminate\Support\Collection;

class ServicePackagePricingService
{
    private const PACKAGE_RULES = [
        'A' => ['base' => 20000, 'per_kg' => 10000],
        'B' => ['base' => 30000, 'per_kg' => 15000],
        'C' => ['base' => 50000, 'per_kg' => 25000],
    ];

    private const MEDIA_MULTIPLIERS = [
        'powder' => 1.0,
        'foam' => 1.1,
        'co2' => 1.2,
        'clean_agent' => 1.25,
    ];

    public function availableMediaOptions(): array
    {
        $mediaMap = [];

        Produk::query()
            ->with('jenisApar')
            ->whereNotNull('kapasitas')
            ->get()
            ->each(function (Produk $produk) use (&$mediaMap) {
                $this->pushMediaSize(
                    $mediaMap,
                    (string) ($produk->jenisApar?->nama ?? ''),
                    (string) ($produk->kapasitas ?? ''),
                );
            });

        UnitApar::query()
            ->with('produk.jenisApar')
            ->get()
            ->each(function (UnitApar $unitApar) use (&$mediaMap) {
                $this->pushMediaSize(
                    $mediaMap,
                    (string) ($unitApar->produk?->jenisApar?->nama ?: $unitApar->bahan ?: ''),
                    (string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: ''),
                );
            });

        return collect($mediaMap)
            ->map(function (array $item, string $key) {
                $sizes = $this->sortSizes(array_values(array_unique($item['sizes'])));

                return [
                    'key' => $key,
                    'label' => $item['label'],
                    'sizes' => $sizes,
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    public function packageCatalog(Collection $servicePakets, ?array $mediaOptions = null): array
    {
        $mediaOptions ??= $this->availableMediaOptions();

        return $servicePakets
            ->map(function (ServicePaket $paket) use ($mediaOptions) {
                $peralatans = $this->resolveEstimatedPeralatan($paket, 1);
                $priceMatrix = [];

                foreach ($mediaOptions as $media) {
                    $priceMatrix[$media['key']] = [];

                    foreach (($media['sizes'] ?? []) as $size) {
                        $priceMatrix[$media['key']][$size] = $this->resolvePackagePrice(
                            $paket,
                            (string) ($media['label'] ?? $media['key']),
                            (string) $size,
                        );
                    }
                }

                return [
                    'id' => (int) $paket->id,
                    'label' => (string) ($paket->label ?? ''),
                    'nama' => (string) $paket->nama,
                    'harga' => (float) ($paket->harga ?? 0),
                    'rincian' => $paket->rincian_list,
                    'price_matrix' => $priceMatrix,
                    'peralatans' => array_map(function (array $item) {
                        return [
                            'peralatan_id' => (int) ($item['peralatan_id'] ?? 0),
                            'nama' => (string) ($item['nama'] ?? '-'),
                            'jumlah' => (int) ($item['jumlah_per_unit'] ?? 0),
                            'stok' => (float) ($item['stok'] ?? 0),
                            'stok_minimum' => (float) ($item['stok_minimum'] ?? 0),
                        ];
                    }, $peralatans),
                ];
            })
            ->values()
            ->all();
    }

    public function resolvePackagePrice(ServicePaket $paket, string $media, string $ukuran): float
    {
        $tier = $this->resolvePackageTier($paket);
        $rule = self::PACKAGE_RULES[$tier] ?? self::PACKAGE_RULES['B'];
        $mediaKey = $this->normalizeMediaKey($media);
        $multiplier = self::MEDIA_MULTIPLIERS[$mediaKey] ?? 1.05;
        $ukuranKg = $this->extractCapacityKg($ukuran);

        if ($ukuranKg <= 0) {
            return 0.0;
        }

        $price = ($rule['base'] + ($rule['per_kg'] * $ukuranKg)) * $multiplier;

        return (float) (ceil($price / 5000) * 5000);
    }

    public function summarizePackageOrder(ServicePaket $paket, array $lineSpecs): array
    {
        $lineItems = [];
        $totalUnits = 0;
        $totalPrice = 0.0;

        foreach ($lineSpecs as $index => $lineSpec) {
            $qty = max(1, (int) ($lineSpec['qty'] ?? 1));
            $media = (string) ($lineSpec['media'] ?? '');
            $ukuran = (string) ($lineSpec['ukuran'] ?? '');
            $unitPrice = $this->resolvePackagePrice($paket, $media, $ukuran);
            $lineTotal = $unitPrice * $qty;

            $lineItems[] = [
                'index' => $index + 1,
                'label' => trim((string) ($lineSpec['label'] ?? 'APAR ' . $media . ' ' . $ukuran)),
                'media' => $this->displayMediaLabel($media),
                'ukuran' => $ukuran,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
                'unit_id' => isset($lineSpec['unit_id']) ? (int) $lineSpec['unit_id'] : null,
            ];

            $totalUnits += $qty;
            $totalPrice += $lineTotal;
        }

        $peralatanItems = $this->resolveEstimatedPeralatan($paket, $totalUnits);
        $stockIssues = collect($peralatanItems)
            ->filter(fn (array $item) => (float) ($item['stok'] ?? 0) < (float) ($item['jumlah'] ?? 0))
            ->values()
            ->all();
        $lowStockItems = collect($peralatanItems)
            ->filter(function (array $item) {
                $remaining = (float) ($item['stok'] ?? 0) - (float) ($item['jumlah'] ?? 0);

                return (float) ($item['jumlah'] ?? 0) > 0
                    && $remaining <= (float) ($item['stok_minimum'] ?? 0);
            })
            ->values()
            ->all();

        return [
            'line_items' => $lineItems,
            'total_units' => $totalUnits,
            'total_price' => (float) round($totalPrice, 0),
            'peralatan_items' => $peralatanItems,
            'stock_issues' => $stockIssues,
            'low_stock_items' => $lowStockItems,
        ];
    }

    public function resolveEstimatedPeralatan(ServicePaket $paket, int $qtyMultiplier = 1): array
    {
        $paket->loadMissing('peralatans');

        $qtyMultiplier = max(1, $qtyMultiplier);
        $explicitPivotMap = $paket->peralatans
            ->keyBy(fn ($peralatan) => (int) $peralatan->id);

        if ($explicitPivotMap->isNotEmpty()) {
            return $explicitPivotMap
                ->map(function (Peralatan $peralatan) use ($qtyMultiplier) {
                    $jumlahPerUnit = max(1, (int) ($peralatan->pivot->jumlah_estimasi ?? 1));

                    return [
                        'peralatan_id' => (int) $peralatan->id,
                        'nama' => (string) $peralatan->nama,
                        'jumlah_per_unit' => $jumlahPerUnit,
                        'jumlah' => $jumlahPerUnit * $qtyMultiplier,
                        'stok' => (float) ($peralatan->stok ?? 0),
                        'stok_minimum' => (float) ($peralatan->stok_minimum ?? 0),
                        'harga_standar' => (float) ($peralatan->harga_standar ?? 0),
                    ];
                })
                ->values()
                ->all();
        }

        if (! $paket->isLegacyTemplate()) {
            return [];
        }

        $tierIndex = $this->packageTierIndex($paket);

        return Peralatan::query()
            ->orderBy('nama')
            ->get()
            ->map(function (Peralatan $peralatan) use ($tierIndex, $explicitPivotMap, $qtyMultiplier) {
                $peralatanTier = $this->equipmentTierIndex((string) $peralatan->nama);
                $explicit = $explicitPivotMap->get((int) $peralatan->id);

                if (is_null($explicit) && $peralatanTier > $tierIndex) {
                    return null;
                }

                $jumlahPerUnit = $explicit
                    ? max(1, (int) ($explicit->pivot->jumlah_estimasi ?? 1))
                    : 1;

                return [
                    'peralatan_id' => (int) $peralatan->id,
                    'nama' => (string) $peralatan->nama,
                    'jumlah_per_unit' => $jumlahPerUnit,
                    'jumlah' => $jumlahPerUnit * $qtyMultiplier,
                    'stok' => (float) ($peralatan->stok ?? 0),
                    'stok_minimum' => (float) ($peralatan->stok_minimum ?? 0),
                    'harga_standar' => (float) ($peralatan->harga_standar ?? 0),
                ];
            })
            ->filter(fn (?array $item) => !is_null($item) && (int) ($item['jumlah'] ?? 0) > 0)
            ->values()
            ->all();
    }

    public function normalizeMediaKey(?string $value): string
    {
        $text = mb_strtolower(trim((string) $value));

        if ($text === '') {
            return 'unknown';
        }

        if (str_contains($text, 'powder') || str_contains($text, 'dry chemical') || str_contains($text, 'dcp')) {
            return 'powder';
        }

        if (str_contains($text, 'foam')) {
            return 'foam';
        }

        if (str_contains($text, 'co2') || str_contains($text, 'carbon')) {
            return 'co2';
        }

        if (str_contains($text, 'clean agent') || str_contains($text, 'halotron')) {
            return 'clean_agent';
        }

        return preg_replace('/[^a-z0-9]+/u', '_', $text) ?: 'unknown';
    }

    public function displayMediaLabel(?string $value): string
    {
        return match ($this->normalizeMediaKey($value)) {
            'powder' => 'Powder',
            'foam' => 'Foam',
            'co2' => 'CO2',
            'clean_agent' => 'Clean Agent',
            default => trim((string) $value) !== '' ? trim((string) $value) : 'APAR',
        };
    }

    public function resolvePackageTier(ServicePaket $paket): string
    {
        $source = mb_strtolower(trim((string) ($paket->label ?: $paket->nama)));

        return match (true) {
            str_contains($source, 'paket a'), str_contains($source, 'service ringan'), str_contains($source, 'inspeksi ringan') => 'A',
            str_contains($source, 'paket c'), str_contains($source, 'service lengkap') => 'C',
            default => 'B',
        };
    }

    private function packageTierIndex(ServicePaket $paket): int
    {
        return match ($this->resolvePackageTier($paket)) {
            'A' => 1,
            'B' => 2,
            'C' => 3,
            default => 2,
        };
    }

    private function equipmentTierIndex(string $name): int
    {
        $normalized = mb_strtolower(trim($name));

        if ($normalized === '') {
            return 2;
        }

        if (
            str_contains($normalized, 'safety pin')
            || str_contains($normalized, 'pin pengaman')
            || str_contains($normalized, 'segel')
            || str_contains($normalized, 'seal')
            || str_contains($normalized, 'o-ring')
            || str_contains($normalized, 'oring')
            || str_contains($normalized, 'baut')
        ) {
            return 1;
        }

        if (
            str_contains($normalized, 'valve')
            || str_contains($normalized, 'manometer')
            || str_contains($normalized, 'pressure gauge')
            || str_contains($normalized, 'gauge')
        ) {
            return 3;
        }

        return 2;
    }

    private function pushMediaSize(array &$mediaMap, string $media, string $size): void
    {
        $size = trim($size);
        if ($size === '') {
            return;
        }

        $key = $this->normalizeMediaKey($media);
        $label = $this->displayMediaLabel($media);

        if (!isset($mediaMap[$key])) {
            $mediaMap[$key] = [
                'label' => $label,
                'sizes' => [],
            ];
        }

        $mediaMap[$key]['sizes'][] = $size;
    }

    private function sortSizes(array $sizes): array
    {
        usort($sizes, function (string $left, string $right) {
            $leftValue = $this->extractCapacityKg($left) ?: 9999;
            $rightValue = $this->extractCapacityKg($right) ?: 9999;

            return $leftValue <=> $rightValue ?: strnatcasecmp($left, $right);
        });

        return $sizes;
    }

    private function extractCapacityKg(?string $value): float
    {
        if (!preg_match('/(\d+(?:[.,]\d+)?)/', (string) $value, $matches)) {
            return 0.0;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }
}
