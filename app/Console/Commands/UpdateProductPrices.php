<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Support\ProductPriceReferenceCatalog;
use Illuminate\Console\Command;

class UpdateProductPrices extends Command
{
    protected $signature = 'update:product-prices
                            {--dry-run : Tampilkan perubahan harga tanpa menyimpan ke database}';

    protected $description = 'Perbarui harga produk APAR dari referensi listing e-commerce tanpa mengubah stok atau data produk lain';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $modeLabel = $dryRun ? 'DRY RUN' : 'UPDATE';

        $this->info("=== {$modeLabel} HARGA PRODUK APAR ===");
        $this->line('Referensi harga diverifikasi dari listing e-commerce pada 2026-06-19.');

        $produks = Produk::with('jenisApar')
            ->orderBy('merek')
            ->orderBy('jenis_apar_id')
            ->orderBy('kapasitas')
            ->orderBy('id')
            ->get();

        if ($produks->isEmpty()) {
            $this->warn('Tidak ada produk di database.');

            return self::SUCCESS;
        }

        $updatedRows = [];
        $skippedRows = [];
        $unchangedCount = 0;

        foreach ($produks as $produk) {
            $reference = ProductPriceReferenceCatalog::findMatch($produk);

            if (! $reference) {
                $skippedRows[] = [
                    $produk->id,
                    $this->productLabel($produk),
                    'Tidak ada referensi e-commerce yang cocok berdasarkan merek, jenis, dan ukuran.',
                ];

                $this->line("<fg=yellow>[SKIP]</> #{$produk->id} {$this->productLabel($produk)}");
                continue;
            }

            $oldPrice = (int) round((float) ($produk->harga ?? 0));
            $newPrice = (int) $reference['price'];
            $note = (string) ($reference['note'] ?? '');

            if ($oldPrice === $newPrice) {
                $unchangedCount++;
                $this->line("<fg=gray>[OK]</> #{$produk->id} {$this->productLabel($produk)} tetap {$this->formatRupiah($newPrice)}");
                continue;
            }

            if (! $dryRun) {
                $produk->harga = $newPrice;
                $produk->save();
            }

            $updatedRows[] = [
                $produk->id,
                $this->productLabel($produk),
                $this->formatRupiah($oldPrice),
                $this->formatRupiah($newPrice),
                ProductPriceReferenceCatalog::formatSource($reference),
                $note !== '' ? $note : '-',
            ];

            $tag = $dryRun ? 'PLAN' : 'UPDATE';
            $this->line("<fg=green>[{$tag}]</> #{$produk->id} {$this->productLabel($produk)} | {$this->formatRupiah($oldPrice)} -> {$this->formatRupiah($newPrice)}");
        }

        $this->newLine();
        $this->info('Ringkasan:');
        $this->line('  Total produk dicek  : ' . $produks->count());
        $this->line('  Produk diperbarui   : ' . count($updatedRows));
        $this->line('  Produk tetap sama   : ' . $unchangedCount);
        $this->line('  Produk dilewati     : ' . count($skippedRows));

        if (! empty($updatedRows)) {
            $this->newLine();
            $this->info($dryRun ? 'Daftar produk yang akan diupdate:' : 'Daftar produk yang berhasil diupdate:');
            $this->table(
                ['ID', 'Produk', 'Harga Lama', 'Harga Baru', 'Referensi', 'Catatan'],
                $updatedRows
            );
        }

        if (! empty($skippedRows)) {
            $this->newLine();
            $this->warn('Daftar produk yang dilewati:');
            $this->table(
                ['ID', 'Produk', 'Alasan'],
                $skippedRows
            );
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('Dry run selesai. Jalankan ulang tanpa --dry-run untuk menyimpan perubahan harga.');
        }

        return self::SUCCESS;
    }

    private function productLabel(Produk $produk): string
    {
        $jenis = $produk->jenisApar?->nama ?: 'APAR';
        $kapasitas = $produk->kapasitas ?: '-';

        return trim("{$produk->nama} | {$produk->merek} | {$jenis} | {$kapasitas}");
    }

    private function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
