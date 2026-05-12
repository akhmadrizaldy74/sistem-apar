<?php

namespace App\Console\Commands;

use App\Models\UnitApar;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class NotifyExpiredApar extends Command
{
    protected $signature = 'apar:notify-expired {--days=7}';
    protected $description = 'Kirim notifikasi WhatsApp ke pelanggan yang APAR-nya expired atau akan expired.';

    public function handle(): int
    {
        $daysThreshold = (int) $this->option('days');
        $today = now()->toDateString();
        $thresholdDate = now()->addDays($daysThreshold)->toDateString();

        $units = UnitApar::with('pelanggan')
            ->where('tgl_expired', '<=', $thresholdDate)
            ->where('tgl_expired', '>=', $today)
            ->get();

        $alreadyNotifiedKey = 'notified_expired_';
        $notified = 0;

        foreach ($units as $unit) {
            if (!$unit->pelanggan || !$unit->pelanggan->no_wa) continue;

            $key = $alreadyNotifiedKey . $unit->id . '_' . now()->format('Ymd');
            if (cache()->has($key)) continue;

            $isExpired = $unit->tgl_expired->lt(now());

            $msg = $isExpired
                ? "Halo {$unit->pelanggan->nama}, APAR Anda (No. Seri: {$unit->no_seri}) *sudah expired* pada {$unit->tgl_expired->format('d M Y')}. Segera hubungi kami untuk service atau refill ulang. Terima kasih."
                : "Halo {$unit->pelanggan->nama}, APAR Anda (No. Seri: {$unit->no_seri}) akan *expired* pada {$unit->tgl_expired->format('d M Y')}. Segera lakukan perpanjangan agar selalu aman. Hubungi kami untuk info lebih lanjut.";

            $phone = preg_replace('/\D+/', '', $unit->pelanggan->no_wa);
            if (!str_starts_with($phone, '62')) {
                $phone = '62' . ltrim($phone, '0');
            }

            $waNumber = config('app.whatsapp_contact', env('WHATSAPP_CONTACT', '6285128008030'));

            try {
                Http::timeout(10)->get(
                    "https://wa.me/{$waNumber}?text=" . urlencode($msg)
                );
                cache()->put($key, true, now()->addDays($daysThreshold));
                $notified++;
            } catch (\Exception $e) {
                $this->warn("Gagal kirim notifikasi untuk unit {$unit->id}: " . $e->getMessage());
            }
        }

        $this->info("Notifikasi expired APAR selesai. {$notified} pesan terkirim.");
        return Command::SUCCESS;
    }
}