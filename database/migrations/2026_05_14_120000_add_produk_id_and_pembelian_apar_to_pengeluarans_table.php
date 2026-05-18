<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->foreignId('produk_id')
                ->nullable()
                ->after('jenis_pengeluaran')
                ->constrained('produks')
                ->nullOnDelete();
        });

        DB::statement("
            ALTER TABLE `pengeluarans`
            MODIFY `jenis_pengeluaran` ENUM(
                'pembelian_apar',
                'pembelian_refill',
                'pembelian_peralatan'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `pengeluarans`
            MODIFY `jenis_pengeluaran` ENUM(
                'pembelian_refill',
                'pembelian_peralatan'
            ) NULL
        ");

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_id');
        });
    }
};
