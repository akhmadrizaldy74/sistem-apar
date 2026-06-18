<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\FinalTransactionStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitAparTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_simplified_unit_apar_monitoring_page(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture();

        $activeUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-APAR-001',
            'tgl_beli' => now()->subMonths(2)->toDateString(),
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(8)->toDateString(),
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-APAR-002',
            'tgl_beli' => now()->subMonths(11)->toDateString(),
            'tgl_produksi' => now()->subMonths(11)->toDateString(),
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addDays(10)->toDateString(),
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-APAR-003',
            'tgl_beli' => now()->subYear()->toDateString(),
            'tgl_produksi' => now()->subYear()->toDateString(),
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->subDay()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.index'));

        $response->assertOk();
        $response->assertSee('Unit APAR');
        $response->assertSee('Kelola dan pantau unit APAR pelanggan yang terhubung dengan transaksi pembelian, refill, dan service.');
        $response->assertDontSee('Registrasi Manual');
        $response->assertSee('Lihat Detail');
        $response->assertSee('Hapus');
        $response->assertDontSee('Cetak Laporan');
        $response->assertDontSee('Kelompok per Pelanggan');
        $response->assertDontSee('Ingatkan');
        $response->assertDontSee(route('admin.unit-apar.edit', $activeUnit), false);

        $summary = $response->viewData('summary');
        $this->assertSame(3, $summary['total']);
        $this->assertSame(1, $summary['aktif']);
        $this->assertSame(1, $summary['hampir']);
        $this->assertSame(1, $summary['expired']);
    }

    public function test_admin_cannot_register_unit_apar_manually_from_monitoring_page(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture();

        $response = $this->actingAs($admin)->post(route('admin.unit-apar.store'), [
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'tgl_produksi' => '2026-05-01',
            'tgl_beli' => '2026-06-08',
            'kondisi_awal' => 'layak',
            'catatan_unit' => 'Unit lobby utama',
        ]);

        $response->assertRedirect(route('admin.unit-apar.index'));
        $response->assertSessionHas('error', 'Registrasi manual unit APAR dinonaktifkan. Unit dibuat otomatis dari transaksi pelanggan yang selesai final.');
        $this->assertDatabaseCount('unit_apars', 0);
    }

    public function test_admin_can_view_read_only_unit_apar_detail(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture();

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-DETAIL-001',
            'tgl_beli' => now()->subMonths(3)->toDateString(),
            'tgl_produksi' => now()->subMonths(3)->toDateString(),
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'lokasi_unit' => 'Gudang utama',
            'catatan_unit' => 'Segel baru diganti',
            'tgl_expired' => now()->addMonths(9)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.show', $unit));

        $response->assertOk();
        $response->assertSee('Detail Unit APAR');
        $response->assertSee('UT-DETAIL-001');
        $response->assertSee('PT Pelanggan Uji');
        $response->assertSee('081234567899');
        $response->assertSee('APAR Powder 6 Kg');
        $response->assertSee('Dry Chemical Powder');
        $response->assertSee('Gudang utama');
        $response->assertSee('Segel baru diganti');
        $response->assertDontSee(route('admin.unit-apar.edit', $unit), false);
    }

    public function test_expiry_rule_uses_six_months_only_for_one_kg_units(): void
    {
        $this->assertSame('2026-12-14', UnitApar::calculateExpiry('2026-06-14', '1 kg', 'Powder')->toDateString());
        $this->assertSame('2027-06-14', UnitApar::calculateExpiry('2026-06-14', '2 kg', 'Powder')->toDateString());
        $this->assertSame('2027-06-14', UnitApar::calculateExpiry('2026-06-14', '6 kg', 'Foam')->toDateString());
        $this->assertSame('2027-06-14', UnitApar::calculateExpiry('2026-06-14', '9 kg', 'CO2')->toDateString());
    }

    public function test_admin_can_hide_unit_apar_from_monitoring_page(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture();

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-HAPUS-001',
            'tgl_beli' => now()->toDateString(),
            'tgl_produksi' => now()->toDateString(),
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.unit-apar.destroy', $unit));

        $response->assertRedirect(route('admin.unit-apar.index'));
        $response->assertSessionHas('success', 'Unit APAR berhasil disembunyikan dari daftar.');
        $this->assertDatabaseHas('unit_apars', ['id' => $unit->id]);
        $this->assertNotNull($unit->fresh()->hidden_at);

        $indexResponse = $this->actingAs($admin)->get(route('admin.unit-apar.index'));
        $indexResponse->assertOk();
        $indexResponse->assertDontSee('UT-HAPUS-001');
    }

    public function test_unit_apar_filter_only_shows_linked_customer_accounts(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture();

        $dummy = Pelanggan::create([
            'nama' => 'Budi Santoso',
            'no_wa' => '081200000001',
            'status' => 'tetap',
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'AKHMAD-18062026-01',
            'tgl_beli' => '2026-06-18',
            'tgl_produksi' => '2026-06-18',
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => '2027-06-18',
        ]);

        UnitApar::create([
            'pelanggan_id' => $dummy->id,
            'produk_id' => $produk->id,
            'no_seri' => 'DUMMY-18062026-01',
            'tgl_beli' => '2026-06-18',
            'tgl_produksi' => '2026-06-18',
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => '2027-06-18',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.index'));

        $response->assertOk();
        $response->assertSee('PT Pelanggan Uji');
        $response->assertDontSee('Budi Santoso');
        $response->assertDontSee('DUMMY-18062026-01');
    }

    public function test_final_product_order_creates_unit_apar_automatically_with_customer_date_serials(): void
    {
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture(customerName: 'Akhmad Rizaldy');

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => '2026-06-10',
            'tgl_expired' => '2027-06-10',
            'keterangan' => 'Batch unit test',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'produk',
            'status' => 'selesai final',
            'tanggal' => '2026-06-18',
            'total' => 1500000,
            'total_harga' => 1500000,
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 2,
            'harga' => 750000,
            'subtotal' => 1500000,
        ]);

        app(FinalTransactionStockService::class)->apply($pesanan->fresh());

        $units = UnitApar::query()
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('no_seri')
            ->get();

        $this->assertCount(2, $units);
        $this->assertSame('AKHMAD-18062026-01', $units[0]->no_seri);
        $this->assertSame('AKHMAD-18062026-02', $units[1]->no_seri);
        $this->assertSame('2027-06-10', $units[0]->tgl_expired->toDateString());
        $this->assertSame('2027-06-10', $units[1]->tgl_expired->toDateString());
    }

    public function test_hidden_units_are_not_recreated_when_admin_opens_pesanan_page(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture(customerName: 'Akhmad Rizaldy');

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => '2026-06-10',
            'tgl_expired' => '2027-06-10',
            'keterangan' => 'Batch unit test hidden',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'produk',
            'status' => 'selesai final',
            'tanggal' => '2026-06-18',
            'total' => 1500000,
            'total_harga' => 1500000,
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 2,
            'harga' => 750000,
            'subtotal' => 1500000,
        ]);

        app(FinalTransactionStockService::class)->apply($pesanan->fresh());

        $units = UnitApar::query()
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $units);

        $hiddenUnit = $units->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.unit-apar.destroy', $hiddenUnit))
            ->assertRedirect(route('admin.unit-apar.index'));

        $this->assertCount(2, UnitApar::query()->where('pesanan_id', $pesanan->id)->get());
        $this->assertSame(1, UnitApar::query()->visible()->where('pesanan_id', $pesanan->id)->count());
        $this->assertNotNull($hiddenUnit->fresh()->hidden_at);

        $response = $this->actingAs($admin)->get(route('admin.pesanan.index'));

        $response->assertOk();
        $this->assertCount(2, UnitApar::query()->where('pesanan_id', $pesanan->id)->get());
        $this->assertSame(1, UnitApar::query()->visible()->where('pesanan_id', $pesanan->id)->count());
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111180',
        ]);
    }

    private function createFixture(string $customerName = 'PT Pelanggan Uji'): array
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => $customerName,
            'no_telpon' => '081234567899',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $customerName,
            'no_wa' => '081234567899',
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 Kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 Kg',
            'penggunaan' => 'Testing',
            'harga' => 750000,
            'stok' => 10,
        ]);

        return compact('user', 'pelanggan', 'produk');
    }
}
