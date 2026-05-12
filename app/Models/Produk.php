<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Produk extends Model
{
    use Auditable;
    protected $fillable = ['nama', 'merek', 'jenis_apar_id', 'kapasitas', 'penggunaan', 'harga', 'gambar', 'deskripsi', 'stok'];

    public function jenisApar()
    {
        return $this->belongsTo(JenisApar::class);
    }

    public function units()
    {
        return $this->hasMany(UnitApar::class);
    }

    public function pesanan()
    {
        return $this->hasMany(PesananDetail::class);
    }

    public function stokBatches()
    {
        return $this->hasMany(StokBatch::class);
    }

    public function pesananDetails()
    {
        return $this->hasMany(PesananDetail::class);
    }

    public function sellableStokBatches()
    {
        return $this->hasMany(StokBatch::class)
            ->where('sisa_qty', '>', 0)
            ->whereDate('tgl_expired', '>=', now()->toDateString());
    }

    public function getStokTersediaAttribute(): int
    {
        return $this->sellableBatchCollection()->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));
    }

    public function getStokBatchTotalAttribute(): int
    {
        return $this->allPositiveBatchCollection()->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));
    }

    public function getStokKadaluarsaAttribute(): int
    {
        return max(0, $this->stok_batch_total - $this->stok_tersedia);
    }

    public function getIsHabisAttribute(): bool
    {
        return $this->stok_tersedia <= 0;
    }

    public function hasEnoughSellableStock(int $qty): bool
    {
        return $this->stok_tersedia >= max(0, $qty);
    }

    private function sellableBatchCollection(): Collection
    {
        if ($this->relationLoaded('stokBatches')) {
            $today = now()->toDateString();

            return $this->stokBatches
                ->filter(function (StokBatch $batch) use ($today) {
                    $expiredAt = $batch->tgl_expired?->toDateString();

                    return (int) ($batch->sisa_qty ?? 0) > 0
                        && !empty($expiredAt)
                        && $expiredAt >= $today;
                })
                ->values();
        }

        return $this->sellableStokBatches()->get();
    }

    private function allPositiveBatchCollection(): Collection
    {
        if ($this->relationLoaded('stokBatches')) {
            return $this->stokBatches
                ->filter(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0) > 0)
                ->values();
        }

        return $this->stokBatches()
            ->where('sisa_qty', '>', 0)
            ->get();
    }
}
