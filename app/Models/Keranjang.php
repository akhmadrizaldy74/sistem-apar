<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $fillable = [
        'user_id',
        'produk_id',
        'qty',
        'harga',
        'tipe_item',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->harga * $this->qty;
    }
}
