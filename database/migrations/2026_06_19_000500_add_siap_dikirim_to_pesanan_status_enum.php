<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @return list<string>
     */
    private function statuses(bool $includeReadyToShip = true): array
    {
        $statuses = [
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
        ];

        if ($includeReadyToShip) {
            $statuses[] = 'siap dikirim';
        }

        return $statuses;
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $allowedStatuses = implode("','", $this->statuses());

        DB::statement(
            "ALTER TABLE pesanans MODIFY status ENUM('{$allowedStatuses}') NOT NULL DEFAULT 'menunggu'"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('pesanans')
            ->where('status', 'siap dikirim')
            ->update(['status' => 'dikonfirmasi admin']);

        $allowedStatuses = implode("','", $this->statuses(false));

        DB::statement(
            "ALTER TABLE pesanans MODIFY status ENUM('{$allowedStatuses}') NOT NULL DEFAULT 'menunggu'"
        );
    }
};
