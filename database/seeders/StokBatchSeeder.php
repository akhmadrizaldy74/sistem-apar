<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\StokBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StokBatchSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus batch lama agar fresh
        StokBatch::truncate();

        $produks = Produk::all();

        foreach ($produks as $index => $produk) {
            $isOneKg = trim(strtolower($produk->kapasitas)) === '1 kg';

            // Batch 1: Masuk 2-3 bulan lalu (Aman)
            $tglProduksi1 = Carbon::now()->subMonths(3)->toDateString();
            $tglExpired1 = $isOneKg 
                ? Carbon::parse($tglProduksi1)->addMonths(6)->toDateString()
                : Carbon::parse($tglProduksi1)->addYears(1)->toDateString();

            StokBatch::create([
                'produk_id' => $produk->id,
                'jumlah_masuk' => 20 + ($index % 5),
                'sisa_qty' => 15 + ($index % 5),
                'tgl_produksi' => $tglProduksi1,
                'tgl_expired' => $tglExpired1,
                'keterangan' => 'Batch Restock Gudang Utama - Vendor A',
            ]);

            // Batch 2: Masuk agak lama (Hampir Expired atau Expired)
            // Biar realistis, beberapa produk kita kasih batch yang mepet/habis masa berlakunya
            if ($index % 3 === 0) {
                // Buat expired
                $tglProduksi2 = Carbon::now()->subMonths($isOneKg ? 8 : 14)->toDateString();
                $tglExpired2 = $isOneKg 
                    ? Carbon::parse($tglProduksi2)->addMonths(6)->toDateString()
                    : Carbon::parse($tglProduksi2)->addYears(1)->toDateString();

                StokBatch::create([
                    'produk_id' => $produk->id,
                    'jumlah_masuk' => 10,
                    'sisa_qty' => 3,
                    'tgl_produksi' => $tglProduksi2,
                    'tgl_expired' => $tglExpired2,
                    'keterangan' => 'Sisa Batch Cuci Gudang - Vendor B',
                ]);
            } elseif ($index % 3 === 1) {
                // Buat hampir expired (misal 15 hari lagi)
                $tglExpired3 = Carbon::now()->addDays(15)->toDateString();
                $tglProduksi3 = $isOneKg 
                    ? Carbon::parse($tglExpired3)->subMonths(6)->toDateString()
                    : Carbon::parse($tglExpired3)->subYears(1)->toDateString();

                StokBatch::create([
                    'produk_id' => $produk->id,
                    'jumlah_masuk' => 15,
                    'sisa_qty' => 8,
                    'tgl_produksi' => $tglProduksi3,
                    'tgl_expired' => $tglExpired3,
                    'keterangan' => 'Batch Khusus Siap Distribusi - Vendor C',
                ]);
            }

            // Hitung ulang total stok di tabel produk
            $totalStok = StokBatch::where('produk_id', $produk->id)->sum('sisa_qty');
            $produk->update(['stok' => $totalStok]);
        }
    }
}
