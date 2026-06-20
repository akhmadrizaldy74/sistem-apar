<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductCatalogPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_product_card_shows_structured_information_and_stock_state(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $produkTersedia = Produk::create([
            'nama' => 'APAR TONATA Powder 1 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Perkantoran, rumah, kendaraan, gudang',
            'harga' => 465000,
            'deskripsi' => 'Produk untuk mobil dan kantor.',
        ]);

        $produkHabis = Produk::create([
            'nama' => 'APAR TONATA Powder 2 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Perkantoran, rumah, kendaraan, gudang',
            'harga' => 618000,
            'deskripsi' => 'Produk cadangan.',
        ]);

        StokBatch::create([
            'produk_id' => $produkTersedia->id,
            'jumlah_masuk' => 15,
            'sisa_qty' => 15,
            'tgl_produksi' => now()->subMonth(),
            'tgl_expired' => now()->addYear(),
            'keterangan' => 'Batch aktif',
        ]);

        $response = $this->get(route('produk.index'));

        $response->assertOk();
        $response->assertSeeText('APAR TONATA Powder 1 kg');
        $response->assertSeeText('TONATA');
        $response->assertSeeText('Jenis:');
        $response->assertSeeText('Ukuran:');
        $response->assertSeeText('Fungsi:');
        $response->assertSeeText('Stok:');
        $response->assertSeeText('15 unit tersedia');
        $response->assertSeeText('TERSEDIA');
        $response->assertSeeText('HABIS');
        $response->assertSeeText('Stok Habis');
        $response->assertSeeText('Rp 465.000');
        $response->assertSeeText('Keranjang');
        $response->assertSeeText('Detail');
        $response->assertDontSee('bg-slate-50/80', false);
    }

    public function test_public_product_detail_shows_complete_information_layout(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Carbon Dioxide (CO2)',
            'deskripsi' => 'Elektrikal',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR TONATA CO2 2 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Ruang server, panel listrik, laboratorium',
            'harga' => 956000,
            'deskripsi' => 'Aman untuk perangkat elektronik.',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 8,
            'sisa_qty' => 8,
            'tgl_produksi' => now()->subWeeks(2),
            'tgl_expired' => now()->addMonths(10),
            'keterangan' => 'Batch CO2 aktif',
        ]);

        $response = $this->get(route('produk.show', $produk));

        $response->assertOk();
        $response->assertSeeText('APAR TONATA CO2 2 kg');
        $response->assertSeeText('Kembali ke Produk');
        self::assertSame(1, substr_count($response->getContent(), 'Kembali ke Produk'));
        $response->assertSeeText('Merek');
        $response->assertSeeText('Jenis');
        $response->assertSeeText('Ukuran');
        $response->assertSeeText('Stok');
        $response->assertSeeText('Fungsi');
        $response->assertSeeText('Ringkasan');
        $response->assertSeeText('8 unit tersedia');
        $response->assertSeeText('Rp 956.000');
        $response->assertSeeText('Jumlah');
        $response->assertSeeText('Masukkan Keranjang');
        $response->assertSeeText('Pesan Sekarang');
        $response->assertSeeText('Tanya Produk via WhatsApp');
    }

    public function test_customer_can_still_add_product_to_cart_from_catalog_flow(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR FIREFIX Powder 3 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Perkantoran, rumah',
            'harga' => 300699,
            'deskripsi' => 'Produk untuk kebutuhan kantor.',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 6,
            'sisa_qty' => 6,
            'tgl_produksi' => now()->subMonth(),
            'tgl_expired' => now()->addYear(),
            'keterangan' => 'Batch aktif untuk keranjang',
        ]);

        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($user)->from(route('produk.index'))->post(route('keranjang.store'), [
            'produk_id' => $produk->id,
            'qty' => 1,
        ]);

        $response->assertRedirect(route('produk.index'));
        $response->assertSessionHas('success');

        $keranjangPage = $this->actingAs($user)->get(route('keranjang.index'));

        $keranjangPage->assertOk();
        $keranjangPage->assertSeeText('APAR FIREFIX Powder 3 kg');
        $keranjangPage->assertSeeText('Rp 300.699');
    }
}
