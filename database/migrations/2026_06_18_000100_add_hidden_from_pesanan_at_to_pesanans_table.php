<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pesanans') || Schema::hasColumn('pesanans', 'hidden_from_pesanan_at')) {
            return;
        }

        Schema::table('pesanans', function (Blueprint $table) {
            $table->timestamp('hidden_from_pesanan_at')->nullable()->after('stok_dikurangi');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pesanans') || !Schema::hasColumn('pesanans', 'hidden_from_pesanan_at')) {
            return;
        }

        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('hidden_from_pesanan_at');
        });
    }
};
