<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisitor
{
    private const COOKIE_NAME = 'apar_visitor_id';
    private const COOKIE_LIFETIME_DAYS = 90;
    private const DEBOUNCE_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            $this->trackVisit($request);
        }

        $response = $next($request);
        $this->ensureCookieExists($request, $response);

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        $path = $request->path();

        $excludedPaths = [
            'admin', 'teknisi', 'login', 'logout', 'register',
            'password', 'api', '_debugbar', 'favicon', 'robots.txt',
            'dashboard', 'up', 'sanctum',
        ];

        foreach ($excludedPaths as $excluded) {
            if (str_starts_with($path, $excluded) || $path === $excluded) {
                return false;
            }
        }

        return true;
    }

    private function trackVisit(Request $request): void
    {
        $visitorId = $request->cookie(self::COOKIE_NAME) ?? $this->generateVisitorId($request);
        $pageUrl = '/' . $request->path();

        $debounceKey = 'last_visit:' . $visitorId . ':' . $pageUrl;
        $lastVisit = cache()->get($debounceKey);

        if ($lastVisit && now()->diffInMinutes($lastVisit) < self::DEBOUNCE_MINUTES) {
            return;
        }

        cache()->put($debounceKey, now(), now()->addMinutes(self::DEBOUNCE_MINUTES));

        $eventType = 'page_view';
        $productId = null;
        $pageTitle = null;

        if (preg_match('/^produk\/(\d+)$/', $request->path(), $matches)) {
            $productId = (int) $matches[1];
            $eventType = 'product_view';
            $pageTitle = $this->getProductName($productId);
        }

        WebsiteVisit::recordVisit($visitorId, $pageUrl, $eventType, $productId, $pageTitle);
    }

    private function getProductName(int $productId): ?string
    {
        try {
            $product = \App\Models\Produk::find($productId);
            return $product?->nama;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ensureCookieExists(Request $request, Response $response): void
    {
        if (!$request->cookie(self::COOKIE_NAME)) {
            $visitorId = $this->generateVisitorId($request);
            cookie()->queue(
                cookie(self::COOKIE_NAME, $visitorId, 60 * 24 * self::COOKIE_LIFETIME_DAYS, '/', null, true, false)
            );
        }
    }

    private function generateVisitorId(Request $request): string
    {
        return Str::uuid()->toString();
    }

    public static function getVisitorId(Request $request): ?string
    {
        return $request->cookie('apar_visitor_id');
    }

    public static function trackProductView(Request $request, int $productId, string $productName): void
    {
        $visitorId = $request->cookie(self::COOKIE_NAME) ?? Str::uuid()->toString();
        WebsiteVisit::trackProductView($visitorId, $productId, $productName);
    }

    public static function trackAddToCart(Request $request, int $productId, string $productName, int $qty = 1): void
    {
        $visitorId = $request->cookie(self::COOKIE_NAME) ?? Str::uuid()->toString();
        WebsiteVisit::trackAddToCart($visitorId, $productId, $productName, $qty);
    }
}