<?php

namespace App\Console\Commands;

use App\Models\Produk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class RepairProducts extends Command
{
    protected $signature = 'apar:repair-products {--dry : Tampilkan perubahan tanpa menyimpan ke database}';

    protected $description = 'Perbaiki data merek, nama produk, dan sinkronisasi gambar APAR sesuai katalog gambar yang tersedia';

    /**
     * Mapping merek lama => merek baru (sesuai folder gambar).
     */
    private array $merekMigration = [
        'SAFETY'  => 'FIREFIX',
        'ABC'     => 'GuardALL',
        'GUARD'   => 'TONATA',
    ];

    /**
     * Mapping merek produk => nama folder di storage.
     */
    private array $merekFolderMap = [
        'firefix'   => 'FIREFIX',
        'fire fix'  => 'FIREFIX',
        'guardall'  => 'GuardALL',
        'guard all' => 'GuardALL',
        'guard'     => 'GuardALL',
        'tonata'    => 'TONATA',
    ];

    /**
     * Mapping jenis APAR => nama folder kategori.
     */
    private array $jenisFolderMap = [
        'dry chemical powder' => 'POWDER',
        'powder'              => 'POWDER',
        'dcp'                 => 'POWDER',
        'co2'                 => 'CO2',
        'co₂'                 => 'CO2',
        'carbon dioxide'      => 'CO2',
        'foam'                => 'FOAM',
        'busa'                => 'FOAM',
        'afff'                => 'FOAM',
        'liquid foam'         => 'FOAM',
    ];

    /**
     * Mapping jenis APAR => label singkat untuk nama produk.
     */
    private array $jenisShortLabel = [
        'POWDER' => 'Powder',
        'CO2'    => 'CO2',
        'FOAM'   => 'Foam',
    ];

    private array $supportedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    private Filesystem $disk;

    public function handle(): int
    {
        $dryRun = $this->option('dry');
        $this->disk = Storage::disk('public');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║    APAR Product Repair — PD. Anugrah Utama          ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');

        if ($dryRun) {
            $this->warn('🧪 Mode --dry aktif: perubahan hanya ditampilkan, TIDAK disimpan.');
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
            return self::FAILURE;
        }

        // Build available image index
        $imageIndex = $this->buildImageIndex($basePath);

        $this->info('📂 Image index: ' . count($imageIndex) . ' gambar ditemukan');
        $this->info('');

        // Load all products
        $produks = Produk::with('jenisApar')->get();

        if ($produks->isEmpty()) {
            $this->warn('Tidak ada produk di database.');
            return self::SUCCESS;
        }

        $totalChecked = $produks->count();
        $totalMerekFixed = 0;
        $totalNamaFixed = 0;
        $totalImageFixed = 0;
        $totalImageNotFound = 0;
        $notFoundList = [];
        $changes = [];

        $this->info("📦 Total produk: {$totalChecked}");
        $this->info('');

        // ═══════════════════════════════════════
        // PHASE 1: Fix Merek
        // ═══════════════════════════════════════
        $this->info('── PHASE 1: Perbaiki Merek ──────────────────────');
        $this->info('');

        foreach ($produks as $produk) {
            $oldMerek = $produk->merek;
            $newMerek = $this->merekMigration[strtoupper(trim($oldMerek))] ?? null;

            if ($newMerek && $newMerek !== $oldMerek) {
                $totalMerekFixed++;
                $this->line("  <fg=cyan>[MEREK]</> {$oldMerek} → {$newMerek}  (ID: {$produk->id})");
                $produk->merek = $newMerek;
            }
        }

        if ($totalMerekFixed === 0) {
            $this->line('  <fg=green>✓</> Semua merek sudah benar.');
        }

        $this->info('');

        // ═══════════════════════════════════════
        // PHASE 2: Fix Nama Produk
        // ═══════════════════════════════════════
        $this->info('── PHASE 2: Perbaiki Nama Produk ────────────────');
        $this->info('');

        foreach ($produks as $produk) {
            $jenisNama = $produk->jenisApar?->nama ?? '';
            $jenisFolder = $this->resolveJenisFolder($jenisNama);
            $jenisLabel = $jenisFolder ? ($this->jenisShortLabel[$jenisFolder] ?? $jenisNama) : $jenisNama;

            $newNama = "APAR {$produk->merek} {$jenisLabel} {$produk->kapasitas}";

            if ($newNama !== $produk->nama) {
                $totalNamaFixed++;
                $this->line("  <fg=yellow>[NAMA]</> {$produk->nama}");
                $this->line("         → {$newNama}");
                $produk->nama = $newNama;
            }
        }

        if ($totalNamaFixed === 0) {
            $this->line('  <fg=green>✓</> Semua nama sudah benar.');
        }

        $this->info('');

        // ═══════════════════════════════════════
        // PHASE 3: Sync Gambar
        // ═══════════════════════════════════════
        $this->info('── PHASE 3: Sinkronisasi Gambar ────────────────');
        $this->info('');

        foreach ($produks as $produk) {
            $merekFolder = $this->resolveMerekFolder($produk->merek);
            if (!$merekFolder) {
                $totalImageNotFound++;
                $notFoundList[] = $produk->nama . " (merek \"{$produk->merek}\" tidak ada folder)";
                $this->line("  <fg=red>[NOT FOUND]</> {$produk->nama} — merek \"{$produk->merek}\"");
                continue;
            }

            $jenisNama = $produk->jenisApar?->nama ?? '';
            $jenisFolder = $this->resolveJenisFolder($jenisNama);
            if (!$jenisFolder) {
                $totalImageNotFound++;
                $notFoundList[] = $produk->nama . " (jenis \"{$jenisNama}\" tidak ada folder)";
                $this->line("  <fg=red>[NOT FOUND]</> {$produk->nama} — jenis \"{$jenisNama}\"");
                continue;
            }

            $ukuranNormalized = $this->normalizeUkuran($produk->kapasitas ?? '');
            if (empty($ukuranNormalized)) {
                $totalImageNotFound++;
                $notFoundList[] = $produk->nama . ' (kapasitas kosong)';
                $this->line("  <fg=red>[NOT FOUND]</> {$produk->nama} — kapasitas kosong");
                continue;
            }

            $indexKey = strtolower("{$merekFolder}/{$jenisFolder}/{$ukuranNormalized}");
            $imagePath = $imageIndex[$indexKey] ?? null;

            if (!$imagePath) {
                $totalImageNotFound++;
                $notFoundList[] = $produk->nama . " (file \"{$indexKey}\" tidak ada)";
                $this->line("  <fg=red>[NOT FOUND]</> {$produk->nama} — file gambar \"{$indexKey}.*\"");
                continue;
            }

            $oldGambar = $produk->gambar;
            $produk->gambar = $imagePath;

            if ($oldGambar !== $imagePath) {
                $totalImageFixed++;
                $label = $oldGambar ? 'UPDATED' : 'SET';
                $this->line("  <fg=green>[{$label}]</> {$produk->nama} => {$imagePath}");
            } else {
                $this->line("  <fg=gray>[OK]</> {$produk->nama} — sudah benar");
            }
        }

        $this->info('');

        // ═══════════════════════════════════════
        // PHASE 4: Save to Database
        // ═══════════════════════════════════════
        if (!$dryRun) {
            $this->info('── PHASE 4: Menyimpan ke Database ──────────────');
            $this->info('');

            $totalSaved = 0;
            foreach ($produks as $produk) {
                if ($produk->isDirty()) {
                    $produk->save();
                    $totalSaved++;
                }
            }
            $this->info("  💾 {$totalSaved} produk disimpan.");
        } else {
            $this->warn('── Mode DRY: Tidak ada data yang disimpan ──────');
        }

        // Summary
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  📊 Ringkasan Repair Produk APAR');
        $this->info('══════════════════════════════════════════════════');
        $this->info("  Total produk dicek       : {$totalChecked}");
        $this->info("  🏷  Merek diperbaiki      : {$totalMerekFixed}");
        $this->info("  📝 Nama diperbaiki       : {$totalNamaFixed}");
        $this->info("  🖼  Gambar dipasang/update : {$totalImageFixed}");
        $this->info("  ❌ Gambar tidak ditemukan : {$totalImageNotFound}");
        $this->info('══════════════════════════════════════════════════');

        if (!empty($notFoundList)) {
            $this->info('');
            $this->warn('Produk yang gagal dicocokkan gambar:');
            foreach ($notFoundList as $item) {
                $this->line("  - {$item}");
            }
        }

        $this->info('');

        return self::SUCCESS;
    }

    /**
     * Build an index of all available images in the apar folder.
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

                    $sizeRaw = pathinfo($filename, PATHINFO_FILENAME);
                    $sizeNormalized = $this->normalizeUkuranValue($sizeRaw);

                    $key = strtolower("{$merekName}/{$jenisName}/{$sizeNormalized}");
                    $index[$key] = $file;
                }
            }
        }

        return $index;
    }

    private function resolveMerekFolder(?string $merek): ?string
    {
        if (empty($merek)) {
            return null;
        }

        $normalized = strtolower(trim($merek));

        if (isset($this->merekFolderMap[$normalized])) {
            return $this->merekFolderMap[$normalized];
        }

        $noSpaces = str_replace(' ', '', $normalized);
        foreach ($this->merekFolderMap as $key => $folder) {
            if (str_replace(' ', '', $key) === $noSpaces) {
                return $folder;
            }
        }

        foreach ($this->merekFolderMap as $key => $folder) {
            if (str_contains($normalized, str_replace(' ', '', $key)) || str_contains(str_replace(' ', '', $key), $normalized)) {
                return $folder;
            }
        }

        return null;
    }

    private function resolveJenisFolder(?string $jenis): ?string
    {
        if (empty($jenis)) {
            return null;
        }

        $normalized = strtolower(trim($jenis));

        if (isset($this->jenisFolderMap[$normalized])) {
            return $this->jenisFolderMap[$normalized];
        }

        foreach ($this->jenisFolderMap as $key => $folder) {
            if (str_contains($normalized, $key)) {
                return $folder;
            }
        }

        $normalizedNoSpaces = str_replace(' ', '', $normalized);
        foreach (['powder' => 'POWDER', 'co2' => 'CO2', 'foam' => 'FOAM'] as $keyword => $folder) {
            if (str_contains($normalizedNoSpaces, $keyword)) {
                return $folder;
            }
        }

        return null;
    }

    private function normalizeUkuran(?string $kapasitas): string
    {
        if (empty($kapasitas)) {
            return '';
        }

        $value = preg_replace('/\s*(kg|liter|l)\s*/i', '', trim($kapasitas));
        $value = trim($value);

        return $this->normalizeUkuranValue($value);
    }

    private function normalizeUkuranValue(string $value): string
    {
        $value = trim($value);
        $value = str_replace('.', ',', $value);
        $value = preg_replace('/,0$/', '', $value);

        return $value;
    }
}
