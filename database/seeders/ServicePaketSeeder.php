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

        $tierResolver = function (string $name): string {
            $normalized = mb_strtolower(trim($name));

            if (
                str_contains($normalized, 'safety pin')
                || str_contains($normalized, 'pin pengaman')
                || str_contains($normalized, 'segel')
                || str_contains($normalized, 'seal')
                || str_contains($normalized, 'o-ring')
                || str_contains($normalized, 'oring')
                || str_contains($normalized, 'baut')
            ) {
                return 'A';
            }

            if (
                str_contains($normalized, 'valve')
                || str_contains($normalized, 'manometer')
                || str_contains($normalized, 'pressure gauge')
                || str_contains($normalized, 'gauge')
            ) {
                return 'C';
            }

            return 'B';
        };

        $pakets = [
            [
                'nama' => 'Service Ringan',
                'label' => 'Paket A',
                'harga' => 40000,
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => "Pemeriksaan tekanan tabung
Pengecekan pin pengaman dan segel
Pembersihan body tabung
Pemeriksaan label dan kondisi luar APAR",
            ],
            [
                'nama' => 'Service Standar',
                'label' => 'Paket B',
                'harga' => 90000,
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'rincian_layanan' => "Inspeksi kondisi fisik tabung, segel, dan pin pengaman
Pemeriksaan selang, nozzle, dan valve
Pembersihan body tabung dan area kepala APAR
Penggantian safety pin dan segel pengaman plastik sesuai standar paket",
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
            ],
        ];

        foreach ($pakets as $paket) {
            $servicePaket = ServicePaket::updateOrCreate(
                ['nama' => $paket['nama']],
                $paket
            );

            $peralatanIds = [];
            $packageTier = match ($paket['label']) {
                'Paket A' => 'A',
                'Paket C' => 'C',
                default => 'B',
            };

            foreach (Peralatan::query()->orderBy('nama')->get() as $peralatan) {
                $equipmentTier = $tierResolver((string) $peralatan->nama);
                $shouldInclude = match ($packageTier) {
                    'A' => $equipmentTier === 'A',
                    'B' => in_array($equipmentTier, ['A', 'B'], true),
                    'C' => in_array($equipmentTier, ['A', 'B', 'C'], true),
                    default => false,
                };

                if ($shouldInclude) {
                    $peralatanIds[$peralatan->id] = ['jumlah_estimasi' => 1];
                }
            }

            $servicePaket->peralatans()->sync($peralatanIds);
        }
    }
}
