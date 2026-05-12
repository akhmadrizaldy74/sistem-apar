<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs') || !Schema::hasColumn('pesanans', 'harga_penawaran_pelanggan')) {
            return;
        }

        DB::table('pesanans')
            ->select(['id'])
            ->where('is_nego', true)
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    $candidate = null;

                    $logs = DB::table('activity_logs')
                        ->select(['properties'])
                        ->where('subject_type', 'App\\Models\\Pesanan')
                        ->where('subject_id', $order->id)
                        ->orderBy('created_at')
                        ->orderBy('id')
                        ->get();

                    foreach ($logs as $log) {
                        $rawProperties = $log->properties ?? [];
                        if (is_array($rawProperties)) {
                            $properties = $rawProperties;
                        } elseif (is_object($rawProperties)) {
                            $properties = (array) $rawProperties;
                        } else {
                            $properties = json_decode((string) $rawProperties, true) ?? [];
                        }

                        foreach ([
                            $properties['attributes']['harga_usulan'] ?? null,
                            $properties['changes']['harga_usulan']['old'] ?? null,
                        ] as $value) {
                            if (is_numeric($value) && (float) $value > 0) {
                                $candidate = (float) $value;
                                break 2;
                            }
                        }
                    }

                    if ($candidate === null) {
                        continue;
                    }

                    DB::table('pesanans')
                        ->where('id', $order->id)
                        ->update([
                            'harga_penawaran_pelanggan' => $candidate,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // noop
    }
};
