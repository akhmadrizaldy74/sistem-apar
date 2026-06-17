<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'tgl_beli' => 'date',
        'tgl_produksi' => 'date',
        'tgl_expired' => 'date',
    ];

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

    public static function extractSizeKg($ukuran): ?float
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?)/', (string) $ukuran, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }

    public static function calculateExpiry($productionDate, $ukuran = null, $bahan = null)
    {
        $date = Carbon::parse($productionDate)->startOfDay();

        if (self::extractSizeKg($ukuran) === 1.0) {
            return $date->addMonths(6);
        }

        return $date->addYear();
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

        $pelangganId = $pelanggan ? $pelanggan->id : null;
        $existingCount = self::where('pelanggan_id', $pelangganId)->count();
        $urutan = $existingCount + 1;
        
        $serial = sprintf('%s-%02d', $baseSerial, $urutan);

        while (self::where('no_seri', $serial)->exists()) {
            $urutan++;
            $serial = sprintf('%s-%02d', $baseSerial, $urutan);
        }

        return $serial;
    }
}
