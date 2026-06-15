<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use Auditable;
    protected $fillable = [
        'user_id',
        'nama',
        'perusahaan',
        'no_wa',
        'alamat',
        'alamat_maps',
        'alamat_detail',
        'alamat_lat',
        'alamat_lng',
        'alamat_provinsi',
        'alamat_kota',
        'alamat_kecamatan',
        'alamat_kode_pos',
        'status',
        'sumber_data',
        'kategori_pelanggan',
        'catatan_internal',
    ];

    public static function excludedPurchaseStatuses(): array
    {
        return ['ditolak', 'dibatalkan', 'batal', 'cancelled', 'canceled'];
    }

    public function scopeLinkedToCustomerAccount(Builder $query): Builder
    {
        return $query
            ->whereNotNull('user_id')
            ->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role', 'pelanggan'));
    }

    public function scopeVisibleInDirectory(Builder $query): Builder
    {
        return $query->linkedToCustomerAccount();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function units()
    {
        return $this->hasMany(UnitApar::class);
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }

    public function productOrders()
    {
        return $this->hasMany(Pesanan::class)
            ->where('tipe', 'produk')
            ->whereNotIn('status', self::excludedPurchaseStatuses());
    }

    public function testimonis()
    {
        return $this->hasMany(Testimoni::class);
    }

    public function complains()
    {
        return $this->hasMany(Complain::class);
    }
}
