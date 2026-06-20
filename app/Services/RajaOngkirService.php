<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    private const DESTINATION_CACHE_MINUTES = 1440;
    private const COST_CACHE_MINUTES = 20;
    private const REQUEST_TIMEOUT_SECONDS = 10;

    public function searchDestination(string $keyword, int $limit = 10): array
    {
        $keyword = trim($keyword);
        if (mb_strlen($keyword) < 3) {
            return [];
        }

        $this->ensureApiKeyConfigured();
        $limit = max(1, min(20, $limit));

        $cacheKey = 'rajaongkir:destination:' . md5(mb_strtolower($keyword) . '|' . $limit);

        return Cache::remember($cacheKey, now()->addMinutes(self::DESTINATION_CACHE_MINUTES), function () use ($keyword, $limit) {
            $payload = $this->request(
                method: 'GET',
                endpoint: 'destination/domestic-destination',
                options: [
                    'search' => $keyword,
                    'limit' => $limit,
                    'offset' => 0,
                ],
            );

            return $this->normalizeDestinationResults((array) ($payload['data'] ?? []));
        });
    }

    public function calculateDomesticCost(?string $originId, ?string $destinationId, int $weight, string $courier): array
    {
        $this->ensureApiKeyConfigured();

        $originId = trim((string) $originId);
        $destinationId = trim((string) $destinationId);
        $courier = trim(mb_strtolower($courier));
        $weight = $this->normalizeWeight($weight);

        if ($originId === '') {
            throw new RuntimeException('Asal pengiriman belum dikonfigurasi.');
        }

        if ($destinationId === '') {
            throw new RuntimeException('Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.');
        }

        if ($courier === '') {
            throw new RuntimeException('Kurir belum tersedia untuk alamat ini.');
        }

        $cacheKey = 'rajaongkir:cost:' . md5(implode('|', [$originId, $destinationId, $weight, $courier]));

        return Cache::remember($cacheKey, now()->addMinutes(self::COST_CACHE_MINUTES), function () use ($originId, $destinationId, $weight, $courier) {
            $payload = $this->request(
                method: 'POST',
                endpoint: 'calculate/domestic-cost',
                options: [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => 'lowest',
                ],
                asForm: true,
            );

            return $this->normalizeCostResults(
                items: (array) ($payload['data'] ?? []),
                destinationId: $destinationId,
                originId: $originId,
                weight: $weight,
            );
        });
    }

    public function getCheapestCost(string $destinationId, int $weight, ?string $courier = null): array
    {
        $originId = $this->configuredOriginId();
        if ($originId === '') {
            throw new RuntimeException('Asal pengiriman belum dikonfigurasi.');
        }

        $destinationId = trim($destinationId);
        if ($destinationId === '') {
            throw new RuntimeException('Lokasi pengiriman belum dapat digunakan untuk menghitung ongkir. Silakan perbarui alamat pengiriman Anda.');
        }

        $weight = $this->normalizeWeight($weight);
        $couriers = $courier
            ? [trim(mb_strtolower($courier))]
            : $this->configuredCouriers();

        $results = [];
        foreach ($couriers as $courierCode) {
            try {
                $results = array_merge(
                    $results,
                    $this->calculateDomesticCost($originId, $destinationId, $weight, $courierCode)
                );
            } catch (RuntimeException $exception) {
                if ($this->isRecoverableCourierFailure($exception->getMessage()) && count($couriers) > 1) {
                    continue;
                }

                throw $exception;
            }
        }

        if (empty($results)) {
            throw new RuntimeException('Kurir belum tersedia untuk alamat ini.');
        }

        usort($results, fn (array $left, array $right) => ($left['cost'] <=> $right['cost']));

        return $results[0];
    }

    public function configuredOriginId(): string
    {
        return trim((string) config('services.rajaongkir.origin_id', ''));
    }

    public function configuredCouriers(): array
    {
        return collect(explode(',', (string) config('services.rajaongkir.couriers', '')))
            ->map(fn (string $courier) => trim(mb_strtolower($courier)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function defaultWeight(): int
    {
        return max(1000, (int) config('services.rajaongkir.default_weight', 1000));
    }

    private function normalizeDestinationResults(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                return [
                    'id' => (string) ($item['id'] ?? ''),
                    'label' => (string) ($item['label'] ?? ''),
                    'province_name' => (string) ($item['province_name'] ?? ''),
                    'city_name' => (string) ($item['city_name'] ?? ''),
                    'district_name' => (string) ($item['district_name'] ?? ''),
                    'subdistrict_name' => (string) ($item['subdistrict_name'] ?? ''),
                    'zip_code' => (string) ($item['zip_code'] ?? ''),
                ];
            })
            ->filter(fn (array $item) => $item['id'] !== '' && $item['label'] !== '')
            ->values()
            ->all();
    }

    private function normalizeCostResults(array $items, string $destinationId, string $originId, int $weight): array
    {
        $normalized = collect($items)
            ->map(function (array $item) use ($destinationId, $originId, $weight) {
                $cost = max(0, (int) ($item['cost'] ?? 0));

                return [
                    'courier_code' => (string) ($item['code'] ?? ''),
                    'courier_name' => (string) ($item['name'] ?? ''),
                    'service' => (string) ($item['service'] ?? ''),
                    'service_description' => (string) ($item['description'] ?? ''),
                    'etd' => (string) ($item['etd'] ?? ''),
                    'cost' => $cost,
                    'formatted_cost' => 'Rp ' . number_format($cost, 0, ',', '.'),
                    'weight' => $weight,
                    'destination_id' => $destinationId,
                    'origin_id' => $originId,
                ];
            })
            ->filter(fn (array $item) => $item['courier_code'] !== '' && $item['service'] !== '')
            ->sortBy('cost')
            ->values()
            ->all();

        if (empty($normalized)) {
            throw new RuntimeException('Kurir belum tersedia untuk alamat ini.');
        }

        return $normalized;
    }

    private function request(string $method, string $endpoint, array $options = [], bool $asForm = false): array
    {
        $baseUrl = rtrim((string) config('services.rajaongkir.base_url', ''), '/');
        if ($baseUrl === '') {
            throw new RuntimeException('Ongkir belum dapat dihitung. Periksa alamat tujuan atau coba lagi.');
        }

        $client = Http::acceptJson()
            ->withHeaders([
                'key' => (string) config('services.rajaongkir.key'),
            ])
            ->baseUrl($baseUrl)
            ->timeout(self::REQUEST_TIMEOUT_SECONDS);

        if ($asForm) {
            $client = $client->asForm();
        }

        $response = strtoupper($method) === 'POST'
            ? $client->post($endpoint, $options)
            : $client->get($endpoint, $options);

        return $this->parseResponse($response);
    }

    private function parseResponse(Response $response): array
    {
        $payload = $response->json();
        if ($response->successful() && is_array($payload)) {
            return $payload;
        }

        $message = $this->resolveFriendlyErrorMessage($response, is_array($payload) ? $payload : []);
        throw new RuntimeException($message);
    }

    private function resolveFriendlyErrorMessage(Response $response, array $payload): string
    {
        $message = trim((string) data_get($payload, 'meta.message', data_get($payload, 'message', '')));

        if ($response->status() === 429 || $this->containsQuotaHint($message)) {
            return 'Kuota cek ongkir habis, coba lagi nanti.';
        }

        if ($this->containsCourierUnavailableHint($message)) {
            return 'Kurir belum tersedia untuk alamat ini.';
        }

        return 'Ongkir belum dapat dihitung. Periksa alamat tujuan atau coba lagi.';
    }

    private function ensureApiKeyConfigured(): void
    {
        if (trim((string) config('services.rajaongkir.key', '')) === '') {
            throw new RuntimeException('Layanan ongkir belum dikonfigurasi.');
        }
    }

    private function normalizeWeight(int $weight): int
    {
        return max(1000, $weight > 0 ? $weight : $this->defaultWeight());
    }

    private function containsQuotaHint(string $message): bool
    {
        $haystack = mb_strtolower($message);

        return str_contains($haystack, 'quota')
            || str_contains($haystack, 'limit')
            || str_contains($haystack, 'too many');
    }

    private function containsCourierUnavailableHint(string $message): bool
    {
        $haystack = mb_strtolower($message);

        return str_contains($haystack, 'courier')
            || str_contains($haystack, 'service not available')
            || str_contains($haystack, 'no shipping cost');
    }

    private function isRecoverableCourierFailure(string $message): bool
    {
        return in_array($message, [
            'Kurir belum tersedia untuk alamat ini.',
            'Ongkir belum dapat dihitung. Periksa alamat tujuan atau coba lagi.',
        ], true);
    }
}
