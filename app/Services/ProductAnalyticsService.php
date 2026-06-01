<?php

namespace App\Services;

use App\Models\PesananDetail;
use App\Models\WebsiteVisit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsService
{
    private const IGNORED_ORDER_STATUSES = [
        'ditolak',
        'batal',
        'cancelled',
        'canceled',
    ];

    private const COUNTED_ORDER_STATUSES = [
        'diproses',
        'menunggu pengambilan',
        'menunggu kedatangan unit',
        'ditugaskan ke teknisi',
        'dikerjakan teknisi',
        'selesai oleh teknisi',
        'dikonfirmasi admin',
        'selesai',
        'selesai final',
        'confirmed',
        'paid',
        'lunas',
    ];

    public function mostViewedProducts(?string $from = null, ?string $to = null, int $limit = 10): Collection
    {
        return WebsiteVisit::query()
            ->selectRaw('product_id, COUNT(*) as view_count')
            ->with('product.jenisApar')
            ->where('event_type', 'product_view')
            ->whereNotNull('product_id')
            ->when($from, fn ($query, $date) => $query->whereDate('visited_at', '>=', $date))
            ->when($to, fn ($query, $date) => $query->whereDate('visited_at', '<=', $date))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn (WebsiteVisit $item) => [
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->nama ?? 'Produk #' . $item->product_id,
                'jenis_apar' => $item->product?->jenisApar?->nama ?? '-',
                'ukuran' => $item->product?->kapasitas ?? '-',
                'merek' => $item->product?->merek ?? '-',
                'view_count' => (int) $item->view_count,
            ]);
    }

    public function mostSoldProducts(
        ?string $from = null,
        ?string $to = null,
        ?int $pelangganId = null,
        int $limit = 10
    ): Collection {
        return PesananDetail::query()
            ->selectRaw('produk_id, SUM(jumlah) as total_sold, SUM(COALESCE(subtotal, jumlah * harga, 0)) as total_revenue')
            ->with('produk.jenisApar')
            ->whereNotNull('produk_id')
            ->where('jumlah', '>', 0)
            ->whereHas('pesanan', function ($query) use ($from, $to, $pelangganId) {
                $query->where('tipe', 'produk')
                    ->where(function ($statusQuery) {
                        $statusQuery->whereNull('status')
                            ->orWhereNotIn(DB::raw('LOWER(status)'), self::IGNORED_ORDER_STATUSES);
                    })
                    ->where(function ($purchaseQuery) {
                        $purchaseQuery->whereIn(DB::raw('LOWER(status)'), self::COUNTED_ORDER_STATUSES)
                            ->orWhereNotNull('pembayaran_terkonfirmasi_at')
                            ->orWhere('metode_pembayaran', 'cash')
                            ->orWhere('sumber_pesanan', 'datang_langsung');
                    })
                    ->when($from, fn ($orderQuery, $date) => $orderQuery->whereDate(DB::raw('COALESCE(tanggal, created_at)'), '>=', $date))
                    ->when($to, fn ($orderQuery, $date) => $orderQuery->whereDate(DB::raw('COALESCE(tanggal, created_at)'), '<=', $date))
                    ->when($pelangganId, fn ($orderQuery, $id) => $orderQuery->where('pelanggan_id', $id));
            })
            ->groupBy('produk_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(fn (PesananDetail $item) => [
                'product_id' => (int) $item->produk_id,
                'product_name' => $item->produk?->nama ?? 'Produk #' . $item->produk_id,
                'jenis_apar' => $item->produk?->jenisApar?->nama ?? '-',
                'ukuran' => $item->produk?->kapasitas ?? '-',
                'merek' => $item->produk?->merek ?? '-',
                'total_sold' => (int) $item->total_sold,
                'total_revenue' => (float) $item->total_revenue,
            ]);
    }
}
