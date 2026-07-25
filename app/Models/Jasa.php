<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jasa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jasa';

    protected $fillable = [
        'nama_jasa',
        'deskripsi',
        'harga',
        'status',
    ];

    protected $casts = [
        'harga' => 'float',
    ];
}
