<?php

namespace App\Services;

use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Support\RegisteredRefillUnitSupport;
use App\Support\ServiceUnitDisplay;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinalTransactionStockService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PaidOrderStockService $paidOrderStockService,
        private readonly UnitExpiryService $unitExpiryService,
    )
    {
    }

    public function apply(Pesanan $pesanan): void
    {
        $pesanan->refresh()->loadMissing([
            'pelanggan',
            'details.produk.jenisApar',
            'service.unitApar.produk.jenisApar',
            'servicePaket.peralatans',
            'serviceJenisRefill',
        ]);

        if ((string) $pesanan->status !== Pesanan::STATUS_SELESAI_FINAL) {
            return;
        }

        DB::transaction(function () use ($pesanan) {
            if ($pesanan->isPaymentConfirmed() && !$pesanan->stok_dikurangi) {
                $this->paidOrderStockService->apply($pesanan);
                $pesanan->refresh()->loadMissing([
                    'pelanggan',
                    'details.produk.jenisApar',
                    'service.unitApar.produk.jenisApar',
                    'servicePaket.peralatans',
                    'serviceJenisRefill',
                    'unitApars.produk',
                ]);
            }

            if ($pesanan->isProductOrder()) {
                $this->unhideProductUnits($pesanan);
                return;
            }

            if ($pesanan->isServiceOrder() || $pesanan->isRefillOrder()) {
                $this->ensureCompletedServiceUnits($pesanan);
            }

            if ($pesanan->isRefillOrder()) {
                $this->applyRefillStock($pesanan);
                return;
            }

            if ($pesanan->isServiceOrder()) {
                $this->applyServicePeralatanStock($pesanan);
            }
        });
    }

    private function applyRefillStock(Pesanan $pesanan): void
    {
        $units = $this->ensureCompletedServiceUnits($pesanan);
        $requirements = $this->resolveRefillStockRequirements($pesanan);

        if ($requirements->isEmpty()) {
            return;
        }

        if ($requirements->count() === 1 && $pesanan->service_jenis_refill_id) {
            $this->ensureRefillLog($pesanan, $units);
        } else {
            $this->ensureRefillServiceRecord($pesanan, $units);
            $this->refreshResolvedUnitsExpiry($units, $pesanan, $pesanan->resolvedOperationalDate());
        }

        foreach ($requirements as $requirement) {
            if ($pesanan->stok_dikurangi) {
                break;
            }

            /** @var JenisRefill $jenisRefill */
            $jenisRefill = $requirement['jenis_refill'];
            $qty = (float) ($requirement['qty'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $this->inventoryService->decreaseRefillStock(
                jenisRefill: $jenisRefill,
                qty: $qty,
                sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Refill APAR - ' . $this->customerName($pesanan),
                tanggal: now(),
            );
        }

        if (!$pesanan->stok_dikurangi) {
            $pesanan->forceFill(['stok_dikurangi' => true])->save();
        }
    }

    private function resolveRefillStockRequirements(Pesanan $pesanan): Collection
    {
        if ($pesanan->service_jenis_refill_id && $pesanan->serviceJenisRefill && (float) ($pesanan->service_total_kg ?? 0) > 0) {
            return collect([[
                'jenis_refill' => $pesanan->serviceJenisRefill,
                'qty' => (float) $pesanan->service_total_kg,
            ]]);
        }

        $details = collect(RegisteredRefillUnitSupport::parseRefillUnitDetails((string) ($pesanan->service_keluhan ?? '')));
        if ($details->isEmpty()) {
            return collect();
        }

        $jenisRefills = JenisRefill::query()->get();
        $requirements = $details
            ->map(function (array $detail) use ($jenisRefills) {
                $jenisRefill = RegisteredRefillUnitSupport::matchJenisRefillByLabel($jenisRefills, (string) ($detail['refill_label'] ?? ''));
                $qty = (float) ($detail['usage_kg'] ?? 0);

                if (! $jenisRefill || $qty <= 0) {
                    return null;
                }

                return [
                    'jenis_refill' => $jenisRefill,
                    'qty' => $qty,
                ];
            })
            ->filter()
            ->groupBy(fn (array $detail) => (int) $detail['jenis_refill']->id)
            ->map(function (Collection $group) {
                /** @var array{jenis_refill: JenisRefill, qty: float} $first */
                $first = $group->first();

                return [
                    'jenis_refill' => $first['jenis_refill'],
                    'qty' => (float) round($group->sum('qty'), 2),
                ];
            })
            ->values();

        if ($requirements->isEmpty()) {
            throw new \RuntimeException('Detail refill per unit tidak bisa dibaca untuk finalisasi stok.');
        }

        return $requirements;
    }

    private function ensureRefillLog(Pesanan $pesanan, ?Collection $resolvedUnits = null): void
    {
        if (!$pesanan->service_jenis_refill_id) {
            return;
        }

        $effectiveWorkDate = $pesanan->resolvedOperationalDate();
        $resolvedUnits ??= collect();
        $unitAparId = $pesanan->service?->unit_apar_id
            ?: $resolvedUnits->first()?->id
            ?: $pesanan->unitApars()->orderBy('id')->value('id');

        $service = Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            [
                'unit_apar_id' => $unitAparId,
                'jenis_service' => 'Refill APAR',
                'tgl_service' => $effectiveWorkDate,
                'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => now(),
            ],
        );

        if ($unitAparId) {
            Refill::updateOrCreate(
                ['service_id' => $service->id],
                [
                    'unit_apar_id' => $unitAparId,
                    'jenis_refill_id' => $pesanan->service_jenis_refill_id,
                    'tgl_refill' => $effectiveWorkDate,
                    'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                ],
            );
        } else {
            Refill::query()->where('service_id', $service->id)->delete();
        }

        $unitsToRefresh = $resolvedUnits->isNotEmpty()
            ? $resolvedUnits
            : ($unitAparId
                ? UnitApar::query()->whereKey($unitAparId)->get()
                : collect());

        $this->refreshResolvedUnitsExpiry($unitsToRefresh, $pesanan, $effectiveWorkDate);
    }

    private function ensureRefillServiceRecord(Pesanan $pesanan, ?Collection $resolvedUnits = null): Service
    {
        $effectiveWorkDate = $pesanan->resolvedOperationalDate();
        $resolvedUnits ??= collect();
        $unitAparId = $pesanan->service?->unit_apar_id ?: $resolvedUnits->first()?->id;

        return Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            [
                'unit_apar_id' => $unitAparId,
                'jenis_service' => 'Refill APAR',
                'tgl_service' => $effectiveWorkDate,
                'biaya' => (float) ($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? $pesanan->total ?? 0),
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => now(),
            ],
        );
    }

    private function refreshResolvedUnitsExpiry(Collection $unitsToRefresh, Pesanan $pesanan, string $effectiveWorkDate): void
    {
        foreach ($unitsToRefresh as $unitApar) {
            $unitApar->update([
                'tgl_expired' => $this->unitExpiryService->calculateExpiry(
                    $effectiveWorkDate,
                    $unitApar->ukuran ?: $pesanan->service_ukuran_apar ?: $unitApar->produk?->kapasitas,
                    $unitApar->bahan ?: $unitApar->produk?->jenisApar?->nama ?: $this->manualUnitMedia($pesanan),
                ),
            ]);
        }
    }

    private function applyServicePeralatanStock(Pesanan $pesanan): void
    {
        $generatedUnits = $this->ensureCompletedServiceUnits($pesanan);
        $unitAparId = $pesanan->service?->unit_apar_id ?: $generatedUnits->first()?->id;
        $effectiveWorkDate = $pesanan->resolvedOperationalDate();

        $service = Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            $pesanan->serviceLogPayload([
                'unit_apar_id' => $unitAparId,
                'tgl_service' => $effectiveWorkDate,
                'status_konfirmasi' => $pesanan->service?->status_konfirmasi ?: 'pending',
                'actual_peralatan_json' => $pesanan->service?->actual_peralatan_json,
                'catatan_teknisi' => $pesanan->service?->catatan_teknisi,
                'laporan_foto' => $pesanan->service?->laporan_foto,
                'tgl_selesai_admin' => $pesanan->service?->tgl_selesai_admin,
                'stok_kurang_history_json' => $pesanan->service?->stok_kurang_history_json,
            ]),
        );

        if (!empty($service->stok_kurang_history)) {
            $service->update([
                'status_konfirmasi' => 'confirmed',
                'tgl_selesai_admin' => $service->tgl_selesai_admin ?: now(),
            ]);
            $this->refreshResolvedUnitsExpiry($generatedUnits, $pesanan, $effectiveWorkDate);
            return;
        }

        $items = collect($service->effective_peralatan ?: $pesanan->estimatedServicePeralatan())
            ->map(fn (array $item) => [
                'peralatan_id' => (int) ($item['peralatan_id'] ?? $item['id'] ?? 0),
                'nama' => (string) ($item['nama'] ?? ''),
                'jumlah' => (int) ($item['jumlah'] ?? 0),
            ])
            ->filter(fn (array $item) => $item['peralatan_id'] > 0 && $item['jumlah'] > 0)
            ->values();

        $history = [];

        foreach ($items as $item) {
            $peralatan = Peralatan::find($item['peralatan_id']);
            if (!$peralatan) {
                continue;
            }

            $stokSebelum = (float) $peralatan->stok;
            $jumlah = (float) $item['jumlah'];

            $this->inventoryService->decreasePeralatanStock(
                peralatan: $peralatan,
                qty: $jumlah,
                sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Service APAR - ' . $this->customerName($pesanan),
                tanggal: now(),
            );

            $history[] = [
                'peralatan_id' => $peralatan->id,
                'nama' => $peralatan->nama,
                'jumlah' => (int) $jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => (float) $peralatan->fresh()->stok,
            ];
        }

        $service->update([
            'actual_peralatan_json' => $service->actual_peralatan_json ?: json_encode($items->all()),
            'status_konfirmasi' => 'confirmed',
            'tgl_selesai_admin' => now(),
            'stok_kurang_history_json' => json_encode($history),
        ]);

        $this->refreshResolvedUnitsExpiry($generatedUnits, $pesanan, $effectiveWorkDate);
    }

    private function ensureCompletedServiceUnits(Pesanan $pesanan): Collection
    {
        if (!$pesanan->isServiceOrder() && !$pesanan->isRefillOrder()) {
            return collect();
        }

        if (ServiceUnitDisplay::forPesanan($pesanan)['is_registered'] ?? false) {
            $resolvedUnits = RegisteredRefillUnitSupport::resolveRegisteredUnitsForOrder($pesanan);

            if ($resolvedUnits->isNotEmpty()) {
                if ($pesanan->service && !$pesanan->service->unit_apar_id) {
                    $pesanan->service->forceFill([
                        'unit_apar_id' => $resolvedUnits->first()->id,
                    ])->save();
                }

                return $resolvedUnits;
            }

            if (!$pesanan->service?->unit_apar_id) {
                return collect();
            }

            return UnitApar::query()->whereKey($pesanan->service->unit_apar_id)->get();
        }

        $existingUnits = UnitApar::query()
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('id')
            ->get();

        if ($existingUnits->isNotEmpty()) {
            return $existingUnits->values();
        }

        if ($pesanan->service?->unit_apar_id) {
            return UnitApar::query()
                ->whereKey($pesanan->service->unit_apar_id)
                ->get();
        }

        return collect();
    }

    private function ensureProductUnitsFromBatchAllocations(Pesanan $pesanan, array $batchAllocations): void
    {
        if (empty($batchAllocations)) {
            return;
        }

        $pesanan->loadMissing(['pelanggan', 'details.produk.jenisApar', 'unitApars']);

        foreach ($pesanan->details as $detail) {
            if (! $detail->produk) {
                continue;
            }

            $remainingUnits = max(
                0,
                (int) $detail->jumlah - $pesanan->unitApars->where('produk_id', $detail->produk_id)->count()
            );

            if ($remainingUnits <= 0) {
                continue;
            }

            foreach ($batchAllocations[$detail->produk_id] ?? [] as $allocation) {
                $qtyFromBatch = min((int) ($allocation['qty'] ?? 0), $remainingUnits);

                for ($i = 0; $i < $qtyFromBatch; $i++) {
                    $this->createProductUnitFromAllocation($pesanan, $detail->produk, $allocation);
                    $remainingUnits--;
                }

                if ($remainingUnits <= 0) {
                    break;
                }
            }

            $pesanan->load('unitApars');
        }
    }

    private function createProductUnitFromAllocation(Pesanan $pesanan, Produk $produk, array $allocation): UnitApar
    {
        $serialDate = optional($pesanan->tanggal)->toDateString() ?: now()->toDateString();
        $productionDate = (string) ($allocation['tgl_produksi'] ?? $serialDate);
        $expiredDate = $this->unitExpiryService
            ->calculateExpiry($serialDate, $produk->kapasitas ?? '-', $produk->jenisApar?->nama ?? '-')
            ->toDateString();

        return UnitApar::create([
            'pelanggan_id' => $pesanan->pelanggan_id,
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'no_seri' => UnitApar::generateSerialNumber($pesanan->pelanggan, $serialDate),
            'tgl_beli' => $serialDate,
            'tgl_produksi' => $productionDate,
            'ukuran' => $produk->kapasitas ?? '-',
            'bahan' => $produk->jenisApar?->nama ?? '-',
            'kondisi_awal' => 'layak',
            'catatan_unit' => 'Unit dibuat otomatis dari pesanan produk yang selesai final.',
            'tgl_expired' => $expiredDate,
        ]);
    }

    private function manualUnitMedia(Pesanan $pesanan): string
    {
        $media = trim((string) (
            $pesanan->serviceJenisRefill?->nama_label
            ?: $pesanan->service_jenis_apar
            ?: 'APAR'
        ));

        return match (strtolower($media)) {
            'powder' => 'Powder',
            'co2' => 'CO2',
            'foam' => 'Foam',
            default => $media !== '' ? $media : 'APAR',
        };
    }

    private function customerName(Pesanan $pesanan): string
    {
        return (string) ($pesanan->pelanggan?->nama ?: 'Pelanggan tidak diketahui');
    }

    private function unhideProductUnits(Pesanan $pesanan): void
    {
        $pesanan->loadMissing(['unitApars']);

        foreach ($pesanan->unitApars as $unitApar) {
            if ($unitApar->isHiddenFromListings()) {
                $unitApar->forceFill([
                    'hidden_at' => null,
                ])->save();
            }
        }
    }
}
