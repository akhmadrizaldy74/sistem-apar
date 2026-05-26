<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Collection;

class OrderPricingService
{
    private const DISCOUNT_TIERS = [
        50 => 25,
        35 => 20,
        20 => 15,
        10 => 10,
        5 => 5,
    ];

    public function discountPercentForTotalUnit(int $totalUnit): int
    {
        foreach (self::DISCOUNT_TIERS as $minimumUnit => $percent) {
            if ($totalUnit >= $minimumUnit) {
                return $percent;
            }
        }

        return 0;
    }

    public function nextDiscountTier(int $totalUnit): ?array
    {
        $tiers = array_reverse(self::DISCOUNT_TIERS, true);

        foreach ($tiers as $minimumUnit => $percent) {
            if ($totalUnit < $minimumUnit) {
                return [
                    'minimum_unit' => (int) $minimumUnit,
                    'percent' => (int) $percent,
                    'remaining_unit' => max(0, (int) $minimumUnit - $totalUnit),
                ];
            }
        }

        return null;
    }

    public function summarizeCart(Collection $cartItems, float $ongkir = 0): array
    {
        return $this->summarizeProductItems($cartItems->map(function ($item) {
            return [
                'produk_id' => (int) ($item->produk_id ?? 0),
                'jumlah' => (int) ($item->qty ?? 0),
                'harga' => (float) ($item->harga ?? 0),
                'subtotal' => (float) (($item->harga ?? 0) * ($item->qty ?? 0)),
            ];
        }), $ongkir);
    }

    public function summarizeProductItems(iterable $items, float $ongkir = 0): array
    {
        $normalizedItems = collect($items)
            ->map(function ($item) {
                $qty = (int) data_get($item, 'jumlah', data_get($item, 'qty', 0));
                $harga = (float) data_get($item, 'harga', 0);
                $subtotal = (float) data_get($item, 'subtotal', $harga * $qty);

                return [
                    'produk_id' => (int) data_get($item, 'produk_id', 0),
                    'jumlah' => max(0, $qty),
                    'harga' => max(0, $harga),
                    'subtotal' => max(0, $subtotal),
                ];
            })
            ->filter(fn (array $item) => $item['jumlah'] > 0)
            ->values();

        $subtotalProduk = (float) $normalizedItems->sum('subtotal');
        $totalUnit = (int) $normalizedItems->sum('jumlah');
        $diskonPersen = $this->discountPercentForTotalUnit($totalUnit);
        $nominalDiskon = (float) round($subtotalProduk * ($diskonPersen / 100), 0);
        $ongkirValue = max(0, (float) $ongkir);
        $totalPembayaran = max(0, (float) round($subtotalProduk - $nominalDiskon + $ongkirValue, 0));
        $nextTier = $this->nextDiscountTier($totalUnit);

        return [
            'items' => $normalizedItems,
            'subtotalProduk' => $subtotalProduk,
            'totalUnit' => $totalUnit,
            'diskonPersen' => $diskonPersen,
            'nominalDiskon' => $nominalDiskon,
            'ongkir' => $ongkirValue,
            'totalPembayaran' => $totalPembayaran,
            'hasItems' => $normalizedItems->isNotEmpty(),
            'nextDiscountTier' => $nextTier['minimum_unit'] ?? null,
            'nextDiscountPercent' => $nextTier['percent'] ?? null,
            'nextDiscountUnitsNeeded' => $nextTier['remaining_unit'] ?? null,
        ];
    }

    public function summarizePesanan(Pesanan $pesanan): array
    {
        if ($pesanan->tipe === 'produk') {
            $details = $pesanan->relationLoaded('details')
                ? $pesanan->details
                : $pesanan->details()->get(['produk_id', 'jumlah', 'harga', 'subtotal']);

            return $this->summarizeProductItems($details, (float) ($pesanan->ongkir ?? 0));
        }

        $subtotalProduk = (float) ($pesanan->service_estimasi_biaya ?: $pesanan->total_harga ?: $pesanan->total ?: 0);
        $ongkir = (float) ($pesanan->ongkir ?? 0);
        $totalPembayaran = max(0, (float) round($subtotalProduk + $ongkir, 0));

        return [
            'items' => collect(),
            'subtotalProduk' => $subtotalProduk,
            'totalUnit' => max(0, (int) ($pesanan->service_jumlah_unit ?? 0)),
            'diskonPersen' => 0,
            'nominalDiskon' => 0.0,
            'ongkir' => $ongkir,
            'totalPembayaran' => $totalPembayaran,
            'hasItems' => $subtotalProduk > 0,
            'nextDiscountTier' => null,
            'nextDiscountPercent' => null,
            'nextDiscountUnitsNeeded' => null,
        ];
    }
}
