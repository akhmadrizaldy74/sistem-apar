<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_po',
        'tanggal_po',
        'supplier_id',
        'total',
        'status',
        'no_surat_jalan',
        'tanggal_surat_jalan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_po' => 'date',
        'tanggal_surat_jalan' => 'date',
        'total' => 'float',
    ];

    /**
     * Auto generate nomor PO saat membuat record baru.
     * Format: PO-YYYYMMDD-XXX (contoh: PO-20260724-001)
     */
    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->nomor_po)) {
                $dateStr = now()->format('Ymd');
                $prefix = 'PO-' . $dateStr . '-';

                $lastPo = static::where('nomor_po', 'like', $prefix . '%')
                    ->withTrashed()
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastPo) {
                    $lastNum = (int) substr((string) $lastPo->nomor_po, -3);
                    $nextNum = $lastNum + 1;
                } else {
                    $nextNum = 1;
                }

                $po->nomor_po = $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }
}
