<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackWebsiteVisitor
{
    private const COOKIE_NAME = 'apar_visitor_id';
    private const REQUEST_ATTRIBUTE = 'apar_tracking_visitor_id';
    private const COOKIE_LIFETIME_DAYS = 90;
    private const DEBOUNCE_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $visitorId = self::resolveVisitorId($request);

        $response = $next($request);

        if ($this->shouldTrack($request)) {
            try {
                $this->trackVisit($request, $visitorId);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->ensureCookieExists($request, $visitorId);

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        $path = trim($request->path(), '/');

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

    private function trackVisit(Request $request, string $visitorId): void
    {
        $pageUrl = $this->normalizePageUrl($request);

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

    private function normalizePageUrl(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? '/' : '/' . $path;
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

    private function ensureCookieExists(Request $request, string $visitorId): void
    {
        if (!$request->cookie(self::COOKIE_NAME)) {
            $secure = config('session.secure');

            if ($secure === null) {
                $secure = $request->isSecure();
            }

            cookie()->queue(
                cookie(
                    self::COOKIE_NAME,
                    $visitorId,
                    60 * 24 * self::COOKIE_LIFETIME_DAYS,
                    config('session.path', '/'),
                    config('session.domain'),
                    $secure,
                    (bool) config('session.http_only', true),
                    false,
                    config('session.same_site', 'lax')
                )
            );
        }
    }

    private static function resolveVisitorId(Request $request): string
    {
        $visitorId = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if (is_string($visitorId) && $visitorId !== '') {
            return $visitorId;
        }

        $visitorId = $request->cookie(self::COOKIE_NAME);

        if (!is_string($visitorId) || $visitorId === '') {
            $visitorId = self::generateVisitorId();
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $visitorId);

        return $visitorId;
    }

    private static function generateVisitorId(): string
    {
        return Str::uuid()->toString();
    }

    public static function getVisitorId(Request $request): ?string
    {
        $visitorId = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if (is_string($visitorId) && $visitorId !== '') {
            return $visitorId;
        }

        return $request->cookie(self::COOKIE_NAME);
    }

    public static function trackProductView(Request $request, int $productId, string $productName): void
    {
        $visitorId = self::resolveVisitorId($request);
        WebsiteVisit::trackProductView($visitorId, $productId, $productName);
    }

    public static function trackAddToCart(Request $request, int $productId, string $productName, int $qty = 1): void
    {
        $visitorId = self::resolveVisitorId($request);
        WebsiteVisit::trackAddToCart($visitorId, $productId, $productName, $qty);
    }
}
