<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unit_apars') || Schema::hasColumn('unit_apars', 'hidden_at')) {
            return;
        }

        Schema::table('unit_apars', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable()->after('tgl_expired');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('unit_apars') || ! Schema::hasColumn('unit_apars', 'hidden_at')) {
            return;
        }

        Schema::table('unit_apars', function (Blueprint $table) {
            $table->dropColumn('hidden_at');
        });
    }
};
