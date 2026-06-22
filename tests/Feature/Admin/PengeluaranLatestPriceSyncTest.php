<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\ServicePaket;
use App\Models\StokBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranLatestPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_peralatan_purchase_updates_master_price_and_next_reference(): void
    {
        $admin = $this->createAdmin();

        $peralatan = Peralatan::create([
            'nama' => 'Valve APAR',
            'stok' => 8,
            'stok_minimum' => 3,
            'harga_standar' => 50000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'peralatan_id' => $peralatan->id,
            'qty' => 2,
            'harga_beli' => 55000,
            'tanggal' => '2026-06-21',
            'keterangan' => 'Pembelian peralatan terbaru',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));
        $this->assertSame(55000.0, (float) $peralatan->fresh()->harga_standar);

        $page = $this->actingAs($admin)->get(route('admin.pengeluaran.index', [
            'open' => 1,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
        ]));

        $page->assertOk();
        $peralatans = $page->viewData('peralatans');
        $this->assertSame(55000.0, (float) $peralatans->firstWhere('id', $peralatan->id)->harga_standar);
    }

    public function test_refill_purchase_updates_master_price_and_next_reference(): void
    {
        $admin = $this->createAdmin();

        $jenisRefill = JenisRefill::create([
            'nama' => 'Dry Powder',
            'stok' => 100,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
            'jenis_refill_id' => $jenisRefill->id,
            'qty' => 5,
            'harga_beli' => 120000,
            'tanggal' => '2026-06-21',
            'keterangan' => 'Pembelian refill supplier baru',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));
        $this->assertSame(120000.0, (float) $jenisRefill->fresh()->harga);

        $page = $this->actingAs($admin)->get(route('admin.pengeluaran.index', [
            'open' => 1,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
        ]));

        $page->assertOk();
        $jenisRefills = $page->viewData('jenisRefills');
        $this->assertSame(120000.0, (float) $jenisRefills->firstWhere('id', $jenisRefill->id)->harga);
    }

    public function test_apar_purchase_updates_admin_purchase_reference_without_changing_catalog_price(): void
    {
        $admin = $this->createAdmin();
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 Kg',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 1500000,
            'stok' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $produk->id,
            'qty' => 3,
            'harga_beli' => 1250000,
            'tanggal' => '2026-06-21',
            'tgl_produksi_apar' => '2026-06-01',
            'keterangan' => 'Pembelian APAR dengan harga acuan terbaru',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));
        $this->assertSame(1500000.0, (float) $produk->fresh()->harga);

        $expensePage = $this->actingAs($admin)->get(route('admin.pengeluaran.index', [
            'open' => 1,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
        ]));

        $expensePage->assertOk();
        $expensePage->assertSeeText('Harga Acuan Saat Ini');
        $this->assertSame(
            1250000.0,
            (float) $expensePage->viewData('productPurchaseReferencePrices')->get($produk->id)
        );

        $productPage = $this->actingAs($admin)->get(route('admin.produk.index'));

        $productPage->assertOk();
        $productPage->assertSeeText('Acuan beli terakhir: Rp 1.250.000');
        $this->assertSame(
            1250000.0,
            (float) $productPage->viewData('productPurchaseReferencePrices')->get($produk->id)
        );
    }

    public function test_apar_purchase_is_blocked_when_product_is_near_expiry(): void
    {
        Carbon::setTestNow('2026-06-22');

        $admin = $this->createAdmin();
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 Kg',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 1500000,
            'stok' => 5,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => '2025-06-26',
            'tgl_expired' => '2026-06-26',
            'keterangan' => 'Stok hampir expired',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.pengeluaran.index'))
            ->post(route('admin.pengeluaran.store'), [
                'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
                'produk_id' => $produk->id,
                'qty' => 3,
                'harga_beli' => 1250000,
                'tanggal' => '2026-06-22',
                'tgl_produksi_apar' => '2026-06-22',
                'keterangan' => 'Tidak boleh lolos karena hampir expired',
            ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));
        $response->assertSessionHasErrors('produk_id');
        $this->assertDatabaseCount('pengeluarans', 0);
        $this->assertSame(5, (int) $produk->fresh()->stok);
        $this->assertSame(5, (int) $produk->fresh()->stok_tersedia);
    }

    public function test_apar_purchase_on_safe_product_keeps_active_expiry_and_syncs_stock(): void
    {
        Carbon::setTestNow('2026-06-22');

        $admin = $this->createAdmin();
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 Kg',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 1500000,
            'stok' => 5,
        ]);

        $batch = StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => '2026-06-20',
            'tgl_expired' => '2027-06-20',
            'keterangan' => 'Stok aman aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $produk->id,
            'qty' => 3,
            'harga_beli' => 1250000,
            'tanggal' => '2026-06-22',
            'tgl_produksi_apar' => '2026-06-22',
            'keterangan' => 'Tambah stok aman',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));

        $produk->refresh();
        $batch->refresh();

        $this->assertSame(8, (int) $produk->stok);
        $this->assertSame(8, (int) $produk->stok_tersedia);
        $this->assertSame(8, (int) $batch->sisa_qty);
        $this->assertSame(8, (int) $batch->jumlah_masuk);
        $this->assertSame('2027-06-20', $batch->tgl_expired?->toDateString());
        $this->assertSame(1, StokBatch::query()->where('produk_id', $produk->id)->count());
    }

    public function test_stock_purchase_sync_does_not_change_service_package_price(): void
    {
        $admin = $this->createAdmin();

        $servicePaket = ServicePaket::create([
            'nama' => 'Service Ringan Khusus',
            'label' => 'Ringan Khusus',
            'harga' => 35000,
            'jenis_refill_id' => null,
            'refill_ratio' => 0,
            'rincian_layanan' => 'Tes harga jasa',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Foam',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Foam 3 Kg',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Kantor',
            'harga' => 900000,
            'stok' => 0,
        ]);

        $jenisRefill = JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 20,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 5,
        ]);

        $peralatan = Peralatan::create([
            'nama' => 'Valve APAR',
            'stok' => 10,
            'stok_minimum' => 3,
            'harga_standar' => 50000,
        ]);

        $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $produk->id,
            'qty' => 1,
            'harga_beli' => 850000,
            'tanggal' => '2026-06-21',
            'tgl_produksi_apar' => '2026-06-01',
        ])->assertRedirect(route('admin.pengeluaran.index'));

        $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
            'jenis_refill_id' => $jenisRefill->id,
            'qty' => 3,
            'harga_beli' => 110000,
            'tanggal' => '2026-06-21',
        ])->assertRedirect(route('admin.pengeluaran.index'));

        $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'peralatan_id' => $peralatan->id,
            'qty' => 2,
            'harga_beli' => 53000,
            'tanggal' => '2026-06-21',
        ])->assertRedirect(route('admin.pengeluaran.index'));

        $this->assertSame(35000.0, (float) $servicePaket->fresh()->harga);
    }

    public function test_pengeluaran_page_keeps_modal_trigger_and_valid_selection_watchers(): void
    {
        $admin = $this->createAdmin();

        $page = $this->actingAs($admin)->get(route('admin.pengeluaran.index', [
            'open' => 1,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
        ]));

        $page->assertOk();
        $page->assertSeeText('Tambah Pengeluaran');
        $page->assertSee('onclick="openPengeluaranModal()"', false);
        $page->assertSee("this.jenisPengeluaran === '".Pengeluaran::JENIS_PEMBELIAN_APAR."'", false);
        $page->assertSee("this.jenisPengeluaran === '".Pengeluaran::JENIS_PEMBELIAN_REFILL."'", false);
        $page->assertSee("this.jenisPengeluaran === '".Pengeluaran::JENIS_PEMBELIAN_PERALATAN."'", false);
        $page->assertDontSee("if (\r\n                            && this.jenisPengeluaran", false);
        $page->assertDontSee("if (\n                            && this.jenisPengeluaran", false);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111119951',
        ]);
    }
}
