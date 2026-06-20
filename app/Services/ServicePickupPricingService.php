<?php

namespace App\Services;

use RuntimeException;

class ServicePickupPricingService
{
    public function quote(?float $customerLat, ?float $customerLng): array
    {
        if (is_null($customerLat) || is_null($customerLng)) {
            throw new RuntimeException('Lokasi penjemputan belum lengkap. Silakan perbarui alamat terlebih dahulu.');
        }

        $storeLat = $this->storeLatitude();
        $storeLng = $this->storeLongitude();

        if (is_null($storeLat) || is_null($storeLng)) {
            throw new RuntimeException('Koordinat toko belum lengkap. Hubungi admin untuk melanjutkan.');
        }

        $oneWayDistance = $this->haversineDistanceKm(
            fromLat: $storeLat,
            fromLng: $storeLng,
            toLat: $customerLat,
            toLng: $customerLng,
        );
        $roundTripDistance = $oneWayDistance * 2;
        $ratePerKm = $this->ratePerKm();
        $minimumCost = $this->minimumCost();

        $calculatedCost = $roundTripDistance * $ratePerKm;
        $totalCost = $oneWayDistance > 0
            ? max($minimumCost, $calculatedCost)
            : 0;

        return [
            'store_lat' => $storeLat,
            'store_lng' => $storeLng,
            'distance_km' => round($oneWayDistance, 2),
            'round_trip_distance_km' => round($roundTripDistance, 2),
            'rate_per_km' => $ratePerKm,
            'minimum_cost' => $minimumCost,
            'cost' => round($totalCost, 0),
        ];
    }

    public function storeLatitude(): ?float
    {
        return $this->sanitizeCoordinate(config('services.apar_service_pickup.store_lat'));
    }

    public function storeLongitude(): ?float
    {
        return $this->sanitizeCoordinate(config('services.apar_service_pickup.store_lng'));
    }

    public function ratePerKm(): float
    {
        return max(0, (float) config('services.apar_service_pickup.rate_per_km', 3500));
    }

    public function minimumCost(): float
    {
        return max(0, (float) config('services.apar_service_pickup.min_cost', 15000));
    }

    private function sanitizeCoordinate(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || ! is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function haversineDistanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadiusKm = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat))
            * cos(deg2rad($toLat))
            * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }
}
