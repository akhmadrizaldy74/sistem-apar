<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->decimal('harga_penawaran_pelanggan', 15, 2)
                ->nullable()
                ->after('harga_usulan');
        });

        DB::table('pesanans')
            ->select(['id', 'harga_usulan', 'harga_penawaran_pelanggan', 'keterangan', 'is_nego'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    if (!$row->is_nego || $row->harga_penawaran_pelanggan !== null) {
                        continue;
                    }

                    $customerOffer = $row->harga_usulan;

                    if (!empty($row->keterangan) && preg_match('/Harga\s+Usulan:\s*Rp\s*([0-9\.\,]+)/i', (string) $row->keterangan, $matches)) {
                        $digits = preg_replace('/[^\d]/', '', (string) ($matches[1] ?? ''));
                        if ($digits !== '') {
                            $customerOffer = (float) $digits;
                        }
                    }

                    DB::table('pesanans')
                        ->where('id', $row->id)
                        ->update([
                            'harga_penawaran_pelanggan' => $customerOffer,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('harga_penawaran_pelanggan');
        });
    }
};
