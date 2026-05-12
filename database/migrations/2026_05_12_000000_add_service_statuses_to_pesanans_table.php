<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = 'pesanans';

        // Modify enum to include 'menunggu pengambilan' and 'menunggu kedatangan unit'
        DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM(
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
            'menunggu kedatangan unit'
        )");
    }

    public function down(): void
    {
        $tableName = 'pesanans';

        // Rollback to original enum without the new statuses
        DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM(
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
            'disetujui'
        )");
    }
};