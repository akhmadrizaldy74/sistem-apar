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

        foreach (['Dry Chemical Powder', 'CO2', 'Foam'] as $nama) {
            JenisRefill::firstOrCreate(['nama' => $nama]);
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
                    ['kapasitas' => '6 Liter', 'harga' => 500000],
                    ['kapasitas' => '9 Liter', 'harga' => 650000],
                ],
            ],
        ];

        $merekHarga = [
            'SAFETY' => 1,
            'ABC' => 1.08,
            'GUARD' => 1.15,
        ];

        foreach ($produkData as $group) {
            foreach ($group['items'] as $item) {
                foreach ($merekHarga as $merek => $pengaliHarga) {
                    $group['jenis']->produks()->updateOrCreate(
                        [
                            'merek' => $merek,
                            'kapasitas' => $item['kapasitas'],
                        ],
                        [
                            'nama' => "APAR {$merek} {$group['jenis']->nama} {$item['kapasitas']}",
                            'penggunaan' => $group['penggunaan'],
                            'harga' => (int) round($item['harga'] * $pengaliHarga),
                            'stok' => 50, // Added default stock so catalog buttons show 'Tambah ke Keranjang'
                        ],
                    );
                }
            }
        }

        $this->call(ServicePaketSeeder::class);
    }
}
