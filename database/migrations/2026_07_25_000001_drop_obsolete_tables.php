<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to drop obsolete and redundant tables.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('pengeluarans');
        Schema::dropIfExists('pelanggangs');
        Schema::dropIfExists('refills');
        Schema::dropIfExists('tugas_refills');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obsolete tables do not need to be restored.
    }
};
