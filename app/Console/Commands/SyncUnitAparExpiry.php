<?php

namespace App\Console\Commands;

use App\Models\UnitApar;
use App\Services\UnitExpiryService;
use Illuminate\Console\Command;

class SyncUnitAparExpiry extends Command
{
    protected $signature = 'apar:sync-unit-expiry {--dry-run : Tampilkan unit yang mismatch tanpa menyimpan perubahan}';
    protected $description = 'Sinkronkan ulang masa berlaku Unit APAR berdasarkan tanggal dasar yang benar dan aturan ukuran.';

    public function __construct(private readonly UnitExpiryService $unitExpiryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;
        $updated = 0;
        $skipped = 0;

        UnitApar::query()
            ->with([
                'produk.jenisApar',
                'services' => fn ($query) => $query
                    ->select('id', 'unit_apar_id', 'jenis_service', 'tgl_service', 'status_konfirmasi')
                    ->orderByDesc('tgl_service')
                    ->orderByDesc('id'),
            ])
            ->orderBy('id')
            ->chunkById(100, function ($units) use ($dryRun, &$processed, &$updated, &$skipped) {
                foreach ($units as $unitApar) {
                    $processed++;

                    $expectedExpiry = $this->unitExpiryService->expectedExpiry($unitApar);
                    if (! $expectedExpiry) {
                        $skipped++;
                        continue;
                    }

                    $currentExpiry = optional($unitApar->tgl_expired)->toDateString();
                    $expectedDate = $expectedExpiry->toDateString();

                    if ($currentExpiry === $expectedDate) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line(sprintf(
                            '[DRY RUN] Unit #%d %s: %s -> %s',
                            $unitApar->id,
                            $unitApar->no_seri ?: '-',
                            $currentExpiry ?: '-',
                            $expectedDate,
                        ));
                    } else {
                        $unitApar->forceFill([
                            'tgl_expired' => $expectedDate,
                        ])->save();
                    }

                    $updated++;
                }
            });

        $summary = sprintf(
            'Sinkronisasi expired selesai. Diproses: %d, diperbarui: %d, dilewati tanpa tanggal dasar: %d%s',
            $processed,
            $updated,
            $skipped,
            $dryRun ? ' [DRY RUN]' : '',
        );

        $this->info($summary);

        return self::SUCCESS;
    }
}
