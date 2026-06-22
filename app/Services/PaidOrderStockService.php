<?php

namespace App\Services;

use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Support\RegisteredRefillUnitSupport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaidOrderStockService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly UnitExpiryService $unitExpiryService,
    )
    {
    }

    public function apply(Pesanan $pesanan): void
    {
        $pesanan->refresh()->loadMissing([
            'pelanggan',
            'details.produk.jenisApar',
            'service',
            'servicePaket.peralatans',
            'serviceJenisRefill',
            'unitApars.produk',
        ]);

        if (!$pesanan->isPaymentConfirmed() || $pesanan->stok_dikurangi) {
            return;
        }

        DB::transaction(function () use ($pesanan) {
            if ($pesanan->isProductOrder()) {
                $this->applyProductStock($pesanan);
            } elseif ($pesanan->isRefillOrder()) {
                $this->applyRefillStock($pesanan);
            } elseif ($pesanan->isServiceOrder()) {
                $this->applyServicePeralatanStock($pesanan);
            }

            $pesanan->forceFill([
                'stok_dikurangi' => true,
            ])->save();
        });
    }

    public function rollback(Pesanan $pesanan): void
    {
        $pesanan->refresh()->loadMissing([
            'details.produk.jenisApar',
            'service',
            'servicePaket.peralatans',
            'serviceJenisRefill',
            'unitApars.produk',
        ]);

        if (!$pesanan->stok_dikurangi) {
            return;
        }

        DB::transaction(function () use ($pesanan) {
            if ($pesanan->isProductOrder()) {
                $this->rollbackProductStock($pesanan);
            } elseif ($pesanan->isRefillOrder()) {
                $this->rollbackRefillStock($pesanan);
            } elseif ($pesanan->isServiceOrder()) {
                $this->rollbackServicePeralatanStock($pesanan);
            }

            $pesanan->forceFill([
                'stok_dikurangi' => false,
            ])->save();
        });
    }

    private function applyProductStock(Pesanan $pesanan): void
    {
        foreach ($pesanan->details as $detail) {
            $produk = $detail->produk;
            if (!$produk) {
                continue;
            }

            $jumlahDibutuhkan = max(0, (int) $detail->jumlah);
            if ($jumlahDibutuhkan <= 0) {
                continue;
            }

            $stokSebelum = (float) $produk->stok_tersedia;
            $allocations = $this->allocateProductBatches($pesanan, $produk, $jumlahDibutuhkan);
            $produk->refresh();
            $produk->syncStoredStockFromBatches();
            $produk->refresh()->load('stokBatches');

            $this->inventoryService->logProductMovement(
                produk: $produk,
                qty: (float) $jumlahDibutuhkan,
                movementType: StockMovement::MOVE_OUT,
                sourceType: StockMovement::SOURCE_PENJUALAN_PRODUK,
                stokSebelum: $stokSebelum,
                stokSesudah: (float) $produk->stok_tersedia,
                reference: $pesanan,
                keterangan: 'Pesanan Produk - ' . $this->customerName($pesanan),
                tanggal: now(),
            );

            $this->ensureProductUnitsForDetail($pesanan, $produk, $allocations);
        }
    }

    /**
     * @return array<int, array{qty: int, tgl_produksi: string, tgl_expired: string}>
     */
    private function allocateProductBatches(Pesanan $pesanan, Produk $produk, int $jumlahDibutuhkan): array
    {
        $allocations = [];
        $warningLimit = now()->startOfDay()->addDays(RegisteredRefillUnitSupport::REFILL_WARNING_DAYS)->toDateString();
        $batches = StokBatch::query()
            ->where('produk_id', $produk->id)
            ->where('sisa_qty', '>', 0)
            ->whereDate('tgl_expired', '>', $warningLimit)
            ->orderBy('tgl_expired')
            ->orderBy('id')
            ->get();

        if ((int) $batches->sum('sisa_qty') < $jumlahDibutuhkan) {
            throw new \RuntimeException("Stok siap jual produk '{$produk->nama}' tidak mencukupi untuk memenuhi pesanan.");
        }

        foreach ($batches as $batch) {
            if ($jumlahDibutuhkan <= 0) {
                break;
            }

            $qtyDariBatch = min((int) $batch->sisa_qty, $jumlahDibutuhkan);
            if ($qtyDariBatch <= 0) {
                continue;
            }

            $allocations[] = [
                'qty' => $qtyDariBatch,
                'tgl_produksi' => $batch->tgl_produksi?->toDateString()
                    ?: optional($pesanan->tanggal)->toDateString()
                    ?: now()->toDateString(),
                'tgl_expired' => $batch->tgl_expired?->toDateString()
                    ?: UnitApar::calculateExpiry(
                        optional($pesanan->tanggal)->toDateString() ?: now()->toDateString(),
                        $produk->kapasitas ?? '-',
                        $produk->jenisApar?->nama ?? '-',
                    )->toDateString(),
            ];

            if ((int) $batch->sisa_qty === $qtyDariBatch) {
                $batch->update(['sisa_qty' => 0]);
            } else {
                $batch->decrement('sisa_qty', $qtyDariBatch);
            }

            $jumlahDibutuhkan -= $qtyDariBatch;
        }

        return $allocations;
    }

    /**
     * @param  array<int, array{qty: int, tgl_produksi: string, tgl_expired: string}>  $allocations
     */
    private function ensureProductUnitsForDetail(Pesanan $pesanan, Produk $produk, array $allocations): void
    {
        $existingCount = $pesanan->unitApars
            ->where('produk_id', $produk->id)
            ->count();

        $requiredCount = array_sum(array_map(fn (array $allocation) => (int) ($allocation['qty'] ?? 0), $allocations));
        $missingCount = max(0, $requiredCount - $existingCount);
        if ($missingCount <= 0) {
            return;
        }

        $shouldHide = (string) $pesanan->status !== Pesanan::STATUS_SELESAI_FINAL && UnitApar::supportsDatabaseColumn('hidden_at');

        foreach ($allocations as $allocation) {
            $qty = max(0, (int) ($allocation['qty'] ?? 0));
            $unitExpiryDate = $allocation['tgl_expired'];

            for ($index = 0; $index < $qty && $missingCount > 0; $index++) {
                UnitApar::create([
                    'pelanggan_id' => $pesanan->pelanggan_id,
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $produk->id,
                    'no_seri' => UnitApar::generateSerialNumber($pesanan->pelanggan, $pesanan->tanggal),
                    'tgl_beli' => $pesanan->tanggal,
                    'tgl_produksi' => $allocation['tgl_produksi'],
                    'ukuran' => $produk->kapasitas ?? '-',
                    'bahan' => $produk->jenisApar?->nama ?? '-',
                    'tgl_expired' => $unitExpiryDate,
                    'hidden_at' => $shouldHide ? now() : null,
                ]);

                $missingCount--;
            }

            if ($missingCount <= 0) {
                break;
            }
        }
    }

    private function rollbackProductStock(Pesanan $pesanan): void
    {
        $affectedProductIds = collect();
        $units = $pesanan->unitApars()
            ->with('produk')
            ->orderBy('id')
            ->get();

        $groupedUnits = $units
            ->groupBy(function (UnitApar $unitApar) {
                return implode('|', [
                    (int) $unitApar->produk_id,
                    optional($unitApar->tgl_produksi)->toDateString() ?: '',
                    $this->resolvedBatchExpiryDate($unitApar),
                ]);
            });

        foreach ($groupedUnits as $unitGroup) {
            /** @var UnitApar|null $firstUnit */
            $firstUnit = $unitGroup->first();
            if (!$firstUnit || !$firstUnit->produk_id) {
                continue;
            }

            $qty = $unitGroup->count();
            $batch = StokBatch::query()
                ->where('produk_id', $firstUnit->produk_id)
                ->whereDate('tgl_produksi', optional($firstUnit->tgl_produksi)->toDateString() ?: now()->toDateString())
                ->whereDate('tgl_expired', $this->resolvedBatchExpiryDate($firstUnit))
                ->first();

            if ($batch) {
                $batch->increment('sisa_qty', $qty);
            } else {
                StokBatch::create([
                    'produk_id' => $firstUnit->produk_id,
                    'jumlah_masuk' => $qty,
                    'sisa_qty' => $qty,
                    'tgl_produksi' => optional($firstUnit->tgl_produksi)->toDateString() ?: now()->toDateString(),
                    'tgl_expired' => $this->resolvedBatchExpiryDate($firstUnit),
                    'keterangan' => 'Pemulihan stok dari pembatalan pesanan aktif #' . $pesanan->id,
                ]);
            }

            $affectedProductIds->push((int) $firstUnit->produk_id);
        }

        if ($units->isEmpty()) {
            foreach ($pesanan->details as $detail) {
                $produk = $detail->produk;
                if (!$produk) {
                    continue;
                }

                $qty = max(0, (int) $detail->jumlah);
                if ($qty <= 0) {
                    continue;
                }

                StokBatch::create([
                    'produk_id' => $produk->id,
                    'jumlah_masuk' => $qty,
                    'sisa_qty' => $qty,
                    'tgl_produksi' => optional($pesanan->tanggal)->toDateString() ?: now()->toDateString(),
                    'tgl_expired' => UnitApar::calculateExpiry(
                        optional($pesanan->tanggal)->toDateString() ?: now()->toDateString(),
                        $produk->kapasitas ?? '-',
                        $produk->jenisApar?->nama ?? '-',
                    )->toDateString(),
                    'keterangan' => 'Pemulihan stok dari pembatalan pesanan aktif #' . $pesanan->id,
                ]);

                $affectedProductIds->push((int) $produk->id);
            }
        }

        $affectedProductIds
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->each(function (int $productId) {
                $produk = Produk::find($productId);

                if ($produk) {
                    $produk->syncStoredStockFromBatches();
                }
            });
    }

    private function applyRefillStock(Pesanan $pesanan): void
    {
        $requirements = $this->resolveRefillRequirements($pesanan);
        if ($requirements->isEmpty()) {
            throw new \RuntimeException('Detail kebutuhan stok refill tidak dapat dibaca untuk pembayaran ini.');
        }

        foreach ($requirements as $requirement) {
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
    }

    private function rollbackRefillStock(Pesanan $pesanan): void
    {
        foreach ($this->resolveRefillRequirements($pesanan) as $requirement) {
            /** @var JenisRefill $jenisRefill */
            $jenisRefill = $requirement['jenis_refill'];
            $qty = (float) ($requirement['qty'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $this->inventoryService->increaseRefillStock(
                jenisRefill: $jenisRefill,
                qty: $qty,
                sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Pemulihan refill - ' . $this->customerName($pesanan),
                tanggal: now(),
            );
        }
    }

    /**
     * @return Collection<int, array{jenis_refill: JenisRefill, qty: float}>
     */
    private function resolveRefillRequirements(Pesanan $pesanan): Collection
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

        return $details
            ->map(function (array $detail) use ($jenisRefills) {
                $jenisRefill = RegisteredRefillUnitSupport::matchJenisRefillByLabel($jenisRefills, (string) ($detail['refill_label'] ?? ''));
                $qty = (float) ($detail['usage_kg'] ?? 0);

                if (!$jenisRefill || $qty <= 0) {
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
    }

    private function applyServicePeralatanStock(Pesanan $pesanan): void
    {
        $service = $this->ensureServiceLog($pesanan);
        if (!$service) {
            return;
        }

        if (! empty($service->stok_kurang_history)) {
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

        if ($items->isEmpty()) {
            return;
        }

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

        $service->forceFill([
            'actual_peralatan_json' => $service->actual_peralatan_json ?: json_encode($items->all()),
            'stok_kurang_history_json' => json_encode($history),
        ])->save();
    }

    private function rollbackServicePeralatanStock(Pesanan $pesanan): void
    {
        /** @var Service|null $service */
        $service = $this->ensureServiceLog($pesanan);
        if (!$service) {
            throw new \RuntimeException('Data service belum siap diproses untuk pengurangan stok.');
        }

        $history = collect($service->stok_kurang_history)
            ->map(fn (array $item) => [
                'peralatan_id' => (int) ($item['peralatan_id'] ?? 0),
                'jumlah' => (float) ($item['jumlah'] ?? 0),
            ])
            ->filter(fn (array $item) => $item['peralatan_id'] > 0 && $item['jumlah'] > 0)
            ->values();

        if ($history->isEmpty()) {
            return;
        }

        foreach ($history as $item) {
            $peralatan = Peralatan::find($item['peralatan_id']);
            if (!$peralatan) {
                continue;
            }

            $this->inventoryService->increasePeralatanStock(
                peralatan: $peralatan,
                qty: (float) $item['jumlah'],
                sourceType: StockMovement::SOURCE_SERVICE_PELANGGAN,
                reference: $pesanan,
                keterangan: 'Pemulihan service - ' . $this->customerName($pesanan),
                tanggal: now(),
            );
        }

        $service->forceFill([
            'stok_kurang_history_json' => null,
        ])->save();
    }

    private function ensureServiceLog(Pesanan $pesanan): ?Service
    {
        if (! $pesanan->isServiceOrder()) {
            return null;
        }

        $pesanan->loadMissing(['servicePaket.peralatans', 'service']);
        $existingService = $pesanan->service;

        return Service::updateOrCreate(
            ['pesanan_id' => $pesanan->id],
            $pesanan->serviceLogPayload([
                'status_konfirmasi' => $existingService?->status_konfirmasi ?: 'pending',
                'actual_peralatan_json' => $existingService?->actual_peralatan_json,
                'catatan_teknisi' => $existingService?->catatan_teknisi,
                'laporan_foto' => $existingService?->laporan_foto,
                'tgl_selesai_admin' => $existingService?->tgl_selesai_admin,
                'stok_kurang_history_json' => $existingService?->stok_kurang_history_json,
            ]),
        );
    }

    private function customerName(Pesanan $pesanan): string
    {
        return (string) ($pesanan->pelanggan?->nama ?: 'Pelanggan tidak diketahui');
    }

    private function resolvedBatchExpiryDate(UnitApar $unitApar): string
    {
        return optional($unitApar->tgl_expired)->toDateString() ?: now()->toDateString();
    }
}
