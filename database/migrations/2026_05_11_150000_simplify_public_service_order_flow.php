<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_refills', function (Blueprint $table) {
            $table->text('service_price_rules_json')->nullable()->after('harga');
        });

        Schema::table('service_pakets', function (Blueprint $table) {
            $table->foreignId('jenis_refill_id')->nullable()->after('harga')->constrained('jenis_refills')->nullOnDelete();
            $table->decimal('refill_ratio', 5, 2)->default(0)->after('jenis_refill_id');
        });

        Schema::table('pesanans', function (Blueprint $table) {
            $table->foreignId('service_paket_id')->nullable()->after('service_jenis_layanan')->constrained('service_pakets')->nullOnDelete();
            $table->foreignId('service_jenis_refill_id')->nullable()->after('service_paket_id')->constrained('jenis_refills')->nullOnDelete();
            $table->string('service_ukuran_apar', 120)->nullable()->after('service_jenis_apar');
            $table->decimal('service_total_kg', 12, 2)->nullable()->after('service_jumlah_unit');
        });

        $rules = [
            'powder' => json_encode([
                ['ukuran' => '1 Kg', 'harga' => 30000],
                ['ukuran' => '2 Kg', 'harga' => 50000],
                ['ukuran' => '3 Kg', 'harga' => 70000],
                ['ukuran' => '4 Kg', 'harga' => 90000],
                ['ukuran' => '5 Kg', 'harga' => 115000],
                ['ukuran' => '6 Kg', 'harga' => 135000],
                ['ukuran' => '6.8 Kg', 'harga' => 150000],
                ['ukuran' => '9 Kg', 'harga' => 190000],
                ['ukuran' => '6 Liter', 'harga' => 150000],
                ['ukuran' => '9 Liter', 'harga' => 210000],
            ]),
            'foam' => json_encode([
                ['ukuran' => '1 Kg', 'harga' => 35000],
                ['ukuran' => '2 Kg', 'harga' => 60000],
                ['ukuran' => '3 Kg', 'harga' => 80000],
                ['ukuran' => '4 Kg', 'harga' => 100000],
                ['ukuran' => '5 Kg', 'harga' => 125000],
                ['ukuran' => '6 Kg', 'harga' => 145000],
                ['ukuran' => '6.8 Kg', 'harga' => 165000],
                ['ukuran' => '9 Kg', 'harga' => 210000],
                ['ukuran' => '6 Liter', 'harga' => 160000],
                ['ukuran' => '9 Liter', 'harga' => 225000],
            ]),
            'co2' => json_encode([
                ['ukuran' => '1 Kg', 'harga' => 45000],
                ['ukuran' => '2 Kg', 'harga' => 65000],
                ['ukuran' => '3 Kg', 'harga' => 85000],
                ['ukuran' => '4 Kg', 'harga' => 110000],
                ['ukuran' => '5 Kg', 'harga' => 130000],
                ['ukuran' => '6 Kg', 'harga' => 150000],
                ['ukuran' => '6.8 Kg', 'harga' => 170000],
                ['ukuran' => '9 Kg', 'harga' => 230000],
                ['ukuran' => '6 Liter', 'harga' => 160000],
                ['ukuran' => '9 Liter', 'harga' => 235000],
            ]),
        ];

        DB::table('jenis_refills')->orderBy('id')->get()->each(function ($jenisRefill) use ($rules) {
            $nama = strtolower((string) $jenisRefill->nama);
            $payload = null;

            if (str_contains($nama, 'powder')) {
                $payload = $rules['powder'];
            } elseif (str_contains($nama, 'foam')) {
                $payload = $rules['foam'];
            } elseif (str_contains($nama, 'co2') || str_contains($nama, 'carbon')) {
                $payload = $rules['co2'];
            }

            if ($payload) {
                DB::table('jenis_refills')
                    ->where('id', $jenisRefill->id)
                    ->update(['service_price_rules_json' => $payload]);
            }
        });

        $peralatanRows = [
            ['nama' => 'Valve APAR', 'stok' => 0, 'stok_minimum' => 3, 'harga_standar' => 35000],
            ['nama' => 'Seal / Segel APAR', 'stok' => 0, 'stok_minimum' => 10, 'harga_standar' => 5000],
            ['nama' => 'Pin Pengaman APAR', 'stok' => 0, 'stok_minimum' => 10, 'harga_standar' => 7000],
        ];

        foreach ($peralatanRows as $row) {
            DB::table('peralatans')->updateOrInsert(
                ['nama' => $row['nama']],
                [
                    'stok' => $row['stok'],
                    'stok_minimum' => $row['stok_minimum'],
                    'harga_standar' => $row['harga_standar'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $powderId = DB::table('jenis_refills')
            ->whereRaw('LOWER(nama) like ?', ['%powder%'])
            ->value('id');

        $servicePackages = [
            [
                'nama' => 'Service Ringan',
                'label' => 'Service Ringan',
                'harga' => 35000,
                'rincian_layanan' => "Cek tekanan\nCek segel\nPembersihan",
                'jenis_refill_id' => null,
                'refill_ratio' => 0,
                'peralatan' => [],
            ],
            [
                'nama' => 'Service Sedang',
                'label' => 'Service Sedang',
                'harga' => 75000,
                'rincian_layanan' => "Cek selang\nCek valve\nRefill sebagian",
                'jenis_refill_id' => $powderId,
                'refill_ratio' => 0.5,
                'peralatan' => [
                    'Seal / Segel APAR' => 1,
                ],
            ],
            [
                'nama' => 'Service Lengkap',
                'label' => 'Service Lengkap',
                'harga' => 150000,
                'rincian_layanan' => "Bongkar APAR\nGanti perlengkapan\nRefill penuh\nPengecekan total",
                'jenis_refill_id' => $powderId,
                'refill_ratio' => 1,
                'peralatan' => [
                    'Valve APAR' => 1,
                    'Seal / Segel APAR' => 1,
                    'Pin Pengaman APAR' => 1,
                ],
            ],
        ];

        foreach ($servicePackages as $package) {
            $peralatan = $package['peralatan'];
            unset($package['peralatan']);

            $id = DB::table('service_pakets')->updateOrInsert(
                ['nama' => $package['nama']],
                array_merge($package, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]),
            );

            $servicePaketId = DB::table('service_pakets')->where('nama', $package['nama'])->value('id');
            if (! $servicePaketId) {
                continue;
            }

            DB::table('service_paket_peralatan')->where('service_paket_id', $servicePaketId)->delete();

            foreach ($peralatan as $namaPeralatan => $qty) {
                $peralatanId = DB::table('peralatans')->where('nama', $namaPeralatan)->value('id');
                if (! $peralatanId) {
                    continue;
                }

                DB::table('service_paket_peralatan')->insert([
                    'service_paket_id' => $servicePaketId,
                    'peralatan_id' => $peralatanId,
                    'jumlah_estimasi' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_jenis_refill_id');
            $table->dropConstrainedForeignId('service_paket_id');
            $table->dropColumn([
                'service_ukuran_apar',
                'service_total_kg',
            ]);
        });

        Schema::table('service_pakets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_refill_id');
            $table->dropColumn('refill_ratio');
        });

        Schema::table('jenis_refills', function (Blueprint $table) {
            $table->dropColumn('service_price_rules_json');
        });
    }
};
