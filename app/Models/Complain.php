<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{
    use Auditable;
    protected $fillable = [
        'pelanggan_id',
        'pesanan_id',
        'service_id',
        'isi_complain',
        'status_penyelesaian',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
