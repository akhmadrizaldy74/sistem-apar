<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('merek')->default('FIREFIX');
            $table->decimal('harga', 15, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->foreignId('jenis_apar_id')->constrained('jenis_apars')->cascadeOnDelete();
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(5);
            $table->string('kapasitas')->nullable();
            $table->text('penggunaan')->nullable();
            $table->timestamps();
        });

        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('no_pesanan')->nullable();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_penerima')->nullable();
            $table->string('nomor_wa_penerima')->nullable();
            $table->text('alamat_pengiriman')->nullable();
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('teknisi_selesai_at')->nullable();
            $table->text('teknisi_catatan')->nullable();
            $table->string('tipe')->default('produk');
            $table->enum('sumber_pesanan', ['website', 'whatsapp', 'telepon', 'datang_langsung', 'input_admin', 'data_lama'])->default('input_admin');
            $table->boolean('is_pesanan_lama')->default(false);
            $table->enum('service_jenis_layanan', ['service', 'refill'])->nullable();
            $table->foreignId('service_paket_id')->nullable()->constrained('service_pakets')->nullOnDelete();
            $table->foreignId('service_jenis_refill_id')->nullable()->constrained('jenis_refills')->nullOnDelete();
            $table->string('service_jenis_apar', 120)->nullable();
            $table->string('service_ukuran_apar', 120)->nullable();
            $table->unsignedInteger('service_jumlah_unit')->nullable();
            $table->decimal('service_total_kg', 12, 2)->nullable();
            $table->text('service_keluhan')->nullable();
            $table->string('service_foto')->nullable();
            $table->enum('service_metode_penanganan', ['dijemput', 'antar sendiri', 'survey lokasi'])->nullable();
            $table->decimal('service_estimasi_biaya', 15, 2)->nullable();
            $table->text('service_admin_catatan')->nullable();
            $table->unsignedBigInteger('total');
            $table->decimal('total_harga', 15, 2)->nullable();
            $table->enum('tipe_harga', ['deal', 'normal'])->default('normal');
            $table->enum('metode_pembayaran', ['transfer', 'cash'])->nullable();
            $table->string('bank', 50)->nullable();
            $table->enum('metode_pengiriman', ['pickup', 'diantar_internal'])->default('pickup');
            $table->decimal('ongkir', 15, 2)->default(0);
            $table->decimal('shipping_distance_km', 10, 2)->nullable();
            $table->string('alamat_maps')->nullable();
            $table->text('alamat_detail')->nullable();
            $table->decimal('alamat_lat', 11, 8)->nullable();
            $table->decimal('alamat_lng', 11, 8)->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamp('link_pembayaran_terkirim_at')->nullable();
            $table->timestamp('pembayaran_terkonfirmasi_at')->nullable();
            $table->decimal('harga_usulan', 15, 2)->nullable();
            $table->decimal('harga_penawaran_pelanggan', 15, 2)->nullable();
            $table->string('kode_nego', 20)->nullable();
            $table->timestamp('kode_nego_terpakai_at')->nullable();
            $table->boolean('is_nego')->default(false);
            $table->text('keterangan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->enum('status', [
                'menunggu',
                'menunggu persetujuan',
                'pending',
                'diproses',
                'selesai',
                'ditolak',
                'menunggu diproses admin',
                'ditugaskan ke teknisi',
                'dikerjakan teknisi',
                'selesai oleh teknisi',
                'dikonfirmasi admin',
                'selesai final',
                'permintaan masuk',
                'direview admin',
                'menunggu penjadwalan',
                'menunggu persetujuan biaya',
                'disetujui',
                'menunggu pengambilan',
                'menunggu kedatangan unit',
            ])->default('menunggu');
            $table->boolean('stok_dikurangi')->default(false);
            $table->date('tanggal');
            $table->json('invoice_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('unit_apars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanans')->nullOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->string('no_seri')->unique();
            $table->string('lokasi_unit')->nullable();
            $table->date('tgl_beli')->nullable();
            $table->date('tgl_produksi');
            $table->string('ukuran');
            $table->string('bahan');
            $table->enum('kondisi_awal', ['layak', 'perlu_servis', 'tidak_aktif'])->default('layak');
            $table->text('catatan_unit')->nullable();
            $table->date('tgl_expired');
            $table->timestamps();
        });

        Schema::create('pesanan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produks')->cascadeOnDelete();
            $table->string('merek');
            $table->string('kapasitas');
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->foreign('produk_id')->references('id')->on('produks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
        });

        Schema::dropIfExists('pesanan_details');
        Schema::dropIfExists('unit_apars');
        Schema::dropIfExists('pesanans');
        Schema::dropIfExists('produks');
    }
};
