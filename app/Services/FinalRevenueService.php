<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Refill;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class FinalRevenueService
{
    public function productOrdersQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Pesanan::query()
            ->where('tipe', 'produk');

        $this->applyFinalOrderStatusConstraint($query);
        $this->applyDateRange($query, 'tanggal', $from, $to);

        return $query->when($pelangganId, fn (Builder $builder, int $id) => $builder->where('pelanggan_id', $id));
    }

    public function serviceTransactionsQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Service::query()
            ->where(function (Builder $builder) {
                $builder->whereNull('jenis_service')
                    ->orWhereRaw("LOWER(COALESCE(jenis_service, '')) NOT LIKE ?", ['%refill%']);
            })
            ->whereHas('pesanan', function (Builder $builder) {
                $this->applyFinalOrderStatusConstraint($builder);
            });

        $this->applyDateRange($query, 'tgl_service', $from, $to);

        return $query->when($pelangganId, function (Builder $builder, int $id) {
            $builder->where(function (Builder $customerQuery) use ($id) {
                $customerQuery->whereHas('unitApar', fn (Builder $unitQuery) => $unitQuery->where('pelanggan_id', $id))
                    ->orWhereHas('pesanan', fn (Builder $orderQuery) => $orderQuery->where('pelanggan_id', $id));
            });
        });
    }

    public function refillTransactionsQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Refill::query()
            ->whereHas('service.pesanan', function (Builder $builder) {
                $this->applyFinalOrderStatusConstraint($builder);
            });

        $this->applyDateRange($query, 'tgl_refill', $from, $to);

        return $query->when($pelangganId, function (Builder $builder, int $id) {
            $builder->where(function (Builder $customerQuery) use ($id) {
                $customerQuery->whereHas('unitApar', fn (Builder $unitQuery) => $unitQuery->where('pelanggan_id', $id))
                    ->orWhereHas('service.pesanan', fn (Builder $orderQuery) => $orderQuery->where('pelanggan_id', $id));
            });
        });
    }

    public function breakdown(?string $from = null, ?string $to = null, ?int $pelangganId = null): array
    {
        $product = $this->sumColumn(
            $this->productOrdersQuery($from, $to, $pelangganId),
            'total'
        );

        $service = $this->sumColumn(
            $this->serviceTransactionsQuery($from, $to, $pelangganId),
            'biaya'
        );

        $refill = $this->sumColumn(
            $this->refillTransactionsQuery($from, $to, $pelangganId),
            'biaya'
        );

        return [
            'product' => $product,
            'service' => $service,
            'refill' => $refill,
            'total' => $product + $service + $refill,
        ];
    }

    private function applyDateRange(Builder $query, string $column, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to);
        }
    }

    private function applyFinalOrderStatusConstraint(Builder $query): void
    {
        $query->whereRaw(
            "TRIM(LOWER(REPLACE(COALESCE(status, ''), '_', ' '))) = ?",
            [trim(strtolower(str_replace('_', ' ', Pesanan::STATUS_SELESAI_FINAL)))]
        );
    }

    private function sumColumn(Builder $query, string $column): float
    {
        return (float) ((clone $query)->sum($column) ?? 0);
    }
}
