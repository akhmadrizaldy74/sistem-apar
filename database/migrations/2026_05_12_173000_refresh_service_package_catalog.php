<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        $peralatanRows = [
            ['nama' => 'Valve APAR', 'stok' => 0, 'harga_standar' => 35000, 'stok_minimum' => 3],
            ['nama' => 'Safety Pin APAR', 'stok' => 0, 'harga_standar' => 5000, 'stok_minimum' => 10],
            ['nama' => 'Segel Pengaman Plastik', 'stok' => 0, 'harga_standar' => 3000, 'stok_minimum' => 10],
        ];

        foreach ($peralatanRows as $row) {
            $existing = DB::table('peralatans')->where('nama', $row['nama'])->first();

            if ($existing) {
                DB::table('peralatans')
                    ->where('id', $existing->id)
                    ->update([
                        'harga_standar' => $row['harga_standar'],
                        'stok_minimum' => $row['stok_minimum'],
                        'updated_at' => $timestamp,
                    ]);

                continue;
            }

            DB::table('peralatans')->insert([
                'nama' => $row['nama'],
                'stok' => $row['stok'],
                'harga_standar' => $row['harga_standar'],
                'stok_minimum' => $row['stok_minimum'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $servicePackages = [
            [
                'aliases' => ['service ringan', 'paket a', 'inspeksi ringan'],
                'nama' => 'Inspeksi Ringan',
                'label' => 'Paket A',
                'harga' => 35000,
                'rincian_layanan' => "Pemeriksaan tekanan tabung\nPengecekan pin pengaman dan segel\nPembersihan body tabung\nPemeriksaan label dan kondisi luar APAR",
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'peralatan' => [],
            ],
            [
                'aliases' => ['service sedang', 'paket b', 'service standar'],
                'nama' => 'Service Standar',
                'label' => 'Paket B',
                'harga' => 75000,
                'rincian_layanan' => "Inspeksi kondisi fisik tabung, segel, dan pin pengaman\nPemeriksaan selang, nozzle, dan valve\nPembersihan body tabung dan area kepala APAR\nPenggantian safety pin dan segel pengaman plastik sesuai standar paket",
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'peralatan' => [
                    'Safety Pin APAR' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
            [
                'aliases' => ['service lengkap', 'paket c'],
                'nama' => 'Service Lengkap',
                'label' => 'Paket C',
                'harga' => 150000,
                'rincian_layanan' => "Pembongkaran komponen utama APAR\nPemeriksaan valve, selang, nozzle, dan tekanan kerja\nPenggantian valve, safety pin, dan segel pengaman plastik sesuai standar paket\nPembersihan menyeluruh dan uji visual kebocoran ringan",
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'peralatan' => [
                    'Valve APAR' => 1,
                    'Safety Pin APAR' => 1,
                    'Segel Pengaman Plastik' => 1,
                ],
            ],
        ];

        foreach ($servicePackages as $package) {
            $aliases = $package['aliases'];
            $peralatan = $package['peralatan'];

            unset($package['aliases'], $package['peralatan']);

            $existingId = DB::table('service_pakets')
                ->where(function ($query) use ($aliases) {
                    foreach ($aliases as $alias) {
                        $query->orWhereRaw('LOWER(nama) = ?', [$alias])
                            ->orWhereRaw('LOWER(label) = ?', [$alias]);
                    }
                })
                ->value('id');

            if ($existingId) {
                DB::table('service_pakets')
                    ->where('id', $existingId)
                    ->update(array_merge($package, [
                        'updated_at' => $timestamp,
                    ]));
                $servicePaketId = $existingId;
            } else {
                $servicePaketId = DB::table('service_pakets')->insertGetId(array_merge($package, [
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]));
            }

            DB::table('service_paket_peralatan')->where('service_paket_id', $servicePaketId)->delete();

            foreach ($peralatan as $namaPeralatan => $jumlah) {
                $peralatanId = DB::table('peralatans')->where('nama', $namaPeralatan)->value('id');
                if (!$peralatanId) {
                    continue;
                }

                DB::table('service_paket_peralatan')->insert([
                    'service_paket_id' => $servicePaketId,
                    'peralatan_id' => $peralatanId,
                    'jumlah_estimasi' => $jumlah,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
