<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    public const JENIS_PEMBELIAN_APAR = 'pembelian_apar';
    public const JENIS_PEMBELIAN_REFILL = 'pembelian_refill';
    public const JENIS_PEMBELIAN_PERALATAN = 'pembelian_peralatan';
    public const JENIS_PENGELUARAN_LAINNYA = 'pengeluaran_lainnya';

    protected $fillable = [
        'kategori',
        'jenis_pengeluaran',
        'produk_id',
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

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function getJenisPengeluaranLabelAttribute(): string
    {
        if ($this->isLegacyOtherExpense()) {
            return 'Pengeluaran Lainnya';
        }

        return match ($this->jenis_pengeluaran) {
            self::JENIS_PEMBELIAN_APAR => 'Pembelian APAR',
            self::JENIS_PEMBELIAN_REFILL => 'Pembelian Refil',
            self::JENIS_PEMBELIAN_PERALATAN => 'Pembelian Peralatan',
            self::JENIS_PENGELUARAN_LAINNYA => 'Pengeluaran Lainnya',
            default => ucfirst((string) ($this->kategori ?: 'Pengeluaran')),
        };
    }

    public function isStockAffecting(): bool
    {
        return in_array($this->jenis_pengeluaran, [
            self::JENIS_PEMBELIAN_APAR,
            self::JENIS_PEMBELIAN_REFILL,
            self::JENIS_PEMBELIAN_PERALATAN,
        ], true);
    }

    public function isLegacyOtherExpense(): bool
    {
        return !$this->isStockAffecting();
    }

    public function getDisplayItemNameAttribute(): string
    {
        if ($this->produk) {
            return $this->produk->nama;
        }

        if ($this->jenisRefill) {
            return $this->jenisRefill->nama;
        }

        if ($this->peralatan) {
            return $this->peralatan->nama;
        }

        return (string) ($this->nama_item ?: '-');
    }
}
