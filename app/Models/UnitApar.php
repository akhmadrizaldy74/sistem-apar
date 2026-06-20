<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UnitApar extends Model
{
    use Auditable;

    protected $fillable = [
        'pelanggan_id',
        'pesanan_id',
        'produk_id',
        'no_seri',
        'lokasi_unit',
        'tgl_beli',
        'tgl_produksi',
        'ukuran',
        'bahan',
        'kondisi_awal',
        'catatan_unit',
        'tgl_expired',
        'hidden_at',
    ];

    protected $casts = [
        'tgl_beli' => 'date',
        'tgl_produksi' => 'date',
        'tgl_expired' => 'date',
        'hidden_at' => 'datetime',
    ];

    protected static array $tableColumnCache = [];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function refills()
    {
        return $this->hasMany(Refill::class);
    }

    public static function supportsDatabaseColumn(string $column): bool
    {
        return (new static())->hasDatabaseColumn($column);
    }

    public function scopeVisible(Builder $query): Builder
    {
        if (! static::supportsDatabaseColumn('hidden_at')) {
            return $query;
        }

        return $query->whereNull($query->getModel()->qualifyColumn('hidden_at'));
    }

    public function isHiddenFromListings(): bool
    {
        if (! static::supportsDatabaseColumn('hidden_at')) {
            return false;
        }

        return ! is_null($this->getAttribute('hidden_at'));
    }

    public function hideFromListings(): bool
    {
        if (! static::supportsDatabaseColumn('hidden_at')) {
            return false;
        }

        if ($this->isHiddenFromListings()) {
            return true;
        }

        return (bool) $this->forceFill([
            'hidden_at' => now(),
        ])->save();
    }

    public static function extractSizeKg($ukuran): ?float
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?)/', (string) $ukuran, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }

    public static function usesSixMonthExpiry($ukuran): bool
    {
        $sizeKg = self::extractSizeKg($ukuran);

        return ! is_null($sizeKg) && abs($sizeKg - 1.0) < 0.0001;
    }

    public static function calculateExpiry($productionDate, $ukuran = null, $bahan = null)
    {
        $date = Carbon::parse($productionDate)->startOfDay();

        if (self::usesSixMonthExpiry($ukuran)) {
            return $date->copy()->addMonthsNoOverflow(6);
        }

        return $date->copy()->addYearNoOverflow();
    }

    public static function generateSerialNumber(?Pelanggan $pelanggan, $tanggal): string
    {
        $namaLengkap = trim((string) ($pelanggan?->nama ?? 'APAR'));
        $namaArray = explode(' ', $namaLengkap);
        $namaAwal = preg_replace('/[^A-Za-z0-9]/', '', $namaArray[0]);
        if (empty($namaAwal)) {
            $namaAwal = 'APAR';
        }
        $namaAwal = strtoupper($namaAwal);

        $kodeTanggal = Carbon::parse($tanggal)->format('dmY');
        $baseSerial = $namaAwal . '-' . $kodeTanggal;

        $existingCount = self::query()
            ->where('no_seri', 'like', $baseSerial . '-%')
            ->count();
        $urutan = $existingCount + 1;

        $serial = sprintf('%s-%02d', $baseSerial, $urutan);

        while (self::where('no_seri', $serial)->exists()) {
            $urutan++;
            $serial = sprintf('%s-%02d', $baseSerial, $urutan);
        }

        return $serial;
    }

    protected function hasDatabaseColumn(string $column): bool
    {
        $table = $this->getTable();

        if (! array_key_exists($table, static::$tableColumnCache)) {
            try {
                static::$tableColumnCache[$table] = Schema::getColumnListing($table);
            } catch (\Throwable) {
                static::$tableColumnCache[$table] = [];
            }
        }

        return in_array($column, static::$tableColumnCache[$table], true);
    }
}
