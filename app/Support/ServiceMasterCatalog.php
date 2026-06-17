<?php

namespace App\Support;

class ServiceMasterCatalog
{
    public static function peralatanDefinitions(): array
    {
        return [
            [
                'name' => 'Selang APAR Powder/Foam',
                'harga_standar' => 35000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Selang APAR',
                    'Selang APAR Powder',
                    'Selang APAR Foam',
                    'Selang Powder/Foam',
                ],
            ],
            [
                'name' => 'Selang APAR CO2',
                'harga_standar' => 120000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Selang CO2',
                    'Selang APAR Carbon Dioxide',
                ],
            ],
            [
                'name' => 'Valve APAR',
                'harga_standar' => 50000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Valve',
                ],
            ],
            [
                'name' => 'Safety Pin APAR',
                'harga_standar' => 10000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Safety Pin (Pin Pengaman)',
                    'Pin Pengaman APAR',
                    'Safety Pin',
                ],
            ],
            [
                'name' => 'Segel Pengaman Plastik',
                'harga_standar' => 5000,
                'stok_minimum' => 5,
                'aliases' => [
                    'Segel APAR',
                    'Segel Pengaman',
                    'Segel Plastik',
                ],
            ],
            [
                'name' => 'Pressure Gauge APAR',
                'harga_standar' => 20000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Manometer APAR',
                    'Pressure Gauge',
                    'Gauge APAR',
                ],
            ],
            [
                'name' => 'O-Ring/Karet Seal',
                'harga_standar' => 5000,
                'stok_minimum' => 5,
                'aliases' => [
                    'O-Ring',
                    'ORing',
                    'Karet Seal',
                    'Karet O-Ring',
                ],
            ],
            [
                'name' => 'Bracket/Gantungan APAR',
                'harga_standar' => 25000,
                'stok_minimum' => 3,
                'aliases' => [
                    'Bracket Gantung (Hanger)',
                    'Bracket Gantung',
                    'Gantungan APAR',
                    'Bracket APAR',
                ],
            ],
            [
                'name' => 'Baut Bracket APAR',
                'harga_standar' => 5000,
                'stok_minimum' => 5,
                'aliases' => [
                    'Baut Pengunci Bracket',
                    'Baut Bracket',
                ],
            ],
        ];
    }

    public static function servicePackageDefinitions(): array
    {
        return [
            [
                'name' => 'Service Ringan',
                'label' => 'Ringan',
                'harga' => 35000,
                'aliases' => [
                    'Paket A',
                    'Inspeksi Ringan',
                ],
                'rincian' => [
                    'Pengecekan ringan unit APAR.',
                    'Penggantian safety pin APAR.',
                    'Penggantian segel pengaman plastik.',
                ],
                'peralatan' => [
                    'Safety Pin APAR' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'name' => 'Ganti Selang Powder/Foam',
                'label' => 'Selang Powder/Foam',
                'harga' => 70000,
                'aliases' => [
                    'Ganti Selang Powder',
                    'Ganti Selang Foam',
                    'Ganti Selang',
                ],
                'rincian' => [
                    'Penggantian selang APAR Powder/Foam.',
                    'Penggantian segel pengaman plastik.',
                ],
                'peralatan' => [
                    'Selang APAR Powder/Foam' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'name' => 'Ganti Selang CO2',
                'label' => 'Selang CO2',
                'harga' => 160000,
                'aliases' => [
                    'Ganti Selang Carbon Dioxide',
                ],
                'rincian' => [
                    'Penggantian selang APAR CO2.',
                    'Penggantian segel pengaman plastik.',
                ],
                'peralatan' => [
                    'Selang APAR CO2' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'name' => 'Ganti Valve APAR',
                'label' => 'Valve',
                'harga' => 100000,
                'aliases' => [
                    'Ganti Valve',
                ],
                'rincian' => [
                    'Penggantian valve APAR.',
                    'Penggantian O-Ring/karet seal.',
                    'Penggantian segel pengaman plastik.',
                ],
                'peralatan' => [
                    'Valve APAR' => 1,
                    'O-Ring/Karet Seal' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'name' => 'Ganti Pressure Gauge',
                'label' => 'Pressure Gauge',
                'harga' => 65000,
                'aliases' => [
                    'Ganti Manometer',
                    'Pressure Gauge',
                ],
                'rincian' => [
                    'Penggantian pressure gauge APAR.',
                    'Penggantian O-Ring/karet seal.',
                    'Penggantian segel pengaman plastik.',
                ],
                'peralatan' => [
                    'Pressure Gauge APAR' => 1,
                    'O-Ring/Karet Seal' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'name' => 'Pasang/Ganti Bracket',
                'label' => 'Bracket',
                'harga' => 60000,
                'aliases' => [
                    'Ganti Bracket',
                    'Pasang Bracket',
                ],
                'rincian' => [
                    'Pemasangan atau penggantian bracket APAR.',
                    'Pemasangan baut bracket APAR.',
                ],
                'peralatan' => [
                    'Bracket/Gantungan APAR' => 1,
                    'Baut Bracket APAR' => 2,
                ],
            ],
        ];
    }

    public static function canonicalPeralatanNames(): array
    {
        return array_column(self::peralatanDefinitions(), 'name');
    }

    public static function canonicalServicePackageNames(): array
    {
        return array_column(self::servicePackageDefinitions(), 'name');
    }

    public static function peralatanOrderMap(): array
    {
        return array_flip(self::canonicalPeralatanNames());
    }

    public static function servicePackageOrderMap(): array
    {
        return array_flip(self::canonicalServicePackageNames());
    }

    public static function isCanonicalPeralatanName(?string $name): bool
    {
        return in_array((string) $name, self::canonicalPeralatanNames(), true);
    }

    public static function isCanonicalServicePackageName(?string $name): bool
    {
        return in_array((string) $name, self::canonicalServicePackageNames(), true);
    }

    public static function normalize(?string $value): string
    {
        $text = mb_strtolower(trim((string) $value));
        $text = str_replace(['&'], ' dan ', $text);
        $text = preg_replace('/[^[:alnum:]]+/u', ' ', $text) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public static function matchesNameOrAlias(?string $value, string $canonical, array $aliases = []): bool
    {
        $normalizedValue = self::normalize($value);

        if ($normalizedValue === self::normalize($canonical)) {
            return true;
        }

        foreach ($aliases as $alias) {
            if ($normalizedValue === self::normalize($alias)) {
                return true;
            }
        }

        return false;
    }
}
