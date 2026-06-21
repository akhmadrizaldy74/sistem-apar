<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;

class FinalRevenueService
{
    public function productOrdersQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Pesanan::query()
            ->where('tipe', 'produk');

        $this->applyRevenueRecognitionConstraint($query);
        $this->applyDateRange($query, $from, $to);

        return $query->when($pelangganId, fn (Builder $builder, int $id) => $builder->where('pelanggan_id', $id));
    }

    public function serviceTransactionsQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Pesanan::query()
            ->where('tipe', 'service')
            ->where(function (Builder $builder) {
                $builder->where('service_jenis_layanan', 'service')
                    ->orWhereNull('service_jenis_layanan')
                    ->orWhere('service_jenis_layanan', '');
            })
            ->with(['pelanggan', 'teknisi', 'servicePaket', 'service', 'unitApars.produk']);

        $this->applyRevenueRecognitionConstraint($query);
        $this->applyDateRange($query, $from, $to);

        return $query->when($pelangganId, fn (Builder $builder, int $id) => $builder->where('pelanggan_id', $id));
    }

    public function refillTransactionsQuery(?string $from = null, ?string $to = null, ?int $pelangganId = null): Builder
    {
        $query = Pesanan::query()
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk']);

        $this->applyRevenueRecognitionConstraint($query);
        $this->applyDateRange($query, $from, $to);

        return $query->when($pelangganId, fn (Builder $builder, int $id) => $builder->where('pelanggan_id', $id));
    }

    public function breakdown(?string $from = null, ?string $to = null, ?int $pelangganId = null): array
    {
        $product = $this->sumColumn(
            $this->productOrdersQuery($from, $to, $pelangganId),
            'total_harga'
        );

        $service = $this->sumColumn(
            $this->serviceTransactionsQuery($from, $to, $pelangganId),
            'total_harga'
        );

        $refill = $this->sumColumn(
            $this->refillTransactionsQuery($from, $to, $pelangganId),
            'total_harga'
        );

        return [
            'product' => $product,
            'service' => $service,
            'refill' => $refill,
            'total' => $product + $service + $refill,
        ];
    }

    private function applyDateRange(Builder $query, ?string $from, ?string $to): void
    {
        $dateExpression = Pesanan::revenueRecognitionDateExpression();

        if ($from) {
            $query->whereRaw("DATE({$dateExpression}) >= ?", [$from]);
        }

        if ($to) {
            $query->whereRaw("DATE({$dateExpression}) <= ?", [$to]);
        }
    }

    private function applyRevenueRecognitionConstraint(Builder $query): void
    {
        $query->revenueRecognized();
    }

    private function sumColumn(Builder $query, string $column): float
    {
        return (float) ((clone $query)->selectRaw("COALESCE(SUM(COALESCE({$column}, total, 0)), 0) as aggregate")->value('aggregate') ?? 0);
    }
}
