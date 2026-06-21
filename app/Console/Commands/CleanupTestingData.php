<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use App\Models\User;
use App\Services\PaidOrderStockService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanupTestingData extends Command
{
    protected $signature = 'apar:cleanup-testing-data {--dry-run : Tampilkan ringkasan cleanup tanpa menghapus data}';

    protected $description = 'Bersihkan data operasional/testing tanpa menyentuh master data dan kembalikan stok transaksi.';

    public function __construct(private readonly PaidOrderStockService $paidOrderStockService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $snapshotBefore = $this->snapshot();
        $customerUserIds = User::query()
            ->where('role', 'pelanggan')
            ->pluck('id');
        $customerEmails = User::query()
            ->whereIn('id', $customerUserIds)
            ->pluck('email')
            ->filter(fn (?string $email) => filled($email))
            ->values();
        $ordersToRollback = Pesanan::query()
            ->where('stok_dikurangi', true)
            ->orderBy('id')
            ->get();
        $publicFilePaths = $this->collectPublicFilePaths();

        $this->info('Snapshot sebelum cleanup:');
        $this->renderSnapshot($snapshotBefore);
        $this->newLine();
        $this->line('Pesanan dengan stok terpotong: ' . $ordersToRollback->count());
        $this->line('Akun pelanggan yang akan dibersihkan: ' . $customerUserIds->count());
        $this->line('File publik terkait transaksi/testing: ' . $publicFilePaths->count());

        if ($dryRun) {
            $this->warn('Mode dry-run aktif. Tidak ada data yang dihapus.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($ordersToRollback, $customerUserIds, $customerEmails) {
            foreach ($ordersToRollback as $pesanan) {
                $this->paidOrderStockService->rollback($pesanan);
            }

            DB::table('tugas_refills')->delete();
            DB::table('refills')->delete();
            DB::table('complains')->delete();
            DB::table('testimonis')->delete();
            DB::table('services')->delete();
            DB::table('pesanan_details')->delete();
            DB::table('unit_apars')->delete();
            DB::table('pesanans')->delete();
            DB::table('website_visits')->delete();
            DB::table('activity_logs')->delete();
            DB::table('jobs')->delete();
            DB::table('failed_jobs')->delete();
            DB::table('job_batches')->delete();

            if ($customerUserIds->isNotEmpty()) {
                DB::table('sessions')
                    ->whereIn('user_id', $customerUserIds)
                    ->delete();
            }

            if ($customerEmails->isNotEmpty()) {
                DB::table('password_reset_tokens')
                    ->whereIn('email', $customerEmails)
                    ->delete();
            }

            DB::table('pelanggans')->delete();

            if ($customerUserIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $customerUserIds)
                    ->delete();
            }
        });

        $deletedFileCount = $this->deletePublicFiles($publicFilePaths);
        $deletedSessionFileCount = $this->clearFileSessions();
        $snapshotAfter = $this->snapshot();

        $this->newLine();
        $this->info('Snapshot sesudah cleanup:');
        $this->renderSnapshot($snapshotAfter);
        $this->newLine();
        $this->info('Cleanup testing selesai.');
        $this->line('Pesanan yang rollback stoknya: ' . $ordersToRollback->count());
        $this->line('File publik yang dibersihkan: ' . $deletedFileCount);
        $this->line('File session yang dibersihkan: ' . $deletedSessionFileCount);
        $this->line('Akun pelanggan yang dibersihkan: ' . $customerUserIds->count());

        return self::SUCCESS;
    }

    private function snapshot(): array
    {
        return [
            'transaksi' => [
                'users_pelanggan' => DB::table('users')->where('role', 'pelanggan')->count(),
                'pelanggans' => DB::table('pelanggans')->count(),
                'pesanans' => DB::table('pesanans')->count(),
                'pesanan_details' => DB::table('pesanan_details')->count(),
                'unit_apars' => DB::table('unit_apars')->count(),
                'services' => DB::table('services')->count(),
                'refills' => DB::table('refills')->count(),
                'complains' => DB::table('complains')->count(),
                'testimonis' => DB::table('testimonis')->count(),
                'activity_logs' => DB::table('activity_logs')->count(),
                'website_visits' => DB::table('website_visits')->count(),
                'jobs' => DB::table('jobs')->count(),
            ],
            'master' => [
                'users_admin' => DB::table('users')->where('role', 'admin')->count(),
                'users_teknisi' => DB::table('users')->where('role', 'teknisi')->count(),
                'produks' => DB::table('produks')->count(),
                'jenis_apars' => DB::table('jenis_apars')->count(),
                'jenis_refills' => DB::table('jenis_refills')->count(),
                'service_pakets' => DB::table('service_pakets')->count(),
                'peralatans' => DB::table('peralatans')->count(),
                'stok_batches' => DB::table('stok_batches')->count(),
                'pengeluarans' => DB::table('pengeluarans')->count(),
                'stok_produk_total' => (int) DB::table('produks')->sum('stok'),
                'stok_refill_total' => (float) DB::table('jenis_refills')->sum('stok'),
                'stok_peralatan_total' => (int) DB::table('peralatans')->sum('stok'),
            ],
        ];
    }

    private function renderSnapshot(array $snapshot): void
    {
        foreach ($snapshot as $group => $values) {
            $this->line(strtoupper($group));

            foreach ($values as $label => $value) {
                $this->line(sprintf('  - %s: %s', $label, $this->formatValue($value)));
            }
        }
    }

    private function collectPublicFilePaths(): Collection
    {
        $paths = collect([
            ...DB::table('pesanans')->pluck('bukti_pembayaran')->all(),
            ...DB::table('pesanans')->pluck('service_foto')->all(),
            ...DB::table('services')->pluck('laporan_foto')->all(),
            ...DB::table('complains')->pluck('foto_path')->all(),
            ...DB::table('testimonis')->pluck('foto_path')->all(),
            ...DB::table('tugas_refills')->pluck('bukti_foto')->all(),
        ]);

        return $paths
            ->filter(fn ($path) => filled($path))
            ->map(fn (string $path) => $this->normalizeStoragePath($path))
            ->filter(fn (?string $path) => filled($path))
            ->unique()
            ->values();
    }

    private function deletePublicFiles(Collection $paths): int
    {
        if ($paths->isEmpty()) {
            return 0;
        }

        $disk = Storage::disk('public');
        $existingPaths = $paths
            ->filter(fn (string $path) => $disk->exists($path))
            ->values();

        if ($existingPaths->isNotEmpty()) {
            $disk->delete($existingPaths->all());
        }

        return $existingPaths->count();
    }

    private function clearFileSessions(): int
    {
        $sessionPath = config('session.files');
        if (! is_string($sessionPath) || $sessionPath === '' || ! File::isDirectory($sessionPath)) {
            return 0;
        }

        $sessionFiles = collect(File::files($sessionPath))
            ->filter(fn ($file) => $file->getFilename() !== '.gitignore')
            ->values();

        foreach ($sessionFiles as $file) {
            File::delete($file->getRealPath());
        }

        return $sessionFiles->count();
    }

    private function normalizeStoragePath(string $path): ?string
    {
        $normalized = trim($path);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $normalized);
        $normalized = preg_replace('#^/?storage/#', '', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        return $normalized !== '' ? $normalized : null;
    }

    private function formatValue(mixed $value): string
    {
        if (is_float($value)) {
            return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
        }

        return (string) $value;
    }
}
