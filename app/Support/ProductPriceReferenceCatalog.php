<?php

namespace App\Support;

use App\Models\Produk;

class ProductPriceReferenceCatalog
{
    /**
     * Harga referensi yang diverifikasi dari listing e-commerce pada 2026-06-19.
     *
     * Beberapa varian memakai alias ukuran marketplace:
     * - CO2 Firefix/GuardALL 2 kg mengikuti listing 2.2 kg.
     * - CO2 Tonata 6.8 kg mengikuti listing 7 kg official store.
     *
     * @var array<int, array<string, mixed>>
     */
    private const REFERENCES = [
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['1'], 'display_size' => '1 kg', 'price' => 181624, 'source_label' => 'Fireshop - APAR 1 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-1-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['2'], 'display_size' => '2 kg', 'price' => 240093, 'source_label' => 'Fireshop - APAR 2 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-2-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 300699, 'source_label' => 'Fireshop - APAR 3 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-3-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['4'], 'display_size' => '4 kg', 'price' => 354118, 'source_label' => 'Fireshop - APAR 4 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-4-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 439976, 'source_label' => 'Fireshop - APAR 6 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-6-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'POWDER', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 601398, 'source_label' => 'Fireshop - APAR 9 Kg Powder Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-9-kg-powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'CO2', 'match_sizes' => ['2', '2.2'], 'display_size' => '2.2 kg', 'price' => 707070, 'source_label' => 'Fireshop - APAR CO2 2.2 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-2-2-kg-co2/', 'checked_at' => '2026-06-19', 'note' => 'Listing marketplace memakai varian 2.2 kg untuk stok 2 kg.'],
        ['brand' => 'FIREFIX', 'media' => 'CO2', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 823232, 'source_label' => 'Fireshop - APAR CO2 3 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-3-kg-co2/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'CO2', 'match_sizes' => ['5'], 'display_size' => '5 kg', 'price' => 1202408, 'source_label' => 'Fireshop - APAR CO2 5 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-5-kg-co2/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'CO2', 'match_sizes' => ['6.8'], 'display_size' => '6.8 kg', 'price' => 1433759, 'source_label' => 'Fireshop - APAR CO2 6.8 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-6-8-kg-co2/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'FOAM', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 495726, 'source_label' => 'Fireshop - APAR Foam 6 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-6-kg-foam/', 'checked_at' => '2026-06-19'],
        ['brand' => 'FIREFIX', 'media' => 'FOAM', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 577117, 'source_label' => 'Fireshop - APAR Foam 9 Kg Firefix', 'source_url' => 'https://fireshop.co.id/harga/apar-firefix-9-kg-foam/', 'checked_at' => '2026-06-19'],

        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['1'], 'display_size' => '1 kg', 'price' => 505050, 'source_label' => 'Fireshop - APAR 1 Kg Powder GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-1-kg-powder-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['2'], 'display_size' => '2 kg', 'price' => 678321, 'source_label' => 'Fireshop - APAR 2 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-2-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 1289820, 'source_label' => 'Fireshop - APAR 3 Kg Powder GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-3-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['4'], 'display_size' => '4 kg', 'price' => 1486207, 'source_label' => 'Fireshop - APAR 4 Kg Powder GuardALL', 'source_url' => 'https://fireshop.co.id/daftar/apar/powder/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 1936867, 'source_label' => 'Fireshop - APAR 6 Kg Powder GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-6-kg-powder-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'POWDER', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 7709006, 'source_label' => 'Fireshop - APAR 9 Kg Powder GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-9-kg-powder-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'CO2', 'match_sizes' => ['2', '2.2'], 'display_size' => '2.2 kg', 'price' => 2808272, 'source_label' => 'Fireshop - APAR CO2 2.2 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-co2-22-kg-guardall/', 'checked_at' => '2026-06-19', 'note' => 'Listing marketplace memakai varian 2.2 kg untuk stok 2 kg.'],
        ['brand' => 'GUARDALL', 'media' => 'CO2', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 3010875, 'source_label' => 'Fireshop - APAR CO2 3 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-co2-3-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'CO2', 'match_sizes' => ['5'], 'display_size' => '5 kg', 'price' => 4351200, 'source_label' => 'Fireshop - APAR CO2 5 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-co2-5-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'CO2', 'match_sizes' => ['6.8'], 'display_size' => '6.8 kg', 'price' => 3574200, 'source_label' => 'Fireshop - APAR CO2 6.8 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-co2-6-8-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'FOAM', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 1814878, 'source_label' => 'Fireshop - APAR Foam 6 Kg GuardALL', 'source_url' => 'https://fireshop.co.id/harga/apar-foam-6-kg-guardall/', 'checked_at' => '2026-06-19'],
        ['brand' => 'GUARDALL', 'media' => 'FOAM', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 2242228, 'source_label' => 'PemadamApi.id - APAR Foam 9 Kg GuardALL', 'source_url' => 'https://www.pemadamapi.id/supplier-alat-pemadam-kebakaran-di-jakarta/', 'checked_at' => '2026-06-19'],

        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['1'], 'display_size' => '1 kg', 'price' => 465000, 'source_label' => 'Blibli - APAR ABC Powder 1 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/apar-1-kg', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['2'], 'display_size' => '2 kg', 'price' => 618000, 'source_label' => 'Blibli - APAR ABC Powder 2 Kg Tonata', 'source_url' => 'https://www.blibli.com/p/apar-abc-powder-2-kg-tonata-set-komplit/ps--TOS-60288-00360', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 774000, 'source_label' => 'Blibli - APAR ABC Powder 3 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/apar-1-kg', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['4'], 'display_size' => '4 kg', 'price' => 909000, 'source_label' => 'Blibli - APAR ABC Powder 4 Kg Tonata', 'source_url' => 'https://www.blibli.com/p/apar-abc-powder-4-kg-tonata-set-komplit-standard-powder/ps--TOS-60288-00361', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 1218000, 'source_label' => 'Blibli - APAR ABC Powder 6 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/tonata-6-kg', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'POWDER', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 1701000, 'source_label' => 'Blibli - APAR ABC Powder 9 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/tonata-apar-powder', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'CO2', 'match_sizes' => ['2'], 'display_size' => '2 kg', 'price' => 956000, 'source_label' => 'Blibli - APAR Karbondioksida 2 Kg Tonata', 'source_url' => 'https://www.blibli.com/p/tonata-karbondioksida-co2-set-komplit-tabung-apar-2-kg/ps--TOS-60288-00008', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'CO2', 'match_sizes' => ['3'], 'display_size' => '3 kg', 'price' => 1075000, 'source_label' => 'Blibli - APAR Karbondioksida 3 Kg Tonata', 'source_url' => 'https://www.blibli.com/p/tonata-karbondioksida-co2-set-komplit-tabung-apar-3-kg/ps--TOS-60288-00009', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'CO2', 'match_sizes' => ['5'], 'display_size' => '5 kg', 'price' => 1677000, 'source_label' => 'Blibli - APAR Karbondioksida 5 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/apar-tonata-karbondioksida', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'CO2', 'match_sizes' => ['6.8', '7'], 'display_size' => '7 kg', 'price' => 2607000, 'source_label' => 'Blibli - APAR Karbondioksida 7 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/apar-co2-per-kilo', 'checked_at' => '2026-06-19', 'note' => 'Listing official store menampilkan varian 7 kg untuk kebutuhan stok 6.8 kg.'],
        ['brand' => 'TONATA', 'media' => 'FOAM', 'match_sizes' => ['6'], 'display_size' => '6 kg', 'price' => 1035000, 'source_label' => 'Blibli - APAR FFFP Foam 6 Kg Tonata', 'source_url' => 'https://www.blibli.com/jual/tonata-6-kg', 'checked_at' => '2026-06-19'],
        ['brand' => 'TONATA', 'media' => 'FOAM', 'match_sizes' => ['9'], 'display_size' => '9 kg', 'price' => 1298000, 'source_label' => 'Blibli - APAR FFFP Foam 9 Kg Tonata', 'source_url' => 'https://www.blibli.com/p/apar-fffp-foam-9-kg-tonata-set-komplit/ps--TOS-60288-00663', 'checked_at' => '2026-06-19'],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::REFERENCES;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findMatch(Produk $produk): ?array
    {
        $brand = self::normalizeBrand((string) $produk->merek);
        $media = self::normalizeMedia((string) ($produk->jenisApar?->nama ?? ''));
        $size = self::normalizeSize((string) $produk->kapasitas);

        foreach (self::REFERENCES as $reference) {
            if ($reference['brand'] !== $brand) {
                continue;
            }

            if ($reference['media'] !== $media) {
                continue;
            }

            if (!in_array($size, $reference['match_sizes'], true)) {
                continue;
            }

            return $reference;
        }

        return null;
    }

    public static function formatSource(array $reference): string
    {
        $source = trim((string) ($reference['source_label'] ?? 'Referensi marketplace'));
        $checkedAt = trim((string) ($reference['checked_at'] ?? ''));

        return $checkedAt !== ''
            ? $source . ' (' . $checkedAt . ')'
            : $source;
    }

    private static function normalizeBrand(string $brand): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($brand)));
    }

    private static function normalizeMedia(string $media): string
    {
        $normalized = strtolower(trim($media));

        if (str_contains($normalized, 'co2') || str_contains($normalized, 'karbon dioksida')) {
            return 'CO2';
        }

        if (str_contains($normalized, 'foam') || str_contains($normalized, 'busa')) {
            return 'FOAM';
        }

        return 'POWDER';
    }

    private static function normalizeSize(string $size): string
    {
        if (!preg_match('/(\d+(?:[.,]\d+)?)/', $size, $matches)) {
            return '';
        }

        $value = (float) str_replace(',', '.', $matches[1]);
        $normalized = rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');

        return $normalized === '' ? '0' : $normalized;
    }
}
