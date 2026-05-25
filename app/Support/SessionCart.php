<?php

namespace App\Support;

use App\Models\Produk;
use Illuminate\Support\Collection;

class SessionCart
{
    private const SESSION_KEY = 'cart.items';

    public static function items(): Collection
    {
        $rawItems = collect(self::raw());

        if ($rawItems->isEmpty()) {
            return collect();
        }

        $produkMap = Produk::with('jenisApar')
            ->whereIn('id', $rawItems->pluck('produk_id')->map(fn ($id) => (int) $id)->filter()->unique())
            ->get()
            ->keyBy('id');

        return $rawItems
            ->map(function (array $item) use ($produkMap) {
                $produk = $produkMap->get((int) ($item['produk_id'] ?? 0));
                if (! $produk) {
                    return null;
                }

                return (object) [
                    'id' => (string) $produk->id,
                    'produk_id' => (int) $produk->id,
                    'qty' => max(1, (int) ($item['qty'] ?? 1)),
                    'harga' => (float) ($item['harga'] ?? $produk->harga ?? 0),
                    'tipe_item' => 'produk',
                    'produk' => $produk,
                ];
            })
            ->filter()
            ->values();
    }

    public static function count(): int
    {
        return (int) self::items()->sum('qty');
    }

    public static function add(Produk $produk, int $qty = 1): void
    {
        $items = self::raw();
        $index = collect($items)->search(fn (array $item) => (int) ($item['produk_id'] ?? 0) === (int) $produk->id);

        if ($index !== false) {
            $items[$index]['qty'] = (int) ($items[$index]['qty'] ?? 0) + $qty;
            $items[$index]['harga'] = (float) ($items[$index]['harga'] ?? $produk->harga ?? 0);
        } else {
            $items[] = [
                'produk_id' => (int) $produk->id,
                'qty' => $qty,
                'harga' => (float) ($produk->harga ?? 0),
                'tipe_item' => 'produk',
            ];
        }

        self::store($items);
    }

    public static function update(int $produkId, int $qty): bool
    {
        $items = self::raw();
        $index = collect($items)->search(fn (array $item) => (int) ($item['produk_id'] ?? 0) === $produkId);

        if ($index === false) {
            return false;
        }

        $items[$index]['qty'] = max(1, $qty);
        self::store($items);

        return true;
    }

    public static function remove(int $produkId): bool
    {
        $items = self::raw();
        $filtered = array_values(array_filter($items, fn (array $item) => (int) ($item['produk_id'] ?? 0) !== $produkId));

        if (count($filtered) === count($items)) {
            return false;
        }

        self::store($filtered);

        return true;
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    private static function raw(): array
    {
        $items = session(self::SESSION_KEY, []);

        return is_array($items) ? $items : [];
    }

    private static function store(array $items): void
    {
        if (empty($items)) {
            self::clear();

            return;
        }

        session([self::SESSION_KEY => array_values($items)]);
    }
}
