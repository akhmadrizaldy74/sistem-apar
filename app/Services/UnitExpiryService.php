<?php

namespace App\Services;

use App\Models\Service;
use App\Models\UnitApar;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class UnitExpiryService
{
    public function calculateExpiry(string|CarbonInterface $baseDate, ?string $ukuran, ?string $bahan = null): CarbonInterface
    {
        $resolvedDate = $baseDate instanceof CarbonInterface
            ? $baseDate->copy()->startOfDay()
            : Carbon::parse($baseDate)->startOfDay();

        return UnitApar::calculateExpiry(
            $resolvedDate->toDateString(),
            $ukuran,
            $bahan,
        );
    }

    public function resolveBaseDate(UnitApar $unitApar): ?CarbonInterface
    {
        $baseProductionDate = $unitApar->tgl_produksi?->copy()->startOfDay();
        $latestConfirmedRefillDate = $this->latestConfirmedRefillDate($unitApar);

        return $latestConfirmedRefillDate
            ?: $baseProductionDate
            ?: $unitApar->tgl_beli?->copy()->startOfDay();
    }

    public function expectedExpiry(UnitApar $unitApar): ?CarbonInterface
    {
        $baseDate = $this->resolveBaseDate($unitApar);
        if (! $baseDate) {
            return null;
        }

        return $this->calculateExpiry(
            $baseDate,
            $this->resolveUkuran($unitApar),
            $this->resolveBahan($unitApar),
        );
    }

    public function syncUnit(UnitApar $unitApar): bool
    {
        $expectedExpiry = $this->expectedExpiry($unitApar);
        if (! $expectedExpiry) {
            return false;
        }

        $expectedDate = $expectedExpiry->toDateString();
        if (optional($unitApar->tgl_expired)->toDateString() === $expectedDate) {
            return false;
        }

        $unitApar->forceFill([
            'tgl_expired' => $expectedDate,
        ])->save();

        return true;
    }

    public function resolveUkuran(UnitApar $unitApar): ?string
    {
        $ukuran = trim((string) ($unitApar->ukuran ?: $unitApar->produk?->kapasitas ?: ''));

        return $ukuran !== '' ? $ukuran : null;
    }

    public function resolveBahan(UnitApar $unitApar): ?string
    {
        $bahan = trim((string) ($unitApar->bahan ?: $unitApar->produk?->jenisApar?->nama ?: ''));

        return $bahan !== '' ? $bahan : null;
    }

    private function latestConfirmedRefillDate(UnitApar $unitApar): ?CarbonInterface
    {
        if ($unitApar->relationLoaded('services')) {
            $serviceDate = $unitApar->services
                ->filter(function (Service $service) {
                    return (string) $service->status_konfirmasi === 'confirmed'
                        && ! is_null($service->tgl_service)
                        && mb_strtolower(trim((string) $service->jenis_service)) === 'refill apar';
                })
                ->sortByDesc(fn (Service $service) => $service->tgl_service?->toDateString())
                ->first()?->tgl_service;

            return $serviceDate?->copy()->startOfDay();
        }

        $serviceDate = $unitApar->services()
            ->where('status_konfirmasi', 'confirmed')
            ->whereNotNull('tgl_service')
            ->whereRaw('LOWER(COALESCE(jenis_service, "")) = ?', ['refill apar'])
            ->max('tgl_service');

        return $serviceDate ? Carbon::parse($serviceDate)->startOfDay() : null;
    }
}
