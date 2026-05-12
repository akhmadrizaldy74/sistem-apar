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
            $table->decimal('stok', 12, 2)->default(0)->change();
            $table->decimal('stok_minimum', 12, 2)->default(5)->change();
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->enum('jenis_pengeluaran', ['pembelian_refill', 'pembelian_peralatan'])->nullable()->after('kategori');
            $table->foreignId('jenis_refill_id')->nullable()->after('jenis_pengeluaran')->constrained('jenis_refills')->nullOnDelete();
            $table->foreignId('peralatan_id')->nullable()->after('jenis_refill_id')->constrained('peralatans')->nullOnDelete();
            $table->string('nama_item')->nullable()->after('peralatan_id');
            $table->decimal('qty', 12, 2)->nullable()->after('nama_item');
            $table->string('satuan', 30)->nullable()->after('qty');
            $table->decimal('harga_beli', 15, 2)->nullable()->after('satuan');
            $table->decimal('total', 15, 2)->nullable()->after('harga_beli');
        });

        DB::table('pengeluarans')
            ->where('kategori', 'refill')
            ->update(['jenis_pengeluaran' => 'pembelian_refill']);

        DB::table('pengeluarans')
            ->where('kategori', 'peralatan')
            ->update(['jenis_pengeluaran' => 'pembelian_peralatan']);

        DB::table('pengeluarans')
            ->whereNull('total')
            ->update(['total' => DB::raw('nominal')]);

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('item_type', ['produk', 'refill', 'peralatan']);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_nama');
            $table->string('satuan', 30)->default('unit');
            $table->enum('movement_type', ['masuk', 'keluar']);
            $table->decimal('qty', 12, 2);
            $table->decimal('stok_sebelum', 12, 2);
            $table->decimal('stok_sesudah', 12, 2);
            $table->string('source_type', 60);
            $table->nullableMorphs('reference');
            $table->text('keterangan')->nullable();
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
            $table->index(['source_type', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('peralatan_id');
            $table->dropConstrainedForeignId('jenis_refill_id');
            $table->dropColumn([
                'jenis_pengeluaran',
                'nama_item',
                'qty',
                'satuan',
                'harga_beli',
                'total',
            ]);
        });

        Schema::table('jenis_refills', function (Blueprint $table) {
            $table->unsignedInteger('stok')->default(0)->change();
            $table->unsignedInteger('stok_minimum')->default(5)->change();
        });
    }
};
