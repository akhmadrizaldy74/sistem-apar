<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_laporan_use_same_paid_revenue_values(): void
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
        $laporanCharts = $laporan->viewData('charts');
        $laporanSummary = $laporan->viewData('summary');
        $laporanCombinedData = $laporan->viewData('combinedData');
        $keuanganCharts = $keuangan->viewData('charts');
        $keuanganTotals = $keuangan->viewData('totals');
        $penjualan = $this->actingAs($admin)->get(route('admin.laporan.penjualan'));

        $penjualan->assertOk();

        $penjualanTransactions = $penjualan->viewData('transactions');

        $this->assertSame(3400000.0, (float) $dashboardKpis['pendapatanBulanIni']);
        $this->assertSame(3800000.0, (float) $dashboardKpis['pendapatanKeseluruhan']);
        $this->assertSame([1500000.0, 1200000.0, 1100000.0], array_map('floatval', $dashboardCharts['revenueComposition']['series']));
        $this->assertSame(3800000.0, (float) $laporanSummary['totalPemasukan']);
        $this->assertSame(3800000.0, (float) $keuanganTotals['total_pemasukan']);
        $this->assertSame($dashboardCharts['revenueComposition']['labels'], $laporanCharts['revenueComposition']['labels']);
        $this->assertSame($dashboardCharts['revenueComposition']['colors'], $laporanCharts['revenueComposition']['colors']);
        $this->assertSame(
            array_map('floatval', $dashboardCharts['revenueComposition']['series']),
            array_map('floatval', $laporanCharts['revenueComposition']['series'])
        );
        $this->assertSame($dashboardCharts['unitStatus']['labels'], $laporanCharts['unitStatus']['labels']);
        $this->assertSame($dashboardCharts['unitStatus']['colors'], $laporanCharts['unitStatus']['colors']);
        $this->assertSame(
            array_map('intval', $dashboardCharts['unitStatus']['series']),
            array_map('intval', $laporanCharts['unitStatus']['series'])
        );
        $this->assertSame($dashboardCharts['revenueComposition']['labels'], $keuanganCharts['revenueComposition']['labels']);
        $this->assertSame($dashboardCharts['revenueComposition']['colors'], $keuanganCharts['revenueComposition']['colors']);
        $this->assertSame(
            array_map('floatval', $dashboardCharts['revenueComposition']['series']),
            array_map('floatval', $keuanganCharts['revenueComposition']['series'])
        );
        $this->assertSame(
            (float) $laporanSummary['totalPemasukan'],
            array_sum(array_map('floatval', $dashboardCharts['revenueComposition']['series']))
        );
        $this->assertTrue($laporanCombinedData->contains(fn (array $row) => $row['jenis'] === 'Refill' && (float) $row['pemasukan'] === 400000.0));
        $this->assertTrue($laporanCombinedData->contains(fn (array $row) => $row['jenis'] === 'Refill' && (float) $row['pemasukan'] === 700000.0));
        $this->assertCount(3, $penjualanTransactions);
        $this->assertTrue($penjualanTransactions->contains(fn (array $row) => $row['jenis_transaksi'] === 'Penjualan Produk' && (float) $row['total'] === 1500000.0));
        $this->assertTrue($penjualanTransactions->contains(fn (array $row) => $row['jenis_transaksi'] === 'Refill APAR' && (float) $row['total'] === 400000.0));
        $this->assertTrue($penjualanTransactions->contains(fn (array $row) => $row['jenis_transaksi'] === 'Refill APAR' && (float) $row['total'] === 700000.0));
        $dashboard->assertSeeText('Total Pendapatan');
        $dashboard->assertSeeText('Bulan Ini');
        $dashboard->assertSeeText('Keseluruhan');
        $dashboard->assertSee('data-revenue-period', false);
        $dashboard->assertSee('data-month-value="Rp 3.400.000"', false);
        $dashboard->assertSee('data-overall-value="Rp 3.800.000"', false);
        $dashboard->assertDontSeeText('Prioritas');
        $dashboard->assertDontSeeText('Pesanan Menunggu');
        $dashboard->assertDontSeeText('Unit Akan Expired');
        $dashboard->assertDontSeeText('Unit Expired');
        $dashboard->assertDontSeeText('Semua transaksi utama dalam kondisi aman');
    }

    public function test_dashboard_revenue_defaults_to_zero_when_no_paid_transactions_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111141',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $serviceReport = $this->actingAs($admin)->get(route('admin.laporan.service'));

        $response->assertOk();
        $serviceReport->assertOk();

        $kpis = $response->viewData('kpis');
        $charts = $response->viewData('charts');

        $this->assertSame(0.0, (float) $kpis['pendapatanBulanIni']);
        $this->assertSame(0.0, (float) $kpis['pendapatanKeseluruhan']);
        $this->assertSame([0.0, 0.0, 0.0], array_map('floatval', $charts['revenueComposition']['series']));
        $response->assertSeeText('Total Pendapatan');
        $response->assertSeeText('Bulan Ini');
        $response->assertSeeText('Keseluruhan');
        $response->assertSee('data-revenue-period', false);
        $response->assertSee('data-month-value="Rp 0"', false);
        $response->assertSee('data-overall-value="Rp 0"', false);
        $response->assertDontSeeText('Pendapatan Final Bulan Ini');
        $response->assertDontSeeText('Prioritas');
        $response->assertDontSeeText('Pesanan Menunggu');
        $response->assertDontSeeText('Unit Akan Expired');
        $response->assertDontSeeText('Unit Expired');
        $response->assertDontSeeText('Semua transaksi utama dalam kondisi aman');
        $response->assertSeeText('Peringatan Stok');
        $response->assertSeeText('Semua stok dalam kondisi aman');
        $this->assertFalse((bool) $response->viewData('stockAlerts')['hasIssues']);
    }

    public function test_dashboard_shows_stock_warnings_for_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111144',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produkLow = Produk::create([
            'nama' => 'APAR Powder 3 Kg',
            'merek' => 'SAFECO',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 Kg',
            'penggunaan' => 'Gudang',
            'harga' => 800000,
            'stok' => 3,
        ]);

        Produk::create([
            'nama' => 'APAR CO2 5 Kg',
            'merek' => 'SAFECO',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '5 Kg',
            'penggunaan' => 'Panel listrik',
            'harga' => 1200000,
            'stok' => 0,
        ]);

        StokBatch::create([
            'produk_id' => $produkLow->id,
            'jumlah_masuk' => 3,
            'sisa_qty' => 3,
            'tgl_produksi' => now()->subMonth()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Stok uji dashboard',
        ]);

        JenisRefill::create([
            'nama' => 'Dry Powder',
            'stok' => 4,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 5,
        ]);

        JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 0,
            'satuan' => 'kg',
            'harga' => 120000,
            'stok_minimum' => 5,
        ]);

        Peralatan::create([
            'nama' => 'Pressure Gauge APAR',
            'stok' => 2,
            'stok_minimum' => 3,
            'harga_standar' => 20000,
        ]);

        Peralatan::create([
            'nama' => 'Safety Pin APAR',
            'stok' => 0,
            'stok_minimum' => 3,
            'harga_standar' => 10000,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Peringatan Stok');
        $response->assertSeeText('APAR Powder 3 Kg');
        $response->assertSeeText('APAR CO2 5 Kg');
        $response->assertSeeText('Powder');
        $response->assertSeeText('Foam');
        $response->assertSeeText('Pressure Gauge APAR');
        $response->assertSeeText('Safety Pin APAR');

        $stockAlerts = $response->viewData('stockAlerts');

        $this->assertTrue((bool) $stockAlerts['hasIssues']);
        $this->assertSame(6, (int) $stockAlerts['totalIssueCount']);
        $this->assertSame(3, (int) $stockAlerts['totalEmptyCount']);
        $this->assertSame(3, (int) $stockAlerts['totalLowCount']);
        $this->assertCount(3, $stockAlerts['groups']);
    }

    public function test_dashboard_ignores_empty_equipment_alias_when_canonical_stock_is_available(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111145',
        ]);

        Peralatan::create([
            'nama' => 'Safety Pin APAR',
            'stok' => 201,
            'stok_minimum' => 3,
            'harga_standar' => 10000,
        ]);

        Peralatan::create([
            'nama' => 'Safety Pin (Pin Pengaman)',
            'stok' => 0,
            'stok_minimum' => 20,
            'harga_standar' => 10000,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Semua stok dalam kondisi aman');
        $response->assertDontSeeText('Safety Pin (Pin Pengaman)');
        $this->assertFalse((bool) $response->viewData('stockAlerts')['hasIssues']);
    }

    public function test_dashboard_monthly_purchase_chart_uses_fallback_when_no_purchase_expense_exists(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111142',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Grafik Pembelian Bulanan');

        $monthlyPurchases = $response->viewData('charts')['monthlyPurchases'];

        $this->assertTrue((bool) $monthlyPurchases['isFallback']);
        $this->assertSame(12, count($monthlyPurchases['labels']));
        $this->assertSame(12, count($monthlyPurchases['shortLabels']));
        $this->assertSame('Des', $monthlyPurchases['shortLabels'][11]);
        $this->assertSame(500000.0, (float) $monthlyPurchases['series'][0]);
        $this->assertSame(1800000.0, (float) $monthlyPurchases['series'][11]);
    }

    public function test_dashboard_monthly_purchase_chart_uses_saved_purchase_expense_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111143',
        ]);

        Pengeluaran::create([
            'kategori' => 'lainnya',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'nama_item' => 'APAR 3 Kg',
            'qty' => 1,
            'satuan' => 'Unit',
            'harga_beli' => 150000,
            'total' => 150000,
            'nominal' => 150000,
            'tanggal' => now()->startOfYear()->addDays(4)->toDateString(),
        ]);

        Pengeluaran::create([
            'kategori' => 'refill',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
            'nama_item' => 'Dry Powder',
            'qty' => 2,
            'satuan' => 'Kg',
            'harga_beli' => 125000,
            'total' => 250000,
            'nominal' => 0,
            'tanggal' => now()->startOfYear()->addMonths(5)->addDays(2)->toDateString(),
        ]);

        Pengeluaran::create([
            'kategori' => 'peralatan',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'nama_item' => 'Selang',
            'qty' => 1,
            'satuan' => 'Unit',
            'harga_beli' => 300000,
            'total' => 300000,
            'nominal' => 300000,
            'tanggal' => now()->copy()->subYear()->startOfYear()->addMonths(9)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();

        $monthlyPurchases = $response->viewData('charts')['monthlyPurchases'];

        $this->assertFalse((bool) $monthlyPurchases['isFallback']);
        $this->assertSame('Januari', $monthlyPurchases['labels'][0]);
        $this->assertSame('Jan', $monthlyPurchases['shortLabels'][0]);
        $this->assertSame(150000.0, (float) $monthlyPurchases['series'][0]);
        $this->assertSame(250000.0, (float) $monthlyPurchases['series'][5]);
        $this->assertSame(0.0, (float) $monthlyPurchases['series'][11]);
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
