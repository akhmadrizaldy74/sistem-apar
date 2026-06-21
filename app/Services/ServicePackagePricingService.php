<?php

namespace App\Services;

use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\ServicePaket;
use App\Models\UnitApar;
use App\Support\ServiceMasterCatalog;
use Illuminate\Support\Collection;

class ServicePackagePricingService
{
    private ?Collection $cachedJenisRefills = null;

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

        // Include sizes from JenisRefill service_price_rules_json
        // This ensures sizes like "5 kg" that may not exist in Produk/UnitApar
        // are still present in the price matrix
        $this->allJenisRefills()->each(function (JenisRefill $jenisRefill) use (&$mediaMap) {
            $rules = $jenisRefill->service_price_rules_json ?? [];
            foreach ($rules as $rule) {
                $ukuran = trim((string) ($rule['ukuran'] ?? ''));
                if ($ukuran !== '' && (float) ($rule['harga'] ?? 0) > 0) {
                    $this->pushMediaSize($mediaMap, (string) $jenisRefill->nama, $ukuran);
                }
            }
        });

        return collect($mediaMap)
            ->map(function (array $item, string $key) {
                // Deduplicate sizes by their numeric kg value to handle variations like "6 Kg" vs "6 kg"
                $uniqueSizes = [];
                $seenKg = [];
                foreach ($item['sizes'] as $size) {
                    $kg = $this->extractCapacityKg($size);
                    $kgKey = $kg > 0 ? (string) $kg : mb_strtolower(trim($size));
                    if (! isset($seenKg[$kgKey])) {
                        $seenKg[$kgKey] = true;
                        $uniqueSizes[] = $size;
                    }
                }
                $sizes = $this->sortSizes($uniqueSizes);

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
        // Try to resolve size-specific price from JenisRefill
        // This uses the service_price_rules_json which maps (jenis APAR + ukuran) -> harga
        if (trim($media) !== '' && trim($ukuran) !== '') {
            $jenisRefill = $this->findJenisRefillByMedia($media);
            if ($jenisRefill) {
                $sizePrice = $jenisRefill->resolveServicePrice($ukuran);
                if (! is_null($sizePrice) && $sizePrice > 0) {
                    return (float) $sizePrice;
                }
            }
        }

        // Fallback to flat paket price for packages without size-specific pricing
        // (e.g. Ganti Bracket, Ganti Valve - where no JenisRefill match exists)
        return max(0.0, (float) ($paket->harga ?? 0));
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
            ->whereIn('nama', ServiceMasterCatalog::canonicalPeralatanNames())
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
            str_contains($source, 'service ringan') => 'A',
            str_contains($source, 'ganti valve'), str_contains($source, 'pressure gauge') => 'C',
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

    /**
     * Find the JenisRefill record matching the given media name (Powder, CO2, Foam).
     */
    private function findJenisRefillByMedia(string $media): ?JenisRefill
    {
        $mediaKey = $this->normalizeMediaKey($media);

        if ($mediaKey === 'unknown' || $mediaKey === '') {
            return null;
        }

        return $this->allJenisRefills()->first(function (JenisRefill $jenisRefill) use ($mediaKey) {
            return $this->normalizeMediaKey((string) $jenisRefill->nama) === $mediaKey;
        });
    }

    /**
     * Cached loader for all JenisRefill records to avoid repeated queries.
     */
    private function allJenisRefills(): Collection
    {
        if ($this->cachedJenisRefills === null) {
            $this->cachedJenisRefills = JenisRefill::all();
        }

        return $this->cachedJenisRefills;
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
