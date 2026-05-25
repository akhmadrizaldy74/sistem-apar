<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('perusahaan')->nullable();
            $table->string('no_wa');
            $table->text('alamat')->nullable();
            $table->string('alamat_maps')->nullable();
            $table->text('alamat_detail')->nullable();
            $table->decimal('alamat_lat', 11, 8)->nullable();
            $table->decimal('alamat_lng', 11, 8)->nullable();
            $table->string('alamat_provinsi', 100)->nullable();
            $table->string('alamat_kota', 100)->nullable();
            $table->string('alamat_kecamatan', 100)->nullable();
            $table->string('alamat_kode_pos', 10)->nullable();
            $table->enum('status', ['calon', 'tetap'])->default('calon');
            $table->enum('sumber_data', ['manual', 'whatsapp', 'telepon', 'arsip_lama'])->default('manual');
            $table->enum('kategori_pelanggan', ['lama', 'baru_manual'])->default('lama');
            $table->text('catatan_internal')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_apars', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('jenis_refills', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->decimal('stok', 12, 2)->default(0);
            $table->string('satuan')->default('kg');
            $table->unsignedBigInteger('harga')->default(0);
            $table->text('service_price_rules_json')->nullable();
            $table->decimal('stok_minimum', 12, 2)->default(5);
            $table->timestamps();
        });

        Schema::create('peralatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(3);
            $table->decimal('harga_standar', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('service_pakets', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('label');
            $table->decimal('harga', 15, 2);
            $table->foreignId('jenis_refill_id')->nullable()->constrained('jenis_refills')->nullOnDelete();
            $table->decimal('refill_ratio', 5, 2)->default(0);
            $table->text('rincian_layanan')->nullable();
            $table->timestamps();
        });

        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['refill', 'peralatan', 'lainnya'])->default('lainnya');
            $table->enum('jenis_pengeluaran', ['pembelian_apar', 'pembelian_refill', 'pembelian_peralatan'])->nullable();
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->foreignId('jenis_refill_id')->nullable()->constrained('jenis_refills')->nullOnDelete();
            $table->foreignId('peralatan_id')->nullable()->constrained('peralatans')->nullOnDelete();
            $table->string('nama_item')->nullable();
            $table->decimal('qty', 12, 2)->nullable();
            $table->string('satuan', 30)->nullable();
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluarans');
        Schema::dropIfExists('service_pakets');
        Schema::dropIfExists('peralatans');
        Schema::dropIfExists('jenis_refills');
        Schema::dropIfExists('jenis_apars');
        Schema::dropIfExists('pelanggans');
    }
};
