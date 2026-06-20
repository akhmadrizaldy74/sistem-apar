<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pesanans')) {
            return;
        }

        Schema::table('pesanans', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanans', 'harga_normal')) {
                $table->double('harga_normal')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'harga_setelah_diskon')) {
                $table->double('harga_setelah_diskon')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'total_awal')) {
                $table->double('total_awal')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'harga_final_admin')) {
                $table->double('harga_final_admin')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'status_persetujuan_harga')) {
                $table->string('status_persetujuan_harga', 40)->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable();
            }

            if (!Schema::hasColumn('pesanans', 'is_pengajuan_harga')) {
                $table->boolean('is_pengajuan_harga')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pesanans')) {
            return;
        }

        Schema::table('pesanans', function (Blueprint $table) {
            foreach ([
                'harga_normal',
                'harga_setelah_diskon',
                'total_awal',
                'harga_final_admin',
                'status_persetujuan_harga',
                'approved_at',
                'approved_by',
                'rejected_at',
                'rejected_by',
                'is_pengajuan_harga',
            ] as $column) {
                if (Schema::hasColumn('pesanans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
