<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Produk extends Model
{
    use Auditable;

    protected static array $aparImageCatalogCache = [];
    protected static array $resolvedImagePathCache = [];

    protected $fillable = ['nama', 'merek', 'jenis_apar_id', 'kapasitas', 'penggunaan', 'harga', 'gambar', 'deskripsi', 'stok'];

    public function jenisApar()
    {
        return $this->belongsTo(JenisApar::class);
    }

    public function units()
    {
        return $this->hasMany(UnitApar::class);
    }

    public function pesanan()
    {
        return $this->hasMany(PesananDetail::class);
    }

    public function stokBatches()
    {
        return $this->hasMany(StokBatch::class);
    }

    public function pesananDetails()
    {
        return $this->hasMany(PesananDetail::class);
    }

    public function sellableStokBatches()
    {
        return $this->hasMany(StokBatch::class)
            ->where('sisa_qty', '>', 0)
            ->whereDate('tgl_expired', '>=', now()->toDateString());
    }

    public function getStokTersediaAttribute(): int
    {
        return $this->sellableBatchCollection()->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));
    }

    public function getStokBatchTotalAttribute(): int
    {
        return $this->allPositiveBatchCollection()->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));
    }

    public function getStokKadaluarsaAttribute(): int
    {
        return max(0, $this->stok_batch_total - $this->stok_tersedia);
    }

    public function getIsHabisAttribute(): bool
    {
        return $this->stok_tersedia <= 0;
    }

    public function getResolvedImagePathAttribute(): ?string
    {
        $cacheKey = implode('|', [
            (string) $this->getKey(),
            (string) $this->gambar,
            (string) $this->merek,
            (string) $this->jenisApar?->nama,
            (string) $this->kapasitas,
        ]);

        if (array_key_exists($cacheKey, self::$resolvedImagePathCache)) {
            return self::$resolvedImagePathCache[$cacheKey];
        }

        $resolved = $this->resolveImagePath();
        self::$resolvedImagePathCache[$cacheKey] = $resolved;

        return $resolved;
    }

    public function getResolvedImageUrlAttribute(): ?string
    {
        $path = $this->resolved_image_path;

        if (!$path) {
            return null;
        }

        return '/storage/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    public function hasResolvedImage(): bool
    {
        return !empty($this->resolved_image_path);
    }

    public function hasEnoughSellableStock(int $qty): bool
    {
        return $this->stok_tersedia >= max(0, $qty);
    }

    private function sellableBatchCollection(): Collection
    {
        if ($this->relationLoaded('stokBatches')) {
            $today = now()->toDateString();

            return $this->stokBatches
                ->filter(function (StokBatch $batch) use ($today) {
                    $expiredAt = $batch->tgl_expired?->toDateString();

                    return (int) ($batch->sisa_qty ?? 0) > 0
                        && !empty($expiredAt)
                        && $expiredAt >= $today;
                })
                ->values();
        }

        return $this->sellableStokBatches()->get();
    }

    private function allPositiveBatchCollection(): Collection
    {
        if ($this->relationLoaded('stokBatches')) {
            return $this->stokBatches
                ->filter(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0) > 0)
                ->values();
        }

        return $this->stokBatches()
            ->where('sisa_qty', '>', 0)
            ->get();
    }

    private function resolveImagePath(): ?string
    {
        if (!empty($this->gambar) && Storage::disk('public')->exists($this->gambar)) {
            return $this->gambar;
        }

        $brand = $this->normalizeBrand((string) $this->merek);
        $media = $this->normalizeMedia((string) ($this->jenisApar?->nama ?? ''));
        $catalog = $this->aparImageCatalog();
        $files = $catalog[$brand][$media] ?? [];

        if (empty($files)) {
            return null;
        }

        $sizeCandidates = $this->sizeCandidates((string) $this->kapasitas);
        foreach ($sizeCandidates as $candidate) {
            foreach ($files as $file) {
                if ($file['basename'] === $candidate) {
                    return $file['path'];
                }
            }
        }

        $targetNumber = $this->extractNumericSize((string) $this->kapasitas);
        if ($targetNumber !== null) {
            $closest = collect($files)
                ->filter(fn (array $file) => $file['number'] !== null)
                ->sortBy(fn (array $file) => abs($file['number'] - $targetNumber))
                ->first();

            if ($closest) {
                return $closest['path'];
            }
        }

        return $files[0]['path'] ?? null;
    }

    private function aparImageCatalog(): array
    {
        if (!empty(self::$aparImageCatalogCache)) {
            return self::$aparImageCatalogCache;
        }

        $catalog = [];
        foreach (Storage::disk('public')->allFiles('apar') as $path) {
            $segments = explode('/', $path);
            if (count($segments) !== 4) {
                continue;
            }

            [, $brand, $media, $filename] = $segments;
            $basename = pathinfo($filename, PATHINFO_FILENAME);

            $catalog[$this->normalizeBrand($brand)][$this->normalizeMedia($media)][] = [
                'basename' => $this->normalizeSizeToken($basename),
                'number' => $this->extractNumericSize($basename),
                'path' => $path,
            ];
        }

        self::$aparImageCatalogCache = $catalog;

        return self::$aparImageCatalogCache;
    }

    private function normalizeBrand(string $brand): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($brand)));
    }

    private function normalizeMedia(string $media): string
    {
        $normalized = strtolower(trim($media));

        if (str_contains($normalized, 'co2') || str_contains($normalized, 'karbon dioksida')) {
            return 'CO2';
        }

        if (str_contains($normalized, 'foam') || str_contains($normalized, 'busa')) {
            return 'FOAM';
        }

        if (str_contains($normalized, 'powder') || str_contains($normalized, 'dry chemical') || str_contains($normalized, 'dry powder')) {
            return 'POWDER';
        }

        return strtoupper(preg_replace('/\s+/', '', $media));
    }

    private function sizeCandidates(string $size): array
    {
        $token = $this->normalizeSizeToken($size);
        $number = $this->extractNumericSize($size);
        $candidates = [$token];

        if ($number !== null) {
            $integerForm = fmod($number, 1.0) === 0.0 ? (string) (int) $number : null;
            if ($integerForm) {
                $candidates[] = $integerForm;
            }

            $dotForm = rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');
            $commaForm = str_replace('.', ',', $dotForm);
            $candidates[] = $dotForm;
            $candidates[] = $commaForm;

            // Alias naming yang dipakai user: produk 2 kg boleh memakai foto 2,2 kg.
            if (abs($number - 2.0) < 0.11 || abs($number - 2.2) < 0.11) {
                $candidates[] = '2';
                $candidates[] = '2.2';
                $candidates[] = '2,2';
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function normalizeSizeToken(string $size): string
    {
        $normalized = strtolower(trim($size));
        $normalized = str_replace(['kg', ' '], '', $normalized);

        return $normalized;
    }

    private function extractNumericSize(string $size): ?float
    {
        if (!preg_match('/(\d+(?:[.,]\d+)?)/', $size, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }
}
