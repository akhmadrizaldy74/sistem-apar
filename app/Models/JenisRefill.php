<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisRefill extends Model
{
    protected $fillable = ['nama', 'stok', 'satuan', 'harga', 'service_price_rules_json', 'stok_minimum'];

    protected $casts = [
        'stok' => 'float',
        'harga' => 'float',
        'service_price_rules_json' => 'array',
        'stok_minimum' => 'float',
    ];

    public function refills()
    {
        return $this->hasMany(Refill::class);
    }

    /** true jika stok <= stok_minimum */
    public function getIsStokRendahAttribute(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    public function getSatuanLabelAttribute(): string
    {
        return 'Kg';
    }

    public function getNamaLabelAttribute(): string
    {
        $nama = strtolower((string) $this->nama);

        if (str_contains($nama, 'powder')) {
            return 'Powder';
        }

        if (str_contains($nama, 'foam')) {
            return 'Foam';
        }

        if (str_contains($nama, 'co2') || str_contains($nama, 'carbon')) {
            return 'CO2';
        }

        return $this->nama;
    }

    public function resolveServicePrice(string $ukuran): ?float
    {
        $ukuranNormalized = strtolower(trim($ukuran));
        $rules = collect($this->service_price_rules_json ?? []);

        $matched = $rules->first(function ($rule) use ($ukuranNormalized) {
            return strtolower(trim((string) ($rule['ukuran'] ?? ''))) === $ukuranNormalized;
        });

        if (! $matched && preg_match('/(\d+(?:[.,]\d+)?)/', $ukuranNormalized, $selectedSize)) {
            $selectedNumber = (float) str_replace(',', '.', $selectedSize[1]);

            $matched = $rules->first(function ($rule) use ($selectedNumber) {
                $ukuranRule = strtolower(trim((string) ($rule['ukuran'] ?? '')));

                if (! preg_match('/(\d+(?:[.,]\d+)?)/', $ukuranRule, $ruleSize)) {
                    return false;
                }

                return (float) str_replace(',', '.', $ruleSize[1]) === $selectedNumber;
            });
        }

        return $matched ? (float) ($matched['harga'] ?? 0) : null;
    }

    public function getServicePriceRulesAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (blank($value)) {
            return [];
        }

        return json_decode((string) $value, true) ?: [];
    }
}
