<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasRefill extends Model
{
    protected $fillable = [
        'stok_batch_id',
        'produk_id',
        'teknisi_id',
        'jumlah_refill',
        'tanggal_refill',
        'catatan_admin',
        'catatan_teknisi',
        'bukti_foto',
        'status',
    ];

    protected $casts = [
        'tanggal_refill' => 'date',
    ];

    public function stokBatch()
    {
        return $this->belongsTo(StokBatch::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}
