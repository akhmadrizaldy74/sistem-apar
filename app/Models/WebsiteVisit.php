<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteVisit extends Model
{
    protected $fillable = [
        'visitor_id', 'session_id', 'page_url', 'ip_address', 'user_agent',
        'visited_at', 'event_type', 'product_id', 'page_title'
    ];

    protected $casts = ['visited_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Produk::class, 'product_id');
    }

    public static function recordVisit(
        string $visitorId,
        string $pageUrl,
        ?string $eventType = 'page_view',
        ?int $productId = null,
        ?string $pageTitle = null
    ): void {
        self::create([
            'visitor_id' => $visitorId,
            'page_url' => $pageUrl,
            'event_type' => $eventType,
            'product_id' => $productId,
            'page_title' => $pageTitle,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent(), 0, 500),
            'visited_at' => now(),
        ]);
    }

    public static function trackProductView(string $visitorId, int $productId, string $productName): void
    {
        self::recordVisit(
            $visitorId,
            '/produk/' . $productId,
            'product_view',
            $productId,
            $productName
        );
    }

    public static function trackAddToCart(string $visitorId, int $productId, string $productName, int $qty = 1): void
    {
        self::recordVisit(
            $visitorId,
            '/keranjang',
            'add_to_cart',
            $productId,
            $productName . ' (x' . $qty . ')'
        );
    }

    public static function trackPageView(string $visitorId, string $pageUrl, ?string $pageTitle = null): void
    {
        self::recordVisit($visitorId, $pageUrl, 'page_view', null, $pageTitle);
    }

    public static function getUniqueVisitors(?string $from = null, ?string $to = null): int
    {
        return self::query()
            ->when($from, fn($q) => $q->whereDate('visited_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('visited_at', '<=', $to))
            ->distinct('visitor_id')
            ->count('visitor_id');
    }

    public static function getTotalPageViews(?string $from = null, ?string $to = null): int
    {
        return self::query()
            ->when($from, fn($q) => $q->whereDate('visited_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('visited_at', '<=', $to))
            ->count();
    }

    public static function getTodayVisitors(): int
    {
        return self::getUniqueVisitors(now()->toDateString(), now()->toDateString());
    }

    public static function getThisMonthVisitors(): int
    {
        return self::getUniqueVisitors(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());
    }

    public static function getMostViewedProducts(?string $from = null, ?string $to = null, int $limit = 10)
    {
        return self::query()
            ->where('event_type', 'product_view')
            ->whereNotNull('product_id')
            ->when($from, fn($q) => $q->whereDate('visited_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('visited_at', '<=', $to))
            ->selectRaw('product_id, COUNT(*) as view_count')
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    public static function getMostAddedToCart(?string $from = null, ?string $to = null, int $limit = 10)
    {
        return self::query()
            ->where('event_type', 'add_to_cart')
            ->whereNotNull('product_id')
            ->when($from, fn($q) => $q->whereDate('visited_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('visited_at', '<=', $to))
            ->selectRaw('product_id, COUNT(*) as cart_count')
            ->groupBy('product_id')
            ->orderByDesc('cart_count')
            ->limit($limit)
            ->get();
    }

    public static function getLabeledPageUrl(?string $pageUrl, ?string $pageTitle, ?int $productId): array
    {
        $label = match (true) {
            str_contains($pageUrl, '/produk/') && $productId => [
                'activity' => 'Melihat Produk',
                'detail' => $pageTitle ?? 'Produk #' . $productId,
            ],
            $pageUrl === '/' => ['activity' => 'Membuka Beranda', 'detail' => 'Halaman Utama'],
            str_contains($pageUrl, 'produk') => ['activity' => 'Melihat Daftar Produk', 'detail' => 'Katalog Produk'],
            str_contains($pageUrl, 'keranjang') => ['activity' => 'Membuka Keranjang', 'detail' => 'Keranjang Belanja'],
            str_contains($pageUrl, 'order') || str_contains($pageUrl, 'checkout') => ['activity' => 'Form Pemesanan', 'detail' => 'Checkout'],
            str_contains($pageUrl, 'complain') => ['activity' => 'Mengirim Komplain', 'detail' => 'Form Komplain'],
            str_contains($pageUrl, 'riwayat') => ['activity' => 'Melihat Riwayat', 'detail' => 'Riwayat Pesanan'],
            default => ['activity' => 'Membuka Halaman', 'detail' => $pageTitle ?? $pageUrl ?? '-'],
        };

        return $label;
    }
}