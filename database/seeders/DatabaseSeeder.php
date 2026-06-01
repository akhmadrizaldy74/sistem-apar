<?php

namespace Database\Seeders;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Sistem',
                'no_telpon' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'teknisi@gmail.com'],
            [
                'name' => 'Teknisi',
                'no_telpon' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
            ],
        );

        $powder = JenisApar::firstOrCreate(['nama' => 'Dry Chemical Powder']);
        $co2 = JenisApar::firstOrCreate(['nama' => 'Carbon Dioxide (CO2)']);
        $foam = JenisApar::firstOrCreate(['nama' => 'Liquid Foam (Busa)']);

        $refillRules = [
            'Dry Chemical Powder' => [
                'satuan' => 'Kg',
                'harga' => 25000,
                'stok_minimum' => 50,
                'service_price_rules_json' => [
                    ['ukuran' => '1 Kg', 'harga' => 30000],
                    ['ukuran' => '2 Kg', 'harga' => 50000],
                    ['ukuran' => '3 Kg', 'harga' => 70000],
                    ['ukuran' => '4 Kg', 'harga' => 90000],
                    ['ukuran' => '5 Kg', 'harga' => 115000],
                    ['ukuran' => '6 Kg', 'harga' => 135000],
                    ['ukuran' => '6.8 Kg', 'harga' => 150000],
                    ['ukuran' => '9 Kg', 'harga' => 190000],
                    ['ukuran' => '6 kg', 'harga' => 150000],
                    ['ukuran' => '9 kg', 'harga' => 210000],
                ],
            ],
            'CO2' => [
                'satuan' => 'Kg',
                'harga' => 35000,
                'stok_minimum' => 30,
                'service_price_rules_json' => [
                    ['ukuran' => '1 Kg', 'harga' => 45000],
                    ['ukuran' => '2 Kg', 'harga' => 65000],
                    ['ukuran' => '3 Kg', 'harga' => 85000],
                    ['ukuran' => '4 Kg', 'harga' => 110000],
                    ['ukuran' => '5 Kg', 'harga' => 130000],
                    ['ukuran' => '6 Kg', 'harga' => 150000],
                    ['ukuran' => '6.8 Kg', 'harga' => 170000],
                    ['ukuran' => '9 Kg', 'harga' => 230000],
                    ['ukuran' => '6 kg', 'harga' => 160000],
                    ['ukuran' => '9 kg', 'harga' => 235000],
                ],
            ],
            'Foam' => [
                'satuan' => 'L',
                'harga' => 20000,
                'stok_minimum' => 40,
                'service_price_rules_json' => [
                    ['ukuran' => '1 Kg', 'harga' => 35000],
                    ['ukuran' => '2 Kg', 'harga' => 60000],
                    ['ukuran' => '3 Kg', 'harga' => 80000],
                    ['ukuran' => '4 Kg', 'harga' => 100000],
                    ['ukuran' => '5 Kg', 'harga' => 125000],
                    ['ukuran' => '6 Kg', 'harga' => 145000],
                    ['ukuran' => '6.8 Kg', 'harga' => 165000],
                    ['ukuran' => '9 Kg', 'harga' => 210000],
                    ['ukuran' => '6 kg', 'harga' => 160000],
                    ['ukuran' => '9 kg', 'harga' => 225000],
                ],
            ],
        ];

        foreach ($refillRules as $nama => $payload) {
            JenisRefill::updateOrCreate(['nama' => $nama], $payload);
        }

        $produkData = [
            [
                'jenis' => $powder,
                'penggunaan' => 'Perkantoran, rumah, kendaraan, gudang',
                'items' => [
                    ['kapasitas' => '1 kg', 'harga' => 150000],
                    ['kapasitas' => '2 kg', 'harga' => 200000],
                    ['kapasitas' => '3 kg', 'harga' => 300000],
                    ['kapasitas' => '4 kg', 'harga' => 400000],
                    ['kapasitas' => '6 kg', 'harga' => 550000],
                    ['kapasitas' => '9 kg', 'harga' => 750000],
                ],
            ],
            [
                'jenis' => $co2,
                'penggunaan' => 'Ruang server, panel listrik, laboratorium',
                'items' => [
                    ['kapasitas' => '2 kg', 'harga' => 450000],
                    ['kapasitas' => '3 kg', 'harga' => 550000],
                    ['kapasitas' => '5 kg', 'harga' => 750000],
                    ['kapasitas' => '6.8 kg', 'harga' => 950000],
                ],
            ],
            [
                'jenis' => $foam,
                'penggunaan' => 'Dapur, SPBU, industri cairan mudah terbakar',
                'items' => [
                    ['kapasitas' => '6 kg', 'harga' => 500000],
                    ['kapasitas' => '9 kg', 'harga' => 650000],
                ],
            ],
        ];

        $merekHarga = [
            'FIREFIX' => 1,
            'GuardALL' => 1.08,
            'TONATA' => 1.15,
        ];

        $jenisShortLabel = [
            'Dry Chemical Powder' => 'Powder',
            'Carbon Dioxide (CO2)' => 'CO2',
            'Liquid Foam (Busa)' => 'Foam',
        ];

        foreach ($produkData as $group) {
            foreach ($group['items'] as $item) {
                foreach ($merekHarga as $merek => $pengaliHarga) {
                    $jenisName = $group['jenis']->nama;
                    $jenisLabel = $jenisShortLabel[$jenisName] ?? $jenisName;
                    $namaProduk = "APAR {$merek} {$jenisLabel} {$item['kapasitas']}";

                    $group['jenis']->produks()->updateOrCreate(
                        [
                            'merek' => $merek,
                            'kapasitas' => $item['kapasitas'],
                        ],
                        [
                            'nama' => $namaProduk,
                            'penggunaan' => $group['penggunaan'],
                            'harga' => (int) round($item['harga'] * $pengaliHarga),
                            'stok' => 50,
                        ],
                    );
                }
            }
        }

        $this->call(ServicePaketSeeder::class);
    }
}
