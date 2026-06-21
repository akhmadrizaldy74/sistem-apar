<?php

namespace App\Services;

use App\Models\Pengeluaran;
use Illuminate\Support\Collection;

class StockPurchaseReferenceService
{
    public function syncAfterExpense(Pengeluaran $pengeluaran): void
    {
        if (
            $pengeluaran->jenis_pengeluaran === Pengeluaran::JENIS_PEMBELIAN_REFILL
            && $pengeluaran->jenisRefill
            && (float) ($pengeluaran->harga_beli ?? 0) > 0
        ) {
            $pengeluaran->jenisRefill->forceFill([
                'harga' => (float) $pengeluaran->harga_beli,
            ])->saveQuietly();

            return;
        }

        if (
            $pengeluaran->jenis_pengeluaran === Pengeluaran::JENIS_PEMBELIAN_PERALATAN
            && $pengeluaran->peralatan
            && (float) ($pengeluaran->harga_beli ?? 0) > 0
        ) {
            $pengeluaran->peralatan->forceFill([
                'harga_standar' => (float) $pengeluaran->harga_beli,
            ])->saveQuietly();
        }
    }

    public function latestProductPurchasePrices(iterable $productIds = []): Collection
    {
        $normalizedIds = collect($productIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        $rows = Pengeluaran::query()
            ->where('jenis_pengeluaran', Pengeluaran::JENIS_PEMBELIAN_APAR)
            ->whereNotNull('produk_id')
            ->when(
                $normalizedIds->isNotEmpty(),
                fn ($query) => $query->whereIn('produk_id', $normalizedIds->all())
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get(['produk_id', 'harga_beli']);

        return $rows
            ->unique(fn (Pengeluaran $pengeluaran) => (int) $pengeluaran->produk_id)
            ->mapWithKeys(fn (Pengeluaran $pengeluaran) => [
                (int) $pengeluaran->produk_id => (float) ($pengeluaran->harga_beli ?? 0),
            ]);
    }
}
