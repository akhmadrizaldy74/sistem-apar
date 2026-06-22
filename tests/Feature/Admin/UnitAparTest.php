<?php

namespace Tests\Feature\Admin;

use App\Models\JenisRefill;
use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
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
            'tgl_expired' => now()->addDays(7)->toDateString(),
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
        $response->assertSee('Daftar unit dibuat lebih ringkas supaya admin cepat mencari, menyaring, dan membuka detail tanpa scroll panjang.');
        $response->assertSee('Total Unit APAR');
        $response->assertSee('Unit Aman');
        $response->assertSee('Unit Expired');
        $response->assertSee('Filter Unit APAR');
        $response->assertSee('Produk');
        $response->assertDontSee('Urutkan');
        $response->assertDontSee('Terbaru');
        $response->assertDontSee('Terlama');
        $response->assertDontSee('Expired terdekat');
        $response->assertSee('Menampilkan');
        $response->assertSee('Nomor Unit');
        $response->assertSee('Pelanggan');
        $response->assertSee('Produk');
        $response->assertSee('Tanggal Masuk');
        $response->assertDontSee('Registrasi Manual');
        $response->assertDontSee('Ringkasan Transaksi');
        $response->assertDontSee('Refill Lama');
        $response->assertDontSee('Pembelian Produk');
        $response->assertDontSee('Unit Lama / Manual');
        $response->assertSee('Lihat Detail');
        $response->assertSee('Hapus');
        $response->assertDontSee(route('admin.unit-apar.edit', $activeUnit), false);
        $this->assertSame(3, $response->viewData('filteredUnitCount'));

        $summary = $response->viewData('summary');
        $this->assertSame(3, $summary['total']);
        $this->assertSame(1, $summary['aktif']);
        $this->assertSame(1, $summary['hampir']);
        $this->assertSame(1, $summary['expired']);
    }

    public function test_admin_unit_apar_index_paginates_unit_list(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture(customerName: 'Akhmad Rizaldy');

        for ($i = 1; $i <= 11; $i++) {
            $tanggal = sprintf('2026-06-%02d', $i);
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelanggan->id,
                'user_id' => $pelanggan->user_id,
                'tipe' => 'produk',
                'status' => Pesanan::STATUS_SELESAI_FINAL,
                'tanggal' => $tanggal,
                'total' => 750000,
                'total_harga' => 750000,
            ]);

            UnitApar::create([
                'pelanggan_id' => $pelanggan->id,
                'pesanan_id' => $pesanan->id,
                'produk_id' => $produk->id,
                'no_seri' => 'GROUP-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'tgl_beli' => $tanggal,
                'tgl_produksi' => $tanggal,
                'ukuran' => '6 Kg',
                'bahan' => 'Dry Chemical Powder',
                'kondisi_awal' => 'layak',
                'tgl_expired' => '2027-06-30',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.index', [
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertSee('Nomor Unit');
        $response->assertSee('GROUP-11');

        $paginator = $response->viewData('units');
        $this->assertSame(11, $paginator->total());
        $this->assertSame(10, $paginator->perPage());
        $this->assertCount(10, $paginator->items());
    }

    public function test_admin_unit_apar_index_supports_search_product_status_and_date_filters(): void
    {
        $admin = $this->createAdmin();
        ['pelanggan' => $pelanggan, 'produk' => $produk] = $this->createFixture(customerName: 'Akhmad Rizaldy');

        $produkLain = Produk::create([
            'nama' => 'APAR GuardALL 3 Kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $produk->jenis_apar_id,
            'kapasitas' => '3 Kg',
            'penggunaan' => 'Area kantor',
            'harga' => 680000,
            'deskripsi' => 'Produk filter test',
            'stok' => 5,
        ]);

        $pesananExpired = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'tanggal' => '2026-06-11',
            'total' => 750000,
            'total_harga' => 750000,
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $pesananExpired->id,
            'produk_id' => $produk->id,
            'no_seri' => 'FILTER-EXPIRED-001',
            'tgl_beli' => '2026-06-11',
            'tgl_produksi' => '2026-06-11',
            'ukuran' => '6 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->subDay()->toDateString(),
        ]);

        $pesananAktif = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'tanggal' => '2026-06-20',
            'total' => 680000,
            'total_harga' => 680000,
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $pesananAktif->id,
            'produk_id' => $produkLain->id,
            'no_seri' => 'FILTER-AKTIF-001',
            'tgl_beli' => '2026-06-20',
            'tgl_produksi' => '2026-06-20',
            'ukuran' => '3 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.index', [
            'search' => 'GuardALL',
            'produk_id' => $produkLain->id,
            'status' => 'aktif',
            'tanggal_mode' => 'single',
            'tanggal' => '2026-06-20',
        ]));

        $response->assertOk();
        $response->assertSee('FILTER-AKTIF-001');
        $response->assertDontSee('FILTER-EXPIRED-001');
        $response->assertSee('Produk:');
        $response->assertSee('Status:');
        $response->assertSee('Tanggal:');
        $this->assertSame(1, $response->viewData('filteredUnitCount'));
        $this->assertSame(1, $response->viewData('summary')['aktif']);
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
        $response->assertSessionHas('error', 'Registrasi manual unit APAR dinonaktifkan. Unit dibuat otomatis dari transaksi pelanggan setelah pembayaran valid, lalu ditampilkan penuh saat transaksi selesai final.');
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

        $jenisRefill = JenisRefill::create([
            'nama' => 'Powder',
            'nama_label' => 'Powder',
            'harga' => 50000,
            'stok' => 100,
            'stok_minimum' => 5,
        ]);

        $refillOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 Kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 6,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'tanggal' => '2026-06-20',
            'total' => 120000,
            'total_harga' => 120000,
            'service_estimasi_biaya' => 120000,
            'service_keluhan' => 'Refill unit detail admin.',
        ]);

        $refillService = Service::create([
            'pesanan_id' => $refillOrder->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => '2026-06-21',
            'biaya' => 120000,
            'status_konfirmasi' => 'confirmed',
            'tgl_selesai_admin' => '2026-06-21 10:30:00',
        ]);

        Refill::create([
            'service_id' => $refillService->id,
            'unit_apar_id' => $unit->id,
            'jenis_refill_id' => $jenisRefill->id,
            'tgl_refill' => '2026-06-21',
            'biaya' => 120000,
        ]);

        $serviceOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => null,
            'service_jenis_apar' => 'Dry Chemical Powder',
            'service_ukuran_apar' => '6 Kg',
            'service_jumlah_unit' => 1,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'tanggal' => '2026-06-22',
            'total' => 75000,
            'total_harga' => 75000,
            'service_estimasi_biaya' => 75000,
            'service_keluhan' => 'Service ringan untuk unit detail admin.',
        ]);

        Service::create([
            'pesanan_id' => $serviceOrder->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Service Ringan',
            'tgl_service' => '2026-06-22',
            'biaya' => 75000,
            'status_konfirmasi' => 'confirmed',
            'tgl_selesai_admin' => '2026-06-22 14:45:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.unit-apar.show', $unit));

        $response->assertOk();
        $response->assertSee('Detail Unit APAR');
        $response->assertSee('UT-DETAIL-001');
        $response->assertSee('PT Pelanggan Uji');
        $response->assertSee('081234567899');
        $response->assertSee('APAR Powder 6 Kg');
        $response->assertSee('Dry Chemical Powder');
        $response->assertSee('Tanggal Dasar Masa Berlaku');
        $response->assertSee('Masa Berlaku Sampai');
        $response->assertSee('Sisa Masa Berlaku');
        $response->assertSee('Riwayat Refill');
        $response->assertSee('Riwayat Service');
        $response->assertSee('Powder');
        $response->assertSee('Service Ringan');
        $response->assertSee('10:30');
        $response->assertSee('14:45');
        $response->assertSee('Dibuat:');
        $response->assertSee('Selesai / Update:');
        $response->assertDontSee('Lokasi Unit');
        $response->assertDontSee('Keterangan');
        $response->assertDontSee('Gudang utama');
        $response->assertDontSee('Segel baru diganti');
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
            'metode_pembayaran' => 'cash',
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

    public function test_final_product_order_uses_batch_production_date_expiry_rules_for_1kg_2kg_and_3kg_units(): void
    {
        ['pelanggan' => $pelanggan] = $this->createFixture(customerName: 'Akhmad Rizaldy');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $scenarios = [
            [
                'name' => 'APAR TONATA Powder 1 kg',
                'brand' => 'TONATA',
                'capacity' => '1 kg',
                'expected_expiry' => '2026-09-11',
            ],
            [
                'name' => 'APAR GuardALL Powder 2 kg',
                'brand' => 'GuardALL',
                'capacity' => '2 kg',
                'expected_expiry' => '2027-03-11',
            ],
            [
                'name' => 'APAR Firefix Powder 3 kg',
                'brand' => 'FIREFIX',
                'capacity' => '3 kg',
                'expected_expiry' => '2027-03-11',
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            $produk = Produk::create([
                'nama' => $scenario['name'],
                'merek' => $scenario['brand'],
                'jenis_apar_id' => $jenisApar->id,
                'kapasitas' => $scenario['capacity'],
                'penggunaan' => 'Testing',
                'harga' => 500000 + ($index * 100000),
                'deskripsi' => 'Skenario expiry unit baru',
                'stok' => 5,
            ]);

            StokBatch::create([
                'produk_id' => $produk->id,
                'jumlah_masuk' => 5,
                'sisa_qty' => 5,
                'tgl_produksi' => '2026-03-11',
                'tgl_expired' => UnitApar::calculateExpiry('2026-03-11', $scenario['capacity'], 'Powder')->toDateString(),
                'keterangan' => 'Batch skenario expiry',
            ]);

            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelanggan->id,
                'user_id' => $pelanggan->user_id,
                'tipe' => 'produk',
                'status' => Pesanan::STATUS_SELESAI_FINAL,
                'tanggal' => '2026-06-19',
                'total' => 500000 + ($index * 100000),
                'total_harga' => 500000 + ($index * 100000),
                'metode_pembayaran' => 'cash',
            ]);

            $pesanan->details()->create([
                'produk_id' => $produk->id,
                'merek' => $produk->merek,
                'kapasitas' => $produk->kapasitas,
                'jumlah' => 1,
                'harga' => 500000 + ($index * 100000),
                'subtotal' => 500000 + ($index * 100000),
            ]);

            app(FinalTransactionStockService::class)->apply($pesanan->fresh());

            $unit = UnitApar::query()
                ->where('pesanan_id', $pesanan->id)
                ->first();

            $this->assertNotNull($unit);
            $this->assertSame('2026-06-19', $unit->tgl_beli->toDateString());
            $this->assertSame('2026-03-11', $unit->tgl_produksi->toDateString());
            $this->assertSame($scenario['capacity'], $unit->ukuran);
            $this->assertSame($scenario['expected_expiry'], $unit->tgl_expired->toDateString());
        }
    }

    public function test_sync_unit_expiry_command_corrects_legacy_units_using_production_or_latest_refill_date(): void
    {
        ['pelanggan' => $pelanggan] = $this->createFixture(customerName: 'Akhmad Rizaldy');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produkSatuKg = Produk::create([
            'nama' => 'APAR TONATA Powder 1 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Testing',
            'harga' => 350000,
            'deskripsi' => 'Legacy 1 kg',
            'stok' => 0,
        ]);

        $produkTigaKg = Produk::create([
            'nama' => 'APAR GuardALL Powder 3 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Testing',
            'harga' => 650000,
            'deskripsi' => 'Legacy 3 kg',
            'stok' => 0,
        ]);

        $unitSatuKg = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkSatuKg->id,
            'no_seri' => 'LEGACY-1KG-01',
            'tgl_beli' => '2026-06-19',
            'tgl_produksi' => '2026-03-11',
            'ukuran' => '1 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => '2026-09-11',
        ]);

        $unitTigaKg = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkTigaKg->id,
            'no_seri' => 'LEGACY-3KG-01',
            'tgl_beli' => '2026-01-10',
            'tgl_produksi' => '2026-01-10',
            'ukuran' => '3 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => '2026-01-16',
        ]);

        Service::create([
            'unit_apar_id' => $unitTigaKg->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => '2026-06-20',
            'biaya' => 150000,
            'status_konfirmasi' => 'confirmed',
        ]);

        $this->artisan('apar:sync-unit-expiry')
            ->expectsOutputToContain('diperbarui: 1')
            ->assertSuccessful();

        $this->assertSame('2026-09-11', $unitSatuKg->fresh()->tgl_expired->toDateString());
        $this->assertSame('2027-06-20', $unitTigaKg->fresh()->tgl_expired->toDateString());
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
            'metode_pembayaran' => 'cash',
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
