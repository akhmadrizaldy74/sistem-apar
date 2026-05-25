<?php

namespace App\Console\Commands;

use App\Models\Produk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CleanInvalidProducts extends Command
{
    protected $signature = 'apar:clean-invalid-products {--dry : Hanya tampilkan audit tanpa melakukan penghapusan/penonaktifan}';

    protected $description = 'Audit dan bersihkan produk APAR yang tidak memiliki file gambar valid dari database / katalog aktif';

    public function handle(): int
    {
        $dryRun = $this->option('dry');
        $disk = Storage::disk('public');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║    APAR Invalid Product Cleaner — Anugrah Utama     ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');

        if ($dryRun) {
            $this->warn('🧪 Mode --dry aktif: data hanya diaudit, database TIDAK diubah.');
            $this->info('');
        }

        // Cek link storage
        $publicStoragePath = public_path('storage');
        if (!file_exists($publicStoragePath) && !is_link($publicStoragePath)) {
            $this->error('Storage link belum dibuat. Silakan jalankan: php artisan storage:link');
            return self::FAILURE;
        }

        $produks = Produk::with(['pesananDetails', 'units', 'stokBatches'])->get();

        if ($produks->isEmpty()) {
            $this->warn('Tidak ada data produk di database.');
            return self::SUCCESS;
        }

        $totalChecked = $produks->count();
        $totalValid = 0;
        $totalInvalid = 0;
        $totalHardDeleted = 0;
        $totalDisabled = 0;

        $this->info("📦 Men-scan {$totalChecked} produk di database...");
        $this->info('');

        $invalidList = [];

        foreach ($produks as $produk) {
            $imagePath = $produk->gambar;
            $isValidImage = !empty($imagePath) && $disk->exists($imagePath);

            if ($isValidImage) {
                $totalValid++;
                $this->line("  <fg=green>[VALID]</> ID: {$produk->id} | {$produk->nama} | Merek: {$produk->merek} | Gambar: {$imagePath}");
            } else {
                $totalInvalid++;
                
                // Cari relasi transaksi
                $hasOrder = $produk->pesananDetails()->exists();
                $hasUnit = $produk->units()->exists();
                $hasBatch = $produk->stokBatches()->exists();
                $hasTugas = DB::table('tugas_refills')->where('produk_id', $produk->id)->exists();

                $hasHistory = $hasOrder || $hasUnit || $hasBatch || $hasTugas;

                $reason = [];
                if ($hasOrder) $reason[] = 'Detail Pesanan';
                if ($hasUnit) $reason[] = 'Unit Terdaftar';
                if ($hasBatch) $reason[] = 'Stok Batch';
                if ($hasTugas) $reason[] = 'Tugas Refill';

                $reasonStr = !empty($reason) ? ' (Dipakai di: ' . implode(', ', $reason) . ')' : '';

                if ($hasHistory) {
                    $totalDisabled++;
                    $actionLabel = $dryRun ? 'AKAN DINONAKTIFKAN' : 'DINONAKTIFKAN';
                    $this->line("  <fg=yellow>[INVALID - {$actionLabel}]</> ID: {$produk->id} | {$produk->nama}{$reasonStr}");
                    
                    if (!$dryRun) {
                        $produk->gambar = null;
                        $produk->save();
                    }
                } else {
                    $totalHardDeleted++;
                    $actionLabel = $dryRun ? 'AKAN DIHAPUS PERMANEN' : 'DIHAPUS PERMANEN';
                    $this->line("  <fg=red>[INVALID - {$actionLabel}]</> ID: {$produk->id} | {$produk->nama} (Tidak ada relasi)");
                    
                    if (!$dryRun) {
                        $produk->delete();
                    }
                }

                $invalidList[] = [
                    'produk' => $produk->nama,
                    'has_history' => $hasHistory,
                    'reasons' => $reason
                ];
            }
        }

        // Output summary
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  📊 Ringkasan Hasil Clean-Up APAR');
        $this->info('══════════════════════════════════════════════════');
        $this->info("  Total Produk Dicek         : {$totalChecked}");
        $this->info("  ✅ Produk Valid (Ada Gambar) : {$totalValid}");
        $this->info("  ❌ Produk Invalid           : {$totalInvalid}");
        $this->info("  🗑️  Dihapus Permanen (DB)    : {$totalHardDeleted}");
        $this->info("  🔒 Dinonaktifkan (Katalog)  : {$totalDisabled}");
        $this->info('══════════════════════════════════════════════════');
        $this->info('');

        if (!$dryRun) {
            $this->info('🎉 Proses pembersihan berhasil diselesaikan dengan aman!');
        } else {
            $this->warn('🧪 Audit selesai. Tidak ada database yang diubah.');
        }

        $this->info('');

        return self::SUCCESS;
    }
}
