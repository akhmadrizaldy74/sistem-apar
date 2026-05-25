<?php

namespace App\Console\Commands;

use App\Models\Produk;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncAparImages extends Command
{
    protected $signature = 'apar:sync-images {--force : Timpa gambar produk yang sudah ada}';

    protected $description = 'Sinkronisasi gambar produk APAR secara otomatis dari folder storage/app/public/apar';

    /**
     * Mapping merek produk => nama folder di storage.
     * Key = normalisasi (lowercase, tanpa spasi), Value = nama folder asli.
     */
    private array $merekFolderMap = [
        'firefix'  => 'FIREFIX',
        'fire fix' => 'FIREFIX',
        'guardall' => 'GuardALL',
        'guard all' => 'GuardALL',
        'guard'    => 'GuardALL',
        'tonata'   => 'TONATA',
    ];

    /**
     * Mapping jenis APAR => nama folder kategori.
     * Key = normalisasi (lowercase), Value = nama folder asli.
     */
    private array $jenisFolderMap = [
        'dry chemical powder' => 'POWDER',
        'powder'              => 'POWDER',
        'dcp'                 => 'POWDER',
        'dry powder'          => 'POWDER',
        'serbuk kimia'        => 'POWDER',
        'co2'                 => 'CO2',
        'co₂'                 => 'CO2',
        'carbon dioxide'      => 'CO2',
        'karbondioksida'      => 'CO2',
        'foam'                => 'FOAM',
        'busa'                => 'FOAM',
        'afff'                => 'FOAM',
    ];

    private array $supportedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    private Filesystem $disk;

    public function handle(): int
    {
        $force = $this->option('force');
        $this->disk = Storage::disk('public');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║       APAR Image Sync — PD. Anugrah Utama       ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        if ($force) {
            $this->warn('⚠  Mode --force aktif: gambar yang sudah ada akan ditimpa.');
            $this->info('');
        }

        // Check storage link
        $publicStoragePath = public_path('storage');
        if (!file_exists($publicStoragePath) && !is_link($publicStoragePath)) {
            $this->error('Storage link belum dibuat. Jalankan: php artisan storage:link');
            return self::FAILURE;
        }

        // Check base folder
        $basePath = 'apar';
        if (!$this->disk->exists($basePath)) {
            $this->error('Folder gambar tidak ditemukan: storage/app/public/apar');
            $this->error('Pastikan folder gambar sudah disimpan di lokasi yang benar.');
            return self::FAILURE;
        }

        // Build available image index
        $imageIndex = $this->buildImageIndex($basePath);

        // Load all products with jenis apar relation
        $produks = Produk::with('jenisApar')->get();

        if ($produks->isEmpty()) {
            $this->warn('Tidak ada produk di database.');
            return self::SUCCESS;
        }

        $totalChecked = $produks->count();
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalNotFound = 0;
        $notFoundList = [];

        $this->info("📦 Total produk: {$totalChecked}");
        $this->info('');

        foreach ($produks as $produk) {
            $productLabel = $this->buildProductLabel($produk);

            // Skip if already has image and not forcing
            if (!empty($produk->gambar) && !$force) {
                $totalSkipped++;
                $this->line("  <fg=yellow>[SKIP]</> Produk sudah punya gambar: {$productLabel}");
                continue;
            }

            // Resolve folder for merek
            $merekFolder = $this->resolveMerekFolder($produk->merek);
            if (!$merekFolder) {
                $totalNotFound++;
                $notFoundList[] = $productLabel;
                $this->line("  <fg=red>[NOT FOUND]</> {$productLabel} — merek \"{$produk->merek}\" tidak ada folder gambar");
                continue;
            }

            // Resolve folder for jenis APAR
            $jenisNama = $produk->jenisApar?->nama ?? '';
            $jenisFolder = $this->resolveJenisFolder($jenisNama);
            if (!$jenisFolder) {
                $totalNotFound++;
                $notFoundList[] = $productLabel;
                $this->line("  <fg=red>[NOT FOUND]</> {$productLabel} — jenis \"{$jenisNama}\" tidak ada folder gambar");
                continue;
            }

            // Resolve file for kapasitas
            $ukuranNormalized = $this->normalizeUkuran($produk->kapasitas ?? '');
            if (empty($ukuranNormalized)) {
                $totalNotFound++;
                $notFoundList[] = $productLabel;
                $this->line("  <fg=red>[NOT FOUND]</> {$productLabel} — kapasitas kosong atau tidak valid");
                continue;
            }

            // Look up image in index
            $indexKey = strtolower("{$merekFolder}/{$jenisFolder}/{$ukuranNormalized}");
            $imagePath = $imageIndex[$indexKey] ?? null;

            if (!$imagePath) {
                $totalNotFound++;
                $notFoundList[] = $productLabel;
                $this->line("  <fg=red>[NOT FOUND]</> {$productLabel} — file gambar tidak ditemukan untuk ukuran \"{$ukuranNormalized}\"");
                continue;
            }

            // Store relative path (apar/MEREK/JENIS/size.ext)
            $relativePath = $imagePath;

            $produk->gambar = $relativePath;
            $produk->save();

            $totalUpdated++;
            $this->line("  <fg=green>[OK]</> {$productLabel} => {$relativePath}");
        }

        // Summary
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info("  📊 Ringkasan Sinkronisasi Gambar APAR");
        $this->info('══════════════════════════════════════════════════');
        $this->info("  Total produk dicek       : {$totalChecked}");
        $this->info("  ✅ Berhasil diberi gambar : {$totalUpdated}");
        $this->info("  ⏩ Dilewati (sudah ada)  : {$totalSkipped}");
        $this->info("  ❌ Gambar tidak ditemukan : {$totalNotFound}");
        $this->info('══════════════════════════════════════════════════');

        if (!empty($notFoundList)) {
            $this->info('');
            $this->warn('Daftar produk yang gagal dicocokkan:');
            foreach ($notFoundList as $item) {
                $this->line("  - {$item}");
            }
        }

        $this->info('');

        return self::SUCCESS;
    }

    /**
     * Build an index of all available images in the apar folder.
     * Key format: "merekfolder/jenisfolder/ukuran" (lowercase)
     * Value: storage path relative to storage/app
     */
    private function buildImageIndex(string $basePath): array
    {
        $index = [];

        $merekFolders = $this->disk->directories($basePath);

        foreach ($merekFolders as $merekDir) {
            $merekName = basename($merekDir);
            $jenisFolders = $this->disk->directories($merekDir);

            foreach ($jenisFolders as $jenisDir) {
                $jenisName = basename($jenisDir);
                $files = $this->disk->files($jenisDir);

                foreach ($files as $file) {
                    $filename = basename($file);
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                    if (!in_array($extension, $this->supportedExtensions)) {
                        continue;
                    }

                    // Extract size from filename (remove extension)
                    $sizeRaw = pathinfo($filename, PATHINFO_FILENAME);
                    $sizeNormalized = $this->normalizeUkuranValue($sizeRaw);

                    $key = strtolower("{$merekName}/{$jenisName}/{$sizeNormalized}");
                    $index[$key] = $file;
                }
            }
        }

        return $index;
    }

    /**
     * Resolve product merek to the actual folder name.
     */
    private function resolveMerekFolder(?string $merek): ?string
    {
        if (empty($merek)) {
            return null;
        }

        $normalized = strtolower(trim($merek));

        // Direct match in map
        if (isset($this->merekFolderMap[$normalized])) {
            return $this->merekFolderMap[$normalized];
        }

        // Try removing spaces
        $noSpaces = str_replace(' ', '', $normalized);
        foreach ($this->merekFolderMap as $key => $folder) {
            if (str_replace(' ', '', $key) === $noSpaces) {
                return $folder;
            }
        }

        // Try partial/contains match
        foreach ($this->merekFolderMap as $key => $folder) {
            if (str_contains($normalized, str_replace(' ', '', $key)) || str_contains(str_replace(' ', '', $key), $normalized)) {
                return $folder;
            }
        }

        return null;
    }

    /**
     * Resolve jenis APAR name to the actual folder name.
     */
    private function resolveJenisFolder(?string $jenis): ?string
    {
        if (empty($jenis)) {
            return null;
        }

        $normalized = strtolower(trim($jenis));

        // Direct match in map
        if (isset($this->jenisFolderMap[$normalized])) {
            return $this->jenisFolderMap[$normalized];
        }

        // Try contains match (check if any map key is contained in the jenis name)
        foreach ($this->jenisFolderMap as $key => $folder) {
            if (str_contains($normalized, $key)) {
                return $folder;
            }
        }

        // Try if jenis name contains map key's folder name directly
        $normalizedNoSpaces = str_replace(' ', '', $normalized);
        foreach (['powder' => 'POWDER', 'co2' => 'CO2', 'foam' => 'FOAM'] as $keyword => $folder) {
            if (str_contains($normalizedNoSpaces, $keyword)) {
                return $folder;
            }
        }

        return null;
    }

    /**
     * Normalize kapasitas field from product.
     * Examples: "1 kg" => "1", "2,2 kg" => "2,2", "6.8 Kg" => "6,8"
     */
    private function normalizeUkuran(?string $kapasitas): string
    {
        if (empty($kapasitas)) {
            return '';
        }

        // Remove kg/KG/Kg/liter/L and trim
        $value = preg_replace('/\s*(kg|liter|l)\s*/i', '', trim($kapasitas));
        $value = trim($value);

        return $this->normalizeUkuranValue($value);
    }

    /**
     * Normalize a size value for consistent matching.
     * Converts both comma and dot to comma for storage key.
     * "2.2" => "2,2", "2,2" => "2,2", "1" => "1"
     */
    private function normalizeUkuranValue(string $value): string
    {
        $value = trim($value);

        // Normalize: replace dots with commas for consistent key
        $value = str_replace('.', ',', $value);

        // Remove trailing ,0 (e.g., "2,0" => "2")
        $value = preg_replace('/,0$/', '', $value);

        return $value;
    }

    /**
     * Build a human-readable product label for console output.
     */
    private function buildProductLabel(Produk $produk): string
    {
        return collect([
            'APAR',
            $produk->merek,
            $produk->jenisApar?->nama,
            $produk->kapasitas,
        ])->filter()->implode(' ');
    }
}
