<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductCatalogPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_public_product_card_shows_structured_information_and_stock_state(): void
    {
        Carbon::setTestNow('2026-06-22');

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
            'tgl_produksi' => '2026-06-20',
            'tgl_expired' => '2027-06-20',
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
        $response->assertSeeText('Masa Berlaku:');
        $response->assertSeeText('20 Juni 2027');
        $response->assertSeeText('Sisa:');
        $response->assertSeeText('11 bulan');
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
        Carbon::setTestNow('2026-06-22');

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
            'tgl_produksi' => '2026-06-20',
            'tgl_expired' => '2027-06-20',
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
        $response->assertSeeText('Masa Berlaku');
        $response->assertSeeText('Sisa');
        $response->assertSeeText('Status');
        $response->assertSeeText('20 Juni 2027');
        $response->assertSeeText('11 bulan');
        $response->assertSeeText('Fungsi');
        $response->assertSeeText('8 unit tersedia');
        $response->assertSeeText('Rp 956.000');
        $response->assertSeeText('Jumlah');
        $response->assertSeeText('Masukkan Keranjang');
        $response->assertSeeText('Pesan Sekarang');
        $response->assertSeeText('Tanya Produk via WhatsApp');
        $response->assertDontSeeText('Tanggal Produksi');
        $response->assertDontSeeText('Ringkasan');
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

    public function test_catalog_filters_show_unique_type_option_and_support_brand_type_size_combination(): void
    {
        Carbon::setTestNow('2026-06-22');

        $powder = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $co2 = JenisApar::create([
            'nama' => 'Carbon Dioxide (CO2)',
            'deskripsi' => 'Elektrikal',
        ]);

        $targetProduct = Produk::create([
            'nama' => 'APAR FIREFIX Powder 3 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $powder->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Gudang',
            'harga' => 700000,
        ]);

        $otherSize = Produk::create([
            'nama' => 'APAR FIREFIX Powder 6 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $powder->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 900000,
        ]);

        $otherType = Produk::create([
            'nama' => 'APAR FIREFIX CO2 3 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $co2->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Ruang panel',
            'harga' => 950000,
        ]);

        foreach ([$targetProduct, $otherSize, $otherType] as $produk) {
            StokBatch::create([
                'produk_id' => $produk->id,
                'jumlah_masuk' => 5,
                'sisa_qty' => 5,
                'tgl_produksi' => '2026-06-20',
                'tgl_expired' => '2027-06-20',
                'keterangan' => 'Stok aman untuk filter',
            ]);
        }

        $allProductsPage = $this->get(route('produk.index'));

        $allProductsPage->assertOk();
        $this->assertSame(
            1,
            substr_count($allProductsPage->getContent(), '>Dry Chemical Powder</option>')
        );

        $filteredPage = $this->get(route('produk.index', [
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $powder->id,
            'ukuran' => '3 kg',
        ]));

        $filteredPage->assertOk();
        $filteredPage->assertSeeText('APAR FIREFIX Powder 3 kg');
        $filteredPage->assertDontSeeText('APAR FIREFIX Powder 6 kg');
        $filteredPage->assertDontSeeText('APAR FIREFIX CO2 3 kg');
    }

    public function test_near_expiry_product_is_hidden_from_catalog_until_admin_updates_its_expiry(): void
    {
        Carbon::setTestNow('2026-06-22');

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR TONATA Powder 2 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 618000,
        ]);

        $batch = StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 7,
            'sisa_qty' => 7,
            'tgl_produksi' => '2025-06-26',
            'tgl_expired' => '2026-06-26',
            'keterangan' => 'Batch hampir expired',
        ]);

        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        $catalogBefore = $this->get(route('produk.index'));
        $catalogBefore->assertOk();
        $catalogBefore->assertDontSeeText('APAR TONATA Powder 2 kg');

        $this->actingAs($user)
            ->from(route('produk.index'))
            ->post(route('keranjang.store'), [
                'produk_id' => $produk->id,
                'qty' => 1,
            ])
            ->assertRedirect(route('produk.index'))
            ->assertSessionHas('error');

        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111188',
        ]);

        JenisRefill::create([
            'nama' => 'Dry Chemical Powder',
            'stok' => 20,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stok.batch.refill', $batch), [
                'batch_id_refill' => $batch->id,
                'tanggal_refill' => '2026-06-22',
                'keterangan' => 'Perbaruan masa berlaku untuk katalog',
            ])
            ->assertRedirect(route('admin.stok.index', ['tab' => 'apar', 'filter' => 'masa-berlaku']));

        $catalogAfter = $this->get(route('produk.index'));
        $catalogAfter->assertOk();
        $catalogAfter->assertSeeText('APAR TONATA Powder 2 kg');
        $this->assertSame('2027-06-22', $batch->fresh()->tgl_expired?->toDateString());
        $this->assertSame(7, (int) $batch->fresh()->sisa_qty);
    }

    public function test_product_with_expired_active_stock_is_hidden_from_public_catalog_until_admin_updates_it(): void
    {
        Carbon::setTestNow('2026-06-22');

        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111177',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR FIREFIX Powder 1 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Perkantoran, rumah, kendaraan, gudang',
            'harga' => 181624,
            'deskripsi' => 'Produk 1 kg untuk kendaraan.',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 3,
            'sisa_qty' => 3,
            'tgl_produksi' => '2025-10-11',
            'tgl_expired' => '2026-04-11',
            'keterangan' => 'Batch lama yang sudah expired',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 6,
            'sisa_qty' => 6,
            'tgl_produksi' => '2026-03-11',
            'tgl_expired' => '2026-09-11',
            'keterangan' => 'Batch aman paling cepat habis',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => '2026-06-15',
            'tgl_expired' => '2026-12-15',
            'keterangan' => 'Batch aman berikutnya',
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 3,
            'sisa_qty' => 3,
            'tgl_produksi' => '2026-06-17',
            'tgl_expired' => '2026-12-17',
            'keterangan' => 'Batch aman paling akhir',
        ]);

        $stockPage = $this->actingAs($admin)->get(route('admin.stok.index', [
            'tab' => 'apar',
            'filter' => 'semua',
        ]));

        $stockPage->assertOk();
        $stockPage->assertSeeText('APAR FIREFIX Powder 1 kg');
        $stockPage->assertSeeText('17 unit');
        $stockPage->assertSeeText('11 April 2026');
        $stockPage->assertSeeText('Expired');

        $catalogPage = $this->get(route('produk.index', [
            'jenis_apar_id' => $jenisApar->id,
            'ukuran' => '1 kg',
        ]));

        $catalogPage->assertOk();
        $catalogPage->assertDontSeeText('APAR FIREFIX Powder 1 kg');
    }
}
