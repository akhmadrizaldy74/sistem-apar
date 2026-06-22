<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_stock_history_and_reports_show_clear_stock_in_and_out_labels(): void
    {
        Carbon::setTestNow('2026-06-22 10:00:00');

        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111188',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 2 kg',
            'merek' => 'SAFECO',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 618000,
            'stok' => 8,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 8,
            'sisa_qty' => 8,
            'tgl_produksi' => '2026-06-20',
            'tgl_expired' => '2027-06-20',
            'keterangan' => 'Batch aktif',
        ]);

        Pengeluaran::create([
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama,
            'qty' => 10,
            'satuan' => 'Unit',
            'harga_beli' => 1100000,
            'nominal' => 11000000,
            'total' => 11000000,
            'keterangan' => 'Catatan pembelian admin',
            'tanggal' => '2026-06-22',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Uji Stok',
            'no_wa' => '628111000123',
            'alamat' => 'Jl. Uji Stok No. 1',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'produk',
            'sumber_pesanan' => 'input_admin',
            'total' => 1236000,
            'total_harga' => 1236000,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => 'selesai final',
            'stok_dikurangi' => true,
            'tanggal' => '2026-06-22',
            'pembayaran_terkonfirmasi_at' => '2026-06-22 11:30:00',
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 2,
            'harga' => 618000,
            'subtotal' => 1236000,
        ]);

        $stockPage = $this->actingAs($admin)->get(route('admin.stok.index'));
        $reportPage = $this->actingAs($admin)->get(route('admin.laporan.index'));
        $financePage = $this->actingAs($admin)->get(route('admin.laporan.keuangan'));

        $stockPage->assertOk();
        $stockPage->assertSeeText('Riwayat Transaksi Stok');
        $stockPage->assertSeeText('Pembelian Stok APAR');
        $stockPage->assertSeeText('Penjualan Produk APAR');
        $stockPage->assertSeeText('Menu Pengeluaran');
        $stockPage->assertSeeText('Pesanan Pelanggan');
        $stockPage->assertSeeText('Pembelian stok APAR dicatat lewat menu Pengeluaran.');
        $stockPage->assertSeeText('Stok keluar karena pembelian pelanggan PT Uji Stok.');
        $stockPage->assertDontSeeText('Pengeluaran Stok - Catatan pembelian admin');

        $reportPage->assertOk();
        $reportPage->assertSeeText('Riwayat Pergerakan Stok');
        $reportPage->assertSeeText('Pembelian Stok APAR');
        $reportPage->assertSeeText('Penjualan Produk APAR');
        $reportPage->assertSeeText('Stok APAR masuk lewat transaksi pembelian admin.');
        $reportPage->assertSeeText('Stok keluar karena pembelian pelanggan PT Uji Stok.');

        $financePage->assertOk();
        $financePage->assertSeeText('Pembelian Stok APAR');
        $financePage->assertSeeText('Menu Pengeluaran Stok');
        $financePage->assertSeeText('Pembelian stok APAR untuk APAR Powder 2 kg sebanyak 10 Unit.');
    }
}
