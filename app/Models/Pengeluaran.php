<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    public const JENIS_PEMBELIAN_REFILL = 'pembelian_refill';
    public const JENIS_PEMBELIAN_PERALATAN = 'pembelian_peralatan';

    protected $fillable = [
        'kategori',
        'jenis_pengeluaran',
        'jenis_refill_id',
        'peralatan_id',
        'nama_item',
        'qty',
        'satuan',
        'harga_beli',
        'total',
        'keterangan',
        'nominal',
        'tanggal',
    ];

    protected $casts = [
        'qty' => 'float',
        'harga_beli' => 'float',
        'total' => 'float',
        'nominal' => 'float',
        'tanggal' => 'date',
    ];

    public function jenisRefill()
    {
        return $this->belongsTo(JenisRefill::class);
    }

    public function peralatan()
    {
        return $this->belongsTo(Peralatan::class);
    }

    public function getJenisPengeluaranLabelAttribute(): string
    {
        return match ($this->jenis_pengeluaran) {
            self::JENIS_PEMBELIAN_REFILL => 'Pembelian Refill',
            self::JENIS_PEMBELIAN_PERALATAN => 'Pembelian Peralatan / Perlengkapan',
            default => ucfirst((string) ($this->kategori ?: 'Pengeluaran')),
        };
    }
}
