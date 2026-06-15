<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemFlowTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertSee('Laporan Operasional');
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

    public function test_admin_can_create_multi_item_order_and_download_invoice_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('CV Aman', '628777000111', 'Jl Aman');

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
            'stok' => 10,
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
            'stok' => 10,
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

        $pelanggan = $this->createLinkedCustomer('PT Multi Aman', '628222000111', 'Jl Industri');

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
            'stok' => 10,
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

        $pelanggan = $this->createLinkedCustomer('PT Servis Aman', '628111000222', 'Jl Teknisi');

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

        $pelanggan = $this->createLinkedCustomer('PT Service Mandiri', '628111999000', 'Jl Service');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);
        Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Ruang panel',
            'harga' => 825000,
            'deskripsi' => 'Produk demo untuk service offline',
        ]);

        $paket = \App\Models\ServicePaket::create([
            'nama' => 'Ganti Selang',
            'label' => 'ganti_selang',
            'harga' => 150000,
            'deskripsi' => 'Paket ganti selang',
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.service.store'), [
            'pelanggan_id' => $pelanggan->id,
            'service_paket_id' => $paket->id,
            'jenis_apar' => 'Powder',
            'ukuran_apar' => '6 kg',
            'jumlah_unit' => 1,
            'tgl_service' => now()->toDateString(),
            'catatan_admin' => 'Selang retak dan diganti sesuai paket service.',
        ]);

        $storeResponse->assertRedirect(route('admin.service.index'));

        $pesanan = Pesanan::query()->latest('id')->first();
        $this->assertNotNull($pesanan);

        $finalResponse = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $finalResponse->assertRedirect();
        $this->assertDatabaseHas('services', [
            'service_paket_id' => $paket->id,
        ]);
        $this->assertDatabaseCount('pesanans', 1);
        $this->assertDatabaseHas('unit_apars', [
            'pesanan_id' => $pesanan->id,
            'bahan' => 'Powder',
            'ukuran' => '6 kg',
        ]);
        $this->assertDatabaseCount('refills', 0);
    }

    public function test_admin_can_create_refill_from_refill_menu(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Refill Mandiri', '628222333444', 'Jl Validasi');

        $jenisApar = JenisApar::create([
            'nama' => 'CO2',
            'deskripsi' => 'CO2',
        ]);

        $jenisRefill = JenisRefill::create([
            'nama' => 'CO2',
            'stok' => 10,
            'satuan' => 'kg',
            'harga' => 55000,
            'satuan_label' => 'kg',
            'service_price_rules_json' => [
                ['ukuran' => '5 kg', 'harga' => 275000],
            ]
        ]);

        Produk::create([
            'nama' => 'APAR CO2 5 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '5 kg',
            'penggunaan' => 'Ruang panel',
            'harga' => 825000,
            'deskripsi' => 'Produk demo untuk refill offline',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.refill.store'), [
            'pelanggan_id' => $pelanggan->id,
            'jenis_refill_id' => $jenisRefill->id,
            'ukuran_apar' => '5 kg',
            'jumlah_unit' => 1,
            'tgl_refill' => now()->toDateString(),
            'catatan_admin' => 'Pengecekan dan pengisian CO2',
        ]);

        $response->assertRedirect(route('admin.refill.index'));
        $pesanan = Pesanan::query()->latest('id')->first();
        $this->assertNotNull($pesanan);
        $this->assertDatabaseHas('pesanans', [
            'pelanggan_id' => $pelanggan->id,
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
        ]);
        $this->assertDatabaseHas('services', [
            'jenis_service' => 'Refill APAR',
        ]);
        $this->assertDatabaseCount('refills', 0);

        $finalResponse = $this->actingAs($admin)->post(route('admin.refill.update-status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $finalResponse->assertRedirect();
        $this->assertDatabaseHas('refills', [
            'jenis_refill_id' => $jenisRefill->id,
        ]);
        $this->assertDatabaseCount('pesanans', 1);
        $this->assertDatabaseCount('services', 1);
        $this->assertDatabaseCount('refills', 1);
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

    public function test_invoice_synchronization_and_separation_for_different_order_types(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Budi Santoso',
            'no_wa' => '628999988887',
            'alamat' => 'Jl. Kebakaran No. 4',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'DCP',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR DCP 3kg',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Multi-purpose',
            'harga' => 150000,
            'deskripsi' => 'APAR DCP 3kg',
            'stok' => 10,
        ]);

        // 1. Create a Product Order
        $pesananProduk = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'tanggal' => now()->toDateString(),
            'total' => 150000,
            'sumber_pesanan' => 'datang_langsung',
        ]);

        // 2. Create a Refill Order (tipe: 'service', service_jenis_layanan: 'refill')
        $pesananRefill = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'tanggal' => now()->toDateString(),
            'total' => 60000,
            'sumber_pesanan' => 'datang_langsung',
        ]);

        $unitApar = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $pesananRefill->id,
            'produk_id' => $produk->id,
            'no_seri' => 'AUTO-REFILL-123',
            'ukuran' => '3 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_produksi' => now()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        $serviceRefill = Service::create([
            'pesanan_id' => $pesananRefill->id,
            'unit_apar_id' => $unitApar->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => now()->toDateString(),
            'biaya' => 60000,
            'status_konfirmasi' => 'confirmed',
        ]);

        $jenisRefill = JenisRefill::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'DCP',
            'harga_per_kg' => 20000,
        ]);

        $refillLog = Refill::create([
            'service_id' => $serviceRefill->id,
            'unit_apar_id' => $unitApar->id,
            'jenis_refill_id' => $jenisRefill->id,
            'tgl_refill' => now()->toDateString(),
            'biaya' => 60000,
        ]);

        // 3. Create a Service Order (tipe: 'service', service_jenis_layanan: 'service')
        $pesananService = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'tanggal' => now()->toDateString(),
            'total' => 85000,
            'sumber_pesanan' => 'datang_langsung',
        ]);

        $servicePaket = ServicePaket::create([
            'nama' => 'Ganti Hose APAR',
            'label' => 'ganti_hose_apar',
            'harga' => 85000,
            'rincian_layanan' => 'Penggantian hose APAR',
        ]);

        $pesananService->update([
            'service_paket_id' => $servicePaket->id,
        ]);

        $serviceLog = Service::create([
            'pesanan_id' => $pesananService->id,
            'unit_apar_id' => $unitApar->id,
            'service_paket_id' => $servicePaket->id,
            'jenis_service' => 'Ganti Hose APAR',
            'tgl_service' => now()->toDateString(),
            'biaya' => 85000,
            'status_konfirmasi' => 'confirmed',
        ]);

        // Check Product Invoice
        $responseProduk = $this->actingAs($admin)->get(route('invoice.show', $pesananProduk));
        $responseProduk->assertOk();
        $responseProduk->assertSee('Invoice Pesanan Produk');
        $responseProduk->assertDontSee('Invoice Refill APAR');
        $responseProduk->assertDontSee('Invoice Service APAR');

        // Check Refill Invoice
        $responseRefill = $this->actingAs($admin)->get(route('invoice.show', $pesananRefill));
        $responseRefill->assertOk();
        $responseRefill->assertSee('Invoice Refill APAR');
        $responseRefill->assertDontSee('Invoice Service APAR');
        $responseRefill->assertDontSee('Invoice Pesanan Produk');

        // Check Service Invoice
        $responseService = $this->actingAs($admin)->get(route('invoice.show', $pesananService));
        $responseService->assertOk();
        $responseService->assertSee('Invoice Service APAR');
        $responseService->assertDontSee('Invoice Refill APAR');
        $responseService->assertDontSee('Invoice Pesanan Produk');

        // Check that Refill data is isolated from Service queries
        $responseAdminServiceIndex = $this->actingAs($admin)->get(route('admin.service.index'));
        $responseAdminServiceIndex->assertOk();
        // The service logs should display Ganti Hose APAR but NOT Refill APAR
        $responseAdminServiceIndex->assertSee('Ganti Hose APAR');
        $responseAdminServiceIndex->assertDontSee('Refill APAR');
    }

    public function test_admin_order_index_hides_paid_badge_when_status_is_final(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Status Final Admin', '628888777001', 'Jl Final Admin');

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo final admin',
            'stok' => 10,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'tanggal' => now()->toDateString(),
            'total' => 500000,
            'total_harga' => 500000,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'sumber_pesanan' => 'website',
            'bukti_pembayaran' => 'bukti/final-admin.jpg',
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 500000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pesanan.index'));

        $response->assertOk();
        $response->assertSee('SELESAI FINAL');
        $response->assertDontSee('Pembayaran Lunas');
    }

    public function test_final_invoice_hides_payment_status_and_only_shows_final_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Status Final Invoice', '628888777002', 'Jl Final Invoice');

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Produk demo final invoice',
            'stok' => 10,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'tanggal' => now()->toDateString(),
            'total' => 500000,
            'total_harga' => 500000,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'sumber_pesanan' => 'website',
            'bukti_pembayaran' => 'bukti/final-invoice.jpg',
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 500000,
            'subtotal' => 500000,
        ]);

        $response = $this->actingAs($admin)->get(route('invoice.show', $pesanan));

        $response->assertOk();
        $response->assertSee('Selesai Final');
        $response->assertDontSee('LUNAS / PAID');
        $response->assertDontSee('Lunas / Paid');
        $response->assertDontSee('Status Pembayaran');
    }

    private function createLinkedCustomer(string $name, string $phone, string $address): Pelanggan
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: (string) random_int(1000, 9999);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => $name,
            'email' => 'customer-'.$digits.'@example.com',
            'no_telpon' => $phone,
        ]);

        return Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $name,
            'no_wa' => $phone,
            'alamat' => $address,
            'status' => 'tetap',
        ]);
    }
}
