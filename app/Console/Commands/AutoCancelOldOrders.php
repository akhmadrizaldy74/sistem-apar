<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use Illuminate\Console\Command;

class AutoCancelOldOrders extends Command
{
    protected $signature = 'pesanan:auto-cancel {--hours=48}';
    protected $description = 'Batalkan pesanan yang belum dibayar setelah melewati batas waktu tertentu.';

    public function handle(): int
    {
        $hoursThreshold = (int) $this->option('hours');

        $orders = Pesanan::whereIn('status', ['pending', 'menunggu'])
            ->whereDate('created_at', '<=', now()->subHours($hoursThreshold))
            ->get();

        $cancelled = 0;

        foreach ($orders as $order) {
            $order->update([
                'status' => 'dibatalkan',
                'keterangan' => "Auto-cancelled oleh sistem setelah {$hoursThreshold} jam tidak ada pembayaran.",
            ]);
            $cancelled++;
        }

        $this->info("Auto-cancel selesai. {$cancelled} pesanan dibatalkan.");
        return Command::SUCCESS;
    }
}