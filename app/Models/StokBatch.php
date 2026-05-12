<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBatch extends Model
{
    protected $fillable = [
        'produk_id',
        'jumlah_masuk',
        'sisa_qty',
        'tgl_produksi',
        'tgl_expired',
        'keterangan',
    ];

    protected $casts = [
        'tgl_produksi' => 'date',
        'tgl_expired' => 'date',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function tugasRefills()
    {
        return $this->hasMany(TugasRefill::class);
    }
}
