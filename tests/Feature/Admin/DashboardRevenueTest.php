<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_laporan_use_same_final_revenue_values(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111140',
        ]);

        [
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'unit' => $unit,
            'jenisRefill' => $jenisRefill,
        ] = $this->createRevenueFixtures();

        $this->createProductOrder($pelanggan, $produk, 'selesai final', 1500000, now()->startOfMonth()->addDays(2));
        $this->createProductOrder($pelanggan, $produk, 'pending', 800000, now()->startOfMonth()->addDays(3));

        $this->createServiceOrder($pelanggan, $unit, 'selesai final', 300000, now()->startOfMonth()->addDays(4));
        $this->createServiceOrder($pelanggan, $unit, 'selesai oleh teknisi', 900000, now()->startOfMonth()->addDays(5));

        $this->createRefillOrder($pelanggan, $unit, $jenisRefill, 'selesai final', 400000, now()->copy()->subMonth()->startOfMonth()->addDays(10));
        $this->createRefillOrder($pelanggan, $unit, $jenisRefill, 'selesai oleh teknisi', 700000, now()->startOfMonth()->addDays(6));

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));
        $laporan = $this->actingAs($admin)->get(route('admin.laporan.index'));
        $keuangan = $this->actingAs($admin)->get(route('admin.laporan.keuangan'));

        $dashboard->assertOk();
        $laporan->assertOk();
        $keuangan->assertOk();

        $dashboardCharts = $dashboard->viewData('charts');
        $dashboardKpis = $dashboard->viewData('kpis');
        $laporanSummary = $laporan->viewData('summary');
        $laporanCombinedData = $laporan->viewData('combinedData');
        $keuanganTotals = $keuangan->viewData('totals');

        $this->assertSame(1800000.0, (float) $dashboardKpis['pendapatanBulanIni']);
        $this->assertSame([1500000.0, 300000.0, 400000.0], array_map('floatval', $dashboardCharts['revenueComposition']['series']));
        $this->assertSame(2200000.0, (float) $laporanSummary['totalPemasukan']);
        $this->assertSame(2200000.0, (float) $keuanganTotals['total_pemasukan']);
        $this->assertSame(
            (float) $laporanSummary['totalPemasukan'],
            array_sum(array_map('floatval', $dashboardCharts['revenueComposition']['series']))
        );
        $this->assertTrue($laporanCombinedData->contains(fn (array $row) => $row['jenis'] === 'Refill' && (float) $row['pemasukan'] === 400000.0));
    }

    public function test_dashboard_revenue_defaults_to_zero_when_no_final_transactions_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111141',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();

        $kpis = $response->viewData('kpis');
        $charts = $response->viewData('charts');

        $this->assertSame(0.0, (float) $kpis['pendapatanBulanIni']);
        $this->assertSame([0.0, 0.0, 0.0], array_map('floatval', $charts['revenueComposition']['series']));
    }

    private function createRevenueFixtures(): array
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'PT Final Revenue',
            'no_wa' => '081234567890',
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR 3 Kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 Kg',
            'penggunaan' => 'Testing',
            'harga' => 1500000,
            'stok' => 10,
        ]);

        $jenisRefill = JenisRefill::create([
            'nama' => 'Dry Powder',
            'stok' => 100,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 5,
        ]);

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-REV-0001',
            'tgl_beli' => now()->subMonths(3)->toDateString(),
            'tgl_produksi' => now()->subMonths(3)->toDateString(),
            'ukuran' => '3 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(9)->toDateString(),
        ]);

        return compact('pelanggan', 'produk', 'unit', 'jenisRefill');
    }

    private function createProductOrder(Pelanggan $pelanggan, Produk $produk, string $status, int $total, Carbon $tanggal): Pesanan
    {
        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'produk',
            'sumber_pesanan' => 'input_admin',
            'total' => $total,
            'total_harga' => $total,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => $status,
            'tanggal' => $tanggal->toDateString(),
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => $total,
            'subtotal' => $total,
        ]);

        return $pesanan;
    }

    private function createServiceOrder(Pelanggan $pelanggan, UnitApar $unit, string $status, int $biaya, Carbon $tanggal): Service
    {
        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'service',
            'sumber_pesanan' => 'input_admin',
            'service_jenis_layanan' => 'service',
            'total' => $biaya,
            'total_harga' => $biaya,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => $status,
            'tanggal' => $tanggal->toDateString(),
        ]);

        return Service::create([
            'pesanan_id' => $pesanan->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Hydrotest',
            'tgl_service' => $tanggal->toDateString(),
            'biaya' => $biaya,
            'status_konfirmasi' => 'confirmed',
        ]);
    }

    private function createRefillOrder(Pelanggan $pelanggan, UnitApar $unit, JenisRefill $jenisRefill, string $status, int $biaya, Carbon $tanggal): Refill
    {
        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'service',
            'sumber_pesanan' => 'input_admin',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'total' => $biaya,
            'total_harga' => $biaya,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => $status,
            'tanggal' => $tanggal->toDateString(),
        ]);

        $service = Service::create([
            'pesanan_id' => $pesanan->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => $tanggal->toDateString(),
            'biaya' => $biaya,
            'status_konfirmasi' => 'confirmed',
        ]);

        return Refill::create([
            'service_id' => $service->id,
            'unit_apar_id' => $unit->id,
            'jenis_refill_id' => $jenisRefill->id,
            'tgl_refill' => $tanggal->toDateString(),
            'biaya' => $biaya,
        ]);
    }
}
