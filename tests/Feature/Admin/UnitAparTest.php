<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\UnitApar;
use App\Models\User;
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
        $response->assertSee('Monitoring APAR');
        $response->assertSee('Pantau kelayakan dan masa berlaku unit APAR milik pelanggan.');
        $response->assertSee('Registrasi APAR');
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

    public function test_admin_can_register_unit_apar_from_monitoring_page(): void
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
        $response->assertSessionHas('success', 'Unit APAR berhasil diregistrasikan.');

        $unit = UnitApar::query()->first();

        $this->assertNotNull($unit);
        $this->assertNotEmpty($unit->no_seri);
        $this->assertSame($pelanggan->id, $unit->pelanggan_id);
        $this->assertSame($produk->id, $unit->produk_id);
        $this->assertSame('layak', $unit->kondisi_awal);
        $this->assertSame('Unit lobby utama', $unit->catatan_unit);
        $this->assertSame('2026-05-01', $unit->tgl_produksi->toDateString());
        $this->assertSame(
            UnitApar::calculateExpiry('2026-05-01', '6 Kg', 'Dry Chemical Powder')->toDateString(),
            $unit->tgl_expired->toDateString()
        );
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

    public function test_admin_can_delete_unit_apar_from_monitoring_page(): void
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
        $response->assertSessionHas('success', 'Unit APAR berhasil dihapus.');
        $this->assertDatabaseMissing('unit_apars', ['id' => $unit->id]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111180',
        ]);
    }

    private function createFixture(): array
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'PT Pelanggan Uji',
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

        return compact('pelanggan', 'produk');
    }
}
