<?php

namespace App\Services;

use App\Models\JenisRefill;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;

class InventoryService
{
    public function applyPurchaseExpense(Pengeluaran $pengeluaran): void
    {
        if ($pengeluaran->jenis_pengeluaran === Pengeluaran::JENIS_PEMBELIAN_REFILL) {
            $jenisRefill = JenisRefill::findOrFail($pengeluaran->jenis_refill_id);

            $this->increaseRefillStock(
                $jenisRefill,
                (float) $pengeluaran->qty,
                StockMovement::SOURCE_PEMBELIAN_PENGELUARAN,
                $pengeluaran,
                $pengeluaran->keterangan,
                $this->resolveMovementDate($pengeluaran->tanggal),
            );

            $pengeluaran->forceFill([
                'kategori' => 'refill',
                'nama_item' => $jenisRefill->nama_label,
                'satuan' => $jenisRefill->satuan_label,
                'total' => $pengeluaran->nominal,
            ])->saveQuietly();

            return;
        }

        if ($pengeluaran->jenis_pengeluaran === Pengeluaran::JENIS_PEMBELIAN_PERALATAN) {
            $peralatan = Peralatan::findOrFail($pengeluaran->peralatan_id);

            $this->increasePeralatanStock(
                $peralatan,
                (float) $pengeluaran->qty,
                StockMovement::SOURCE_PEMBELIAN_PENGELUARAN,
                $pengeluaran,
                $pengeluaran->keterangan,
                $this->resolveMovementDate($pengeluaran->tanggal),
            );

            $pengeluaran->forceFill([
                'kategori' => 'peralatan',
                'nama_item' => $peralatan->nama,
                'satuan' => 'Unit',
                'total' => $pengeluaran->nominal,
            ])->saveQuietly();
        }
    }

    public function increaseRefillStock(
        JenisRefill $jenisRefill,
        float $qty,
        string $sourceType,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
    ): void {
        $this->mutateSimpleStock(
            model: $jenisRefill,
            itemType: StockMovement::ITEM_REFILL,
            movementType: StockMovement::MOVE_IN,
            qty: $qty,
            sourceType: $sourceType,
            satuan: $jenisRefill->satuan_label,
            reference: $reference,
            keterangan: $keterangan,
            tanggal: $tanggal,
            decimals: 2,
        );
    }

    public function decreaseRefillStock(
        JenisRefill $jenisRefill,
        float $qty,
        string $sourceType,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
    ): void {
        $this->mutateSimpleStock(
            model: $jenisRefill,
            itemType: StockMovement::ITEM_REFILL,
            movementType: StockMovement::MOVE_OUT,
            qty: $qty,
            sourceType: $sourceType,
            satuan: $jenisRefill->satuan_label,
            reference: $reference,
            keterangan: $keterangan,
            tanggal: $tanggal,
            decimals: 2,
        );
    }

    public function increasePeralatanStock(
        Peralatan $peralatan,
        float $qty,
        string $sourceType,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
    ): void {
        $this->mutateSimpleStock(
            model: $peralatan,
            itemType: StockMovement::ITEM_PERALATAN,
            movementType: StockMovement::MOVE_IN,
            qty: $qty,
            sourceType: $sourceType,
            satuan: 'Unit',
            reference: $reference,
            keterangan: $keterangan,
            tanggal: $tanggal,
            decimals: 0,
        );
    }

    public function decreasePeralatanStock(
        Peralatan $peralatan,
        float $qty,
        string $sourceType,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
    ): void {
        $this->mutateSimpleStock(
            model: $peralatan,
            itemType: StockMovement::ITEM_PERALATAN,
            movementType: StockMovement::MOVE_OUT,
            qty: $qty,
            sourceType: $sourceType,
            satuan: 'Unit',
            reference: $reference,
            keterangan: $keterangan,
            tanggal: $tanggal,
            decimals: 0,
        );
    }

    public function logProductMovement(
        Produk $produk,
        float $qty,
        string $movementType,
        string $sourceType,
        float $stokSebelum,
        float $stokSesudah,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
    ): void {
        $this->createMovement([
            'item_type' => StockMovement::ITEM_PRODUK,
            'item_id' => $produk->id,
            'item_nama' => $produk->nama,
            'satuan' => 'Unit',
            'movement_type' => $movementType,
            'qty' => round($qty, 2),
            'stok_sebelum' => round($stokSebelum, 2),
            'stok_sesudah' => round($stokSesudah, 2),
            'source_type' => $sourceType,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'keterangan' => $keterangan,
            'tanggal' => $this->resolveMovementDate($tanggal),
        ]);
    }

    protected function mutateSimpleStock(
        Model $model,
        string $itemType,
        string $movementType,
        float $qty,
        string $sourceType,
        string $satuan,
        ?Model $reference = null,
        ?string $keterangan = null,
        Carbon|string|null $tanggal = null,
        int $decimals = 0,
    ): void {
        $qty = $decimals > 0 ? round($qty, $decimals) : (float) round($qty);
        if ($qty <= 0) {
            throw new RuntimeException('Qty stok harus lebih besar dari 0.');
        }

        $stokSebelum = (float) ($model->stok ?? 0);
        $stokSesudah = $movementType === StockMovement::MOVE_IN
            ? $stokSebelum + $qty
            : $stokSebelum - $qty;

        $stokSesudah = $decimals > 0 ? round($stokSesudah, $decimals) : (float) round($stokSesudah);

        if ($stokSesudah < 0) {
            throw new RuntimeException('Stok ' . $model->nama . ' tidak mencukupi. Tersedia ' . $this->formatQty($stokSebelum, $decimals) . ' ' . $satuan . '.');
        }

        $model->forceFill(['stok' => $stokSesudah])->save();

        $this->createMovement([
            'item_type' => $itemType,
            'item_id' => $model->getKey(),
            'item_nama' => (string) ($model->nama ?? class_basename($model)),
            'satuan' => $satuan,
            'movement_type' => $movementType,
            'qty' => $qty,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'source_type' => $sourceType,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'keterangan' => $keterangan,
            'tanggal' => $this->resolveMovementDate($tanggal),
        ]);
    }

    protected function createMovement(array $attributes): void
    {
        StockMovement::create($attributes);
    }

    protected function resolveMovementDate(Carbon|string|null $tanggal): Carbon
    {
        if ($tanggal instanceof Carbon) {
            return $tanggal;
        }

        if (is_string($tanggal) && $tanggal !== '') {
            return Carbon::parse($tanggal)->startOfDay();
        }

        return now();
    }

    protected function formatQty(float $qty, int $decimals = 0): string
    {
        return $decimals > 0
            ? rtrim(rtrim(number_format($qty, $decimals, ',', '.'), '0'), ',')
            : number_format($qty, 0, ',', '.');
    }
}
