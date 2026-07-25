<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Collection;

class StockPurchaseReferenceService
{
    public function latestProductPurchasePrices(iterable $productIds = []): Collection
    {
        $normalizedIds = collect($productIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        $poDetails = PurchaseOrderDetail::query()
            ->where('kategori', 'produk')
            ->whereHas('purchaseOrder', function ($query) {
                $query->whereIn('status', ['dikirim', 'diterima']);
            })
            ->latest('id')
            ->get();

        $map = collect();
        $produks = Produk::all();

        foreach ($poDetails as $detail) {
            $cleanNama = explode(' - ', $detail->nama_item)[0];
            $matchedProduk = $produks->first(function (Produk $p) use ($cleanNama, $detail) {
                return str_contains(strtolower($p->nama), strtolower($cleanNama))
                    || str_contains(strtolower($detail->nama_item), strtolower($p->nama));
            });

            if ($matchedProduk && !$map->has($matchedProduk->id)) {
                if ($normalizedIds->isEmpty() || $normalizedIds->contains($matchedProduk->id)) {
                    $map->put($matchedProduk->id, (float) $detail->harga_satuan);
                }
            }
        }

        return $map;
    }
}
