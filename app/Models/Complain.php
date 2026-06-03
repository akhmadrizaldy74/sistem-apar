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
        'foto_path',
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

    public function displaySubmittedAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->created_at) {
            return $this->created_at->copy()->timezone(config('app.timezone'));
        }

        return $this->tanggal?->copy()
            ->timezone(config('app.timezone'))
            ->startOfDay();
    }

    public function displaySubmittedDateTime(string $format = 'd M Y, H:i'): string
    {
        return $this->displaySubmittedAt()?->format($format) ?? '-';
    }

    public function relatedPesanan(): ?Pesanan
    {
        return $this->pesanan ?: $this->service?->pesanan;
    }

    public function relatedService(): ?Service
    {
        return $this->service ?: $this->pesanan?->service;
    }

    public function relatedRefill(): ?Refill
    {
        return $this->relatedService()?->refill;
    }

    public function relatedTransactionType(): string
    {
        $pesanan = $this->relatedPesanan();

        if ($pesanan?->isRefillOrder()) {
            return 'refill';
        }

        if ($pesanan?->isServiceOrder()) {
            return 'service';
        }

        if ($pesanan) {
            return 'pesanan';
        }

        if ($this->relatedRefill()) {
            return 'refill';
        }

        if ($this->relatedService()) {
            return 'service';
        }

        return 'umum';
    }

    public function relatedTransactionLabel(): string
    {
        return match ($this->relatedTransactionType()) {
            'pesanan' => 'Pesanan Produk',
            'service' => 'Service APAR',
            'refill' => 'Refill APAR',
            default => 'Umum',
        };
    }

    public function relatedTransactionAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->relatedPesanan()) {
            return $this->relatedPesanan()->displayTransactionAt();
        }

        if ($this->relatedTransactionType() === 'refill' && $this->relatedRefill()) {
            return $this->relatedRefill()->displayTransactionAt();
        }

        if ($this->relatedTransactionType() === 'service' && $this->relatedService()) {
            return $this->relatedService()->displayTransactionAt();
        }

        return $this->displaySubmittedAt();
    }

    public function relatedTransactionDateTime(string $format = 'd M Y, H:i'): string
    {
        return $this->relatedTransactionAt()?->format($format) ?? '-';
    }
}
