<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServicePaket extends Model
{
    protected $fillable = ['nama', 'label', 'harga', 'jenis_refill_id', 'refill_ratio', 'rincian_layanan'];

    protected $casts = [
        'harga' => 'float',
        'refill_ratio' => 'float',
    ];

    public function peralatans(): BelongsToMany
    {
        return $this->belongsToMany(Peralatan::class, 'service_paket_peralatan')
            ->withPivot('jumlah_estimasi');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function jenisRefill()
    {
        return $this->belongsTo(JenisRefill::class);
    }

    public function getRincianListAttribute(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->rincian_layanan))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function isLegacyTemplate(): bool
    {
        $candidates = [
            mb_strtolower(trim((string) $this->label)),
            mb_strtolower(trim((string) $this->nama)),
        ];

        foreach ($candidates as $candidate) {
            if (in_array($candidate, [
                'service ringan',
                'service sedang',
                'service lengkap',
                'paket a',
                'paket b',
                'paket c',
                'inspeksi ringan',
                'service standar',
            ], true)) {
                return true;
            }
        }

        return false;
    }
}
