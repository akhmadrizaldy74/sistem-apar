<?php

namespace Database\Seeders;

use App\Models\Peralatan;
use App\Models\ServicePaket;
use Illuminate\Database\Seeder;

class ServicePaketSeeder extends Seeder
{
    public function run(): void
    {
        $peralatans = [
            ['nama' => 'Valve APAR', 'stok' => 20, 'harga_standar' => 35000, 'stok_minimum' => 3],
            ['nama' => 'Safety Pin APAR', 'stok' => 50, 'harga_standar' => 5000, 'stok_minimum' => 10],
            ['nama' => 'Segel Pengaman Plastik', 'stok' => 50, 'harga_standar' => 3000, 'stok_minimum' => 10],
        ];

        foreach ($peralatans as $peralatan) {
            Peralatan::updateOrCreate(['nama' => $peralatan['nama']], $peralatan);
        }

        $pakets = [
            [
                'nama' => 'Inspeksi Ringan',
                'label' => 'Paket A',
                'harga' => 35000,
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => "Pemeriksaan tekanan tabung
Pengecekan pin pengaman dan segel
Pembersihan body tabung
Pemeriksaan label dan kondisi luar APAR",
                'peralatans' => [],
            ],
            [
                'nama' => 'Service Standar',
                'label' => 'Paket B',
                'harga' => 75000,
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => "Inspeksi kondisi fisik tabung, segel, dan pin pengaman
Pemeriksaan selang, nozzle, dan valve
Pembersihan body tabung dan area kepala APAR
Penggantian safety pin dan segel pengaman plastik sesuai standar paket",
                'peralatans' => [
                    'Safety Pin APAR' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'nama' => 'Service Lengkap',
                'label' => 'Paket C',
                'harga' => 150000,
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => "Pembongkaran komponen utama APAR
Pemeriksaan valve, selang, nozzle, dan tekanan kerja
Penggantian valve, safety pin, dan segel pengaman plastik sesuai standar paket
Pembersihan menyeluruh dan uji visual kebocoran ringan",
                'peralatans' => [
                    'Valve APAR' => 1,
                    'Safety Pin APAR' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
        ];

        foreach ($pakets as $paket) {
            $peralatansData = $paket['peralatans'];
            unset($paket['peralatans']);

            $servicePaket = ServicePaket::updateOrCreate(
                ['nama' => $paket['nama']],
                $paket
            );

            $peralatanIds = [];
            foreach ($peralatansData as $namaPeralatan => $jumlah) {
                $peralatan = Peralatan::where('nama', $namaPeralatan)->first();
                if ($peralatan) {
                    $peralatanIds[$peralatan->id] = ['jumlah_estimasi' => $jumlah];
                }
            }

            $servicePaket->peralatans()->sync($peralatanIds);
        }
    }
}
