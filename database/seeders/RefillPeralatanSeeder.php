<?php

namespace Database\Seeders;

use App\Models\JenisRefill;
use App\Models\Peralatan;
use Illuminate\Database\Seeder;

class RefillPeralatanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Refill
        $refills = [
            ['nama' => 'Dry Chemical Powder', 'stok' => 500.0, 'satuan' => 'Kg', 'harga' => 25000, 'stok_minimum' => 50.0],
            ['nama' => 'CO2', 'stok' => 300.0, 'satuan' => 'Kg', 'harga' => 35000, 'stok_minimum' => 30.0],
            ['nama' => 'Foam', 'stok' => 450.0, 'satuan' => 'L', 'harga' => 20000, 'stok_minimum' => 40.0],
        ];

        foreach ($refills as $ref) {
            JenisRefill::updateOrCreate(
                ['nama' => $ref['nama']],
                [
                    'stok' => $ref['stok'],
                    'satuan' => $ref['satuan'],
                    'harga' => $ref['harga'],
                    'stok_minimum' => $ref['stok_minimum'],
                ]
            );
        }

        // 2. Seed Peralatan
        $peralatans = [
            ['nama' => 'Selang APAR', 'stok' => 50, 'harga_standar' => 35000, 'stok_minimum' => 10],
            ['nama' => 'Baut Pengunci bracket', 'stok' => 200, 'harga_standar' => 5000, 'stok_minimum' => 30],
            ['nama' => 'Bracket Gantung (Hanger)', 'stok' => 45, 'harga_standar' => 15000, 'stok_minimum' => 8],
            ['nama' => 'Safety Pin (Pin Pengaman)', 'stok' => 120, 'harga_standar' => 8000, 'stok_minimum' => 20],
            ['nama' => 'Nozzle Corong CO2', 'stok' => 25, 'harga_standar' => 45000, 'stok_minimum' => 5],
        ];

        foreach ($peralatans as $per) {
            Peralatan::updateOrCreate(
                ['nama' => $per['nama']],
                [
                    'stok' => $per['stok'],
                    'harga_standar' => $per['harga_standar'],
                    'stok_minimum' => $per['stok_minimum'],
                ]
            );
        }
    }
}
