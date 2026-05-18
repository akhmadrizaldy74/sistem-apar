<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Service;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_check_apar_page_can_be_rendered(): void
    {
        $response = $this->get(route('cek-apar'));

        $response->assertOk();
        $response->assertSee('Status & Riwayat APAR');
    }

    public function test_public_produk_page_can_be_rendered(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        Produk::create([
            'nama' => 'APAR 3 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Perkantoran, rumah',
            'harga' => 350000,
            'deskripsi' => 'Produk demo',
        ]);

        $response = $this->get(route('produk.index'));

        $response->assertOk();
        $response->assertSee('Produk APAR');
    }

    public function test_public_produk_page_can_filter_by_brand(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Perkantoran',
            'harga' => 350000,
            'deskripsi' => 'Produk demo',
        ]);

        Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'ABC',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Perkantoran',
            'harga' => 375000,
            'deskripsi' => 'Produk demo',
        ]);

        $response = $this->get(route('produk.index', ['merek' => 'ABC']));

        $response->assertOk();
        $response->assertSee('ABC');
        $response->assertSee('375.000');
        $response->assertDontSee('350.000');
    }

    public function test_admin_report_hub_can_be_rendered(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.index'));

        $response->assertOk();
        $response->assertSee('Pusat Laporan');
    }

    public function test_admin_can_download_service_report_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Demo',
            'no_wa' => '6281234567890',
            'alamat' => 'Jl Demo',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR 6 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo',
        ]);

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'SN-001',
            'tgl_beli' => now()->toDateString(),
            'tgl_produksi' => now()->toDateString(),
            'ukuran' => '6kg',
            'bahan' => 'Powder',
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        Service::create([
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Inspeksi Visual & Tekanan',
            'tgl_service' => now()->toDateString(),
            'keterangan' => 'Pengecekan tekanan',
            'biaya' => 50000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.service.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_public_check_apar_shows_purchase_history(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'PT Demo',
            'no_wa' => '6281234567890',
            'alamat' => 'Jl Demo',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR 3 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Perkantoran',
            'harga' => 350000,
            'deskripsi' => 'Produk demo',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'total' => 700000,
            'tanggal' => now()->toDateString(),
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => 'SAFETY',
            'kapasitas' => '3 kg',
            'jumlah' => 2,
            'harga' => 350000,
            'subtotal' => 700000,
        ]);

        $response = $this->followingRedirects()->post(route('cek-apar.check'), [
            'no_wa' => '6281234567890',
        ]);

        $response->assertOk();
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('APAR 3 KG');
    }

    public function test_admin_can_create_multi_item_order_and_download_invoice_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'CV Aman',
            'no_wa' => '628777000111',
            'alamat' => 'Jl Aman',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'CO2',
            'deskripsi' => 'CO2',
        ]);

        $produkPertama = Produk::create([
            'nama' => 'APAR CO2',
            'merek' => 'ABC',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '5 kg',
            'penggunaan' => 'Ruang server',
            'harga' => 950000,
            'deskripsi' => 'Produk demo',
        ]);
        StokBatch::create([
            'produk_id' => $produkPertama->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => now()->subMonth()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Stok test',
        ]);

        $produkKedua = Produk::create([
            'nama' => 'APAR CO2',
            'merek' => 'GUARD',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6.8 kg',
            'penggunaan' => 'Ruang server',
            'harga' => 1200000,
            'deskripsi' => 'Produk demo',
        ]);
        StokBatch::create([
            'produk_id' => $produkKedua->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => now()->subMonth()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Stok test',
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.pesanan.store'), [
            'tipe' => 'produk',
            'pelanggan_id' => $pelanggan->id,
            'tanggal' => now()->toDateString(),
            'items' => [
                [
                    'produk_id' => $produkPertama->id,
                    'kapasitas' => '5 kg',
                    'merek' => 'ABC',
                    'jumlah' => 1,
                ],
                [
                    'produk_id' => $produkKedua->id,
                    'kapasitas' => '6.8 kg',
                    'merek' => 'GUARD',
                    'jumlah' => 2,
                ],
            ],
        ]);

        $storeResponse->assertRedirect(route('admin.pesanan.index'));

        $pesanan = Pesanan::with(['details', 'unitApars'])->firstOrFail();
        $this->assertCount(2, $pesanan->details);
        $this->assertCount(3, $pesanan->unitApars);
        $this->assertSame(3350000, (int) $pesanan->total);

        $laporanResponse = $this->actingAs($admin)->get(route('admin.laporan.pesanan'));
        $laporanResponse->assertOk();
        $laporanResponse->assertSee('Laporan Pesanan');
        $laporanResponse->assertSee('CV Aman');

        $pdfResponse = $this->actingAs($admin)->get(route('admin.pesanan.invoice.pdf', $pesanan));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_save_product_order_even_if_there_is_an_empty_row(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Multi Aman',
            'no_wa' => '628222000111',
            'alamat' => 'Jl Industri',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo',
        ]);
        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => now()->subMonth()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Stok test',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pesanan.store'), [
            'tipe' => 'produk',
            'pelanggan_id' => $pelanggan->id,
            'tanggal' => now()->toDateString(),
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'kapasitas' => '6 kg',
                    'merek' => 'SAFETY',
                    'jumlah' => 2,
                ],
                [
                    'produk_id' => '',
                    'kapasitas' => '',
                    'merek' => '',
                    'jumlah' => '',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.pesanan.index'));
        $this->assertDatabaseCount('pesanans', 1);
        $this->assertDatabaseCount('pesanan_details', 1);
        $this->assertDatabaseCount('unit_apars', 2);
    }

    public function test_admin_cannot_create_service_through_pesanan_after_module_split(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Servis Aman',
            'no_wa' => '628111000222',
            'alamat' => 'Jl Teknisi',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo',
        ]);

        $response = $this->from(route('admin.pesanan.index'))
            ->actingAs($admin)
            ->post(route('admin.pesanan.store'), [
                'tipe' => 'service',
                'pelanggan_id' => $pelanggan->id,
                'tanggal' => now()->toDateString(),
                'service' => [
                    'unit_apar_id' => '',
                    'jenis_service' => 'Ganti Selang',
                    'deskripsi' => 'Seal bocor dan perlu pengecekan ulang tekanan',
                    'biaya' => 175000,
                ],
            ]);

        $response->assertRedirect(route('admin.pesanan.index'));
        $response->assertSessionHasErrors('tipe');
        $this->assertDatabaseCount('pesanans', 0);
        $this->assertDatabaseCount('services', 0);
    }

    public function test_admin_can_create_service_from_service_menu(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Service Mandiri',
            'no_wa' => '628111999000',
            'alamat' => 'Jl Service',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo',
        ]);

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'SN-SVC-001',
            'tgl_beli' => now()->subMonths(6)->toDateString(),
            'tgl_produksi' => now()->subMonths(7)->toDateString(),
            'ukuran' => '6 kg',
            'bahan' => 'Powder',
            'tgl_expired' => now()->addMonths(6)->toDateString(),
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.service.store'), [
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Ganti Selang',
            'tgl_service' => now()->toDateString(),
            'keterangan' => 'Selang retak dan diganti sesuai paket service.',
            'biaya' => 150000,
        ]);

        $storeResponse->assertRedirect(route('admin.service.index'));
        $this->assertDatabaseHas('services', [
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Ganti Selang',
            'biaya' => 150000,
        ]);
        $this->assertDatabaseCount('pesanans', 0);
        $this->assertDatabaseCount('refills', 0);

        $servicePage = $this->actingAs($admin)->get(route('admin.service.index'));
        $servicePage->assertOk();
        $servicePage->assertSee('Ganti Selang');
        $servicePage->assertSee('SN-SVC-001');
    }

    public function test_admin_can_create_refill_from_refill_menu(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Refill Mandiri',
            'no_wa' => '628222333444',
            'alamat' => 'Jl Validasi',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'CO2',
            'deskripsi' => 'CO2',
        ]);

        $jenisRefill = JenisRefill::create([
            'nama' => 'CO2',
            'stok' => 10,
            'satuan' => 'kg',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR CO2',
            'merek' => 'GUARD',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '5 kg',
            'penggunaan' => 'Ruang server',
            'harga' => 900000,
            'deskripsi' => 'Produk demo',
        ]);

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'SN-RFL-002',
            'tgl_beli' => now()->subMonths(3)->toDateString(),
            'tgl_produksi' => now()->subMonths(4)->toDateString(),
            'ukuran' => '5 kg',
            'bahan' => 'CO2',
            'tgl_expired' => now()->addMonths(9)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.refill.store'), [
            'unit_apar_id' => $unit->id,
            'jenis_refill_id' => $jenisRefill->id,
            'tgl_refill' => now()->toDateString(),
            'biaya' => 275000,
        ]);

        $response->assertRedirect(route('admin.refill.index'));
        $this->assertDatabaseHas('refills', [
            'unit_apar_id' => $unit->id,
            'jenis_refill_id' => $jenisRefill->id,
            'biaya' => 275000,
        ]);
        $this->assertDatabaseCount('services', 0);
        $this->assertDatabaseCount('pesanans', 0);
    }

    public function test_laporan_pesanan_hanya_menampilkan_pesanan_produk(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelangganProduk = Pelanggan::create([
            'nama' => 'PT Produk',
            'no_wa' => '628222333445',
            'alamat' => 'Jl Produk',
        ]);

        $pelangganService = Pelanggan::create([
            'nama' => 'PT Legacy Service',
            'no_wa' => '628222333446',
            'alamat' => 'Jl Service',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo',
        ]);

        $pesananProduk = Pesanan::create([
            'pelanggan_id' => $pelangganProduk->id,
            'tipe' => 'produk',
            'tanggal' => now()->toDateString(),
            'total' => 500000,
        ]);

        $pesananProduk->details()->create([
            'produk_id' => $produk->id,
            'merek' => 'SAFETY',
            'kapasitas' => '6 kg',
            'jumlah' => 1,
            'harga' => 500000,
            'subtotal' => 500000,
        ]);

        $pesananService = Pesanan::create([
            'pelanggan_id' => $pelangganService->id,
            'tipe' => 'service',
            'tanggal' => now()->toDateString(),
            'total' => 75000,
        ]);

        Service::create([
            'pesanan_id' => $pesananService->id,
            'unit_apar_id' => null,
            'jenis_service' => 'Ganti Segel',
            'tgl_service' => now()->toDateString(),
            'keterangan' => 'Data legacy service.',
            'biaya' => 75000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.pesanan'));

        $response->assertOk();
        $response->assertSee('PT Produk');
        $response->assertDontSee('Ganti Segel');
    }

    public function test_old_product_orders_are_not_auto_synced_into_unit_apar_when_opening_pesanan_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Sinkron Otomatis',
            'no_wa' => '628555000111',
            'alamat' => 'Jl Otomatis',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'CO2',
            'deskripsi' => 'CO2',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR CO2',
            'merek' => 'GUARD',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Panel listrik',
            'harga' => 700000,
            'deskripsi' => 'Produk lama',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'tanggal' => now()->subDays(3)->toDateString(),
            'total' => 1400000,
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => 'GUARD',
            'kapasitas' => '3 kg',
            'jumlah' => 2,
            'harga' => 700000,
            'subtotal' => 1400000,
        ]);

        $this->assertDatabaseCount('unit_apars', 0);

        $response = $this->actingAs($admin)->get(route('admin.pesanan.index'));

        $response->assertOk();
        $this->assertDatabaseCount('unit_apars', 0);
    }

    public function test_teknisi_completion_flow_matches_activity_diagram(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Diagram Flow',
            'no_wa' => '628123450001',
            'alamat' => 'Jl Diagram',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'service',
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 250000,
            'service_jenis_layanan' => 'service',
            'service_estimasi_biaya' => 250000,
            'total_harga' => 250000,
        ]);

        $teknisiResponse = $this->actingAs($teknisi)->post(route('teknisi.tugas.selesai', $pesanan), [
            'catatan' => 'Pekerjaan selesai sesuai laporan teknisi.',
        ]);

        $teknisiResponse->assertRedirect();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            'teknisi_catatan' => 'Pekerjaan selesai sesuai laporan teknisi.',
        ]);

        $adminResponse = $this->actingAs($admin)->post(route('admin.pesanan.konfirmasi-pelanggan', $pesanan));

        $adminResponse->assertRedirect();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
        ]);
    }
}
