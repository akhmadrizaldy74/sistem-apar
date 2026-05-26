<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use Auditable;
    protected $fillable = [
        'pesanan_id',
        'unit_apar_id',
        'service_paket_id',
        'jenis_service',
        'rincian_layanan',
        'tgl_service',
        'keterangan',
        'biaya',
        'estimasi_peralatan_json',
        'actual_peralatan_json',
        'catatan_teknisi',
        'laporan_foto',
        'tgl_selesai_admin',
        'status_konfirmasi',
        'stok_kurang_history_json',
    ];

    protected $casts = [
        'tgl_service' => 'date',
        'tgl_selesai_admin' => 'datetime',
    ];

    public function unitApar(): BelongsTo
    {
        return $this->belongsTo(UnitApar::class);
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function servicePaket(): BelongsTo
    {
        return $this->belongsTo(ServicePaket::class);
    }

    public function refill()
    {
        return $this->hasOne(Refill::class);
    }

    public function getEstimasiPeralatanAttribute(): array
    {
        return $this->estimasi_peralatan_json
            ? json_decode($this->estimasi_peralatan_json, true)
            : [];
    }

    public function getActualPeralatanAttribute(): array
    {
        return $this->actual_peralatan_json
            ? json_decode($this->actual_peralatan_json, true)
            : [];
    }

    public function getStokKurangHistoryAttribute(): array
    {
        return $this->stok_kurang_history_json
            ? json_decode($this->stok_kurang_history_json, true)
            : [];
    }

    public function getDisplayCustomerNameAttribute(): string
    {
        return (string) ($this->unitApar?->pelanggan?->nama
            ?: $this->pesanan?->pelanggan?->nama
            ?: '-');
    }

    public function getDisplayUnitLabelAttribute(): string
    {
        if ($this->unitApar?->no_seri) {
            return (string) $this->unitApar->no_seri;
        }

        if ($this->pesanan_id) {
            return 'Unit Request Pelanggan';
        }

        return 'Tanpa Seri';
    }

    public function getEffectivePeralatanAttribute(): array
    {
        $actual = $this->actual_peralatan;

        if (!empty($actual)) {
            return $actual;
        }

        return $this->estimasi_peralatan;
    }

    public function transactionDisplayName(): string
    {
        return $this->refill ? 'Refill APAR' : 'Service APAR';
    }

    public function displayTransactionAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->created_at) {
            return $this->created_at->copy()->timezone(config('app.timezone'));
        }

        return $this->tgl_service?->copy()
            ->timezone(config('app.timezone'))
            ->startOfDay();
    }

    public function displayTransactionDateTime(string $format = 'd M Y, H:i'): string
    {
        return $this->displayTransactionAt()?->format($format) ?? '-';
    }
}
