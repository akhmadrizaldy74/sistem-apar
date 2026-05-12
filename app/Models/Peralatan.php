<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Peralatan extends Model
{
    protected $fillable = ['nama', 'stok', 'harga_standar', 'stok_minimum'];

    protected $casts = [
        'stok' => 'integer',
        'harga_standar' => 'float',
        'stok_minimum' => 'integer',
    ];

    public function getIsStokRendahAttribute(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    public function servicePakets(): BelongsToMany
    {
        return $this->belongsToMany(ServicePaket::class, 'service_paket_peralatan')
            ->withPivot('jumlah_estimasi');
    }
}
