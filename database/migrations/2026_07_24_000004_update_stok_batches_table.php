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
        Schema::table('stok_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('stok_batches', 'sumber')) {
                $table->enum('sumber', ['manual', 'purchase_order'])->default('manual')->after('keterangan');
            }
            if (!Schema::hasColumn('stok_batches', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete()->after('sumber');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_batches', function (Blueprint $table) {
            if (Schema::hasColumn('stok_batches', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
            if (Schema::hasColumn('stok_batches', 'sumber')) {
                $table->dropColumn('sumber');
            }
        });
    }
};
