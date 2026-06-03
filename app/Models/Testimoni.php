<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    use Auditable;

    protected $fillable = [
        'pelanggan_id',
        'rating',
        'review',
        'foto_path',
        'is_anonymous',
        'tanggal',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_anonymous' => 'boolean',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
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
}
