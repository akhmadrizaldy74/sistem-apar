<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanans', 'tipe_pesanan')) {
                $table->enum('tipe_pesanan', ['jual_produk', 'refill', 'jasa'])->nullable()->after('tipe');
            }
            if (!Schema::hasColumn('pesanans', 'jasa_id')) {
                $table->foreignId('jasa_id')->nullable()->after('tipe_pesanan')->constrained('jasa')->nullOnDelete();
            }
            if (!Schema::hasColumn('pesanans', 'unit_apar_id')) {
                $table->foreignId('unit_apar_id')->nullable()->after('jasa_id')->constrained('unit_apars')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            if (Schema::hasColumn('pesanans', 'unit_apar_id')) {
                $table->dropForeign(['unit_apar_id']);
                $table->dropColumn('unit_apar_id');
            }
            if (Schema::hasColumn('pesanans', 'jasa_id')) {
                $table->dropForeign(['jasa_id']);
                $table->dropColumn('jasa_id');
            }
            if (Schema::hasColumn('pesanans', 'tipe_pesanan')) {
                $table->dropColumn('tipe_pesanan');
            }
        });
    }
};
