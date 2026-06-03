<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->integer('jumlah_masuk');
            $table->integer('sisa_qty');
            $table->date('tgl_produksi');
            $table->date('tgl_expired');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_paket_id')->nullable()->constrained('service_pakets')->nullOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanans')->nullOnDelete();
            $table->foreignId('unit_apar_id')->nullable()->constrained('unit_apars')->cascadeOnDelete();
            $table->string('jenis_service')->nullable();
            $table->text('rincian_layanan')->nullable();
            $table->text('estimasi_peralatan_json')->nullable();
            $table->text('actual_peralatan_json')->nullable();
            $table->date('tgl_service');
            $table->timestamp('tgl_selesai_admin')->nullable();
            $table->string('status_konfirmasi')->nullable();
            $table->text('stok_kurang_history_json')->nullable();
            $table->text('keterangan')->nullable();
            $table->text('catatan_teknisi')->nullable();
            $table->string('laporan_foto')->nullable();
            $table->decimal('biaya', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('refills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete();
            $table->foreignId('unit_apar_id')->constrained('unit_apars')->cascadeOnDelete();
            $table->foreignId('jenis_refill_id')->constrained('jenis_refills')->cascadeOnDelete();
            $table->date('tgl_refill');
            $table->decimal('biaya', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('service_paket_peralatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_paket_id')->constrained('service_pakets')->cascadeOnDelete();
            $table->foreignId('peralatan_id')->constrained('peralatans')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_estimasi')->default(1);
            $table->timestamps();
        });

        Schema::create('tugas_refills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_batch_id')->constrained('stok_batches')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('jumlah_refill');
            $table->date('tanggal_refill')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->text('catatan_teknisi')->nullable();
            $table->string('bukti_foto')->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
            $table->timestamps();
        });

        Schema::create('complains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanans')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('isi_complain');
            $table->string('foto_path')->nullable();
            $table->enum('status_penyelesaian', ['menunggu', 'diproses', 'selesai'])->default('menunggu');
            $table->date('tanggal');
            $table->timestamps();
        });

        Schema::create('testimonis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('review');
            $table->string('foto_path')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->date('tanggal');
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('log_name')->default('default');
            $table->string('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['log_name', 'subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('testimonis');
        Schema::dropIfExists('complains');
        Schema::dropIfExists('tugas_refills');
        Schema::dropIfExists('service_paket_peralatan');
        Schema::dropIfExists('refills');
        Schema::dropIfExists('services');
        Schema::dropIfExists('stok_batches');
    }
};
