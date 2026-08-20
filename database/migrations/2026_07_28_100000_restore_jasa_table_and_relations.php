<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to ensure `jasa` table and foreign key relations exist.
     */
    public function up(): void
    {
        if (!Schema::hasTable('jasa')) {
            Schema::create('jasa', function (Blueprint $table) {
                $table->id();
                $table->string('nama_jasa');
                $table->text('deskripsi')->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('pesanans', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanans', 'tipe_pesanan')) {
                $table->enum('tipe_pesanan', ['jual_produk', 'refill', 'jasa'])->nullable()->after('tipe');
            }
            if (!Schema::hasColumn('pesanans', 'jasa_id')) {
                $table->foreignId('jasa_id')->nullable()->after('tipe_pesanan')->constrained('jasa')->nullOnDelete();
            } else {
                try {
                    $table->foreign('jasa_id')->references('id')->on('jasa')->nullOnDelete();
                } catch (\Throwable) {
                    // Foreign key already exists
                }
            }
        });

        // Insert default data into `jasa` if empty
        if (DB::table('jasa')->count() === 0) {
            $servicePakets = DB::table('service_pakets')->get();
            if ($servicePakets->isNotEmpty()) {
                foreach ($servicePakets as $paket) {
                    DB::table('jasa')->insert([
                        'id' => $paket->id,
                        'nama_jasa' => $paket->nama ?? $paket->label ?? 'Jasa Service APAR',
                        'deskripsi' => $paket->rincian_layanan ?? 'Layanan perawatan dan pengisian APAR',
                        'harga' => $paket->harga ?? 0,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                DB::table('jasa')->insert([
                    [
                        'nama_jasa' => 'Jasa Isi Ulang / Refill APAR',
                        'deskripsi' => 'Pengisian ulang media pemadam APAR Powder/CO2/Foam sesuai standar K3.',
                        'harga' => 150000.00,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_jasa' => 'Jasa Inspeksi & Service Berkala APAR',
                        'deskripsi' => 'Pengecekan tekanan, kondisi tabung, segel, valve, dan kelayakan APAR.',
                        'harga' => 50000.00,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_jasa' => 'Jasa Perbaikan & Replacement Part APAR',
                        'deskripsi' => 'Perbaikan sparepart tabung APAR seperti pressure gauge, selang, dan nozzle.',
                        'harga' => 75000.00,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            if (Schema::hasColumn('pesanans', 'jasa_id')) {
                $table->dropForeign(['jasa_id']);
                $table->dropColumn('jasa_id');
            }
            if (Schema::hasColumn('pesanans', 'tipe_pesanan')) {
                $table->dropColumn('tipe_pesanan');
            }
        });

        Schema::dropIfExists('jasa');
    }
};
