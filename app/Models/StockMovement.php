<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    public const ITEM_PRODUK = 'produk';
    public const ITEM_REFILL = 'refill';
    public const ITEM_PERALATAN = 'peralatan';

    public const MOVE_IN = 'masuk';
    public const MOVE_OUT = 'keluar';

    public const SOURCE_PEMBELIAN_PENGELUARAN = 'pembelian_pengeluaran';
    public const SOURCE_REFILL_PELANGGAN = 'refill_pelanggan';
    public const SOURCE_SERVICE_PELANGGAN = 'service_pelanggan';
    public const SOURCE_PENJUALAN_PRODUK = 'penjualan_produk';
    public const SOURCE_BATCH_APAR = 'batch_apar';
    public const SOURCE_HASIL_REFILL_BATCH = 'hasil_refill_batch';

    protected $fillable = [
        'item_type',
        'item_id',
        'item_nama',
        'satuan',
        'movement_type',
        'qty',
        'stok_sebelum',
        'stok_sesudah',
        'source_type',
        'reference_type',
        'reference_id',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'qty' => 'float',
        'stok_sebelum' => 'float',
        'stok_sesudah' => 'float',
        'tanggal' => 'datetime',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            self::ITEM_PRODUK => 'Produk APAR',
            self::ITEM_REFILL => 'Media Refill',
            self::ITEM_PERALATAN => 'Peralatan',
            default => ucfirst((string) $this->item_type),
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_PEMBELIAN_PENGELUARAN => 'Pembelian dari Pengeluaran',
            self::SOURCE_REFILL_PELANGGAN => 'Refill Pelanggan',
            self::SOURCE_SERVICE_PELANGGAN => 'Service Pelanggan',
            self::SOURCE_PENJUALAN_PRODUK => 'Penjualan Produk',
            self::SOURCE_BATCH_APAR => 'Tambah Batch APAR',
            self::SOURCE_HASIL_REFILL_BATCH => 'Hasil Refill Batch',
            default => ucwords(str_replace('_', ' ', (string) $this->source_type)),
        };
    }
}
