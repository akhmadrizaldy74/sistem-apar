<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRefillRequestUiAndStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_refill_index_shows_manual_unit_for_unregistered_apar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Refill Manual', '628111110001', 'Jl Refill Manual');
        $jenisRefill = JenisRefill::create([
            'nama' => 'Powder',
            'stok' => 40,
            'satuan' => 'kg',
            'harga' => 150000,
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '9 kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 9,
            'status' => Pesanan::STATUS_PENDING,
            'tanggal' => now()->toDateString(),
            'total' => 150000,
            'total_harga' => 150000,
            'service_estimasi_biaya' => 150000,
            'sumber_pesanan' => 'website',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.refill.index'));

        $response->assertOk();
        $response->assertSeeText('APAR Tidak Terdaftar');
        $response->assertSeeText('APAR Powder 9 kg');
        $response->assertSeeText('Powder');
    }

    public function test_admin_service_index_shows_manual_unit_for_unregistered_apar(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Service Manual', '628111110002', 'Jl Service Manual');
        $paket = ServicePaket::create([
            'nama' => 'Hydrotest Ringan',
            'label' => 'hydrotest_ringan',
            'harga' => 180000,
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => $paket->id,
            'service_jenis_apar' => 'Foam',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 1,
            'status' => Pesanan::STATUS_PENDING,
            'tanggal' => now()->toDateString(),
            'total' => 180000,
            'total_harga' => 180000,
            'service_estimasi_biaya' => 180000,
            'sumber_pesanan' => 'website',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.service.index'));

        $response->assertOk();
        $response->assertSeeText('APAR Tidak Terdaftar');
        $response->assertSeeText('APAR Foam 6 kg');
        $response->assertSeeText('Hydrotest Ringan');
    }

    public function test_product_final_creates_unit_using_stock_batch_production_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Batch Final', '628111110024', 'Jl Batch Final');
        $jenisApar = JenisApar::create([
            'nama' => 'Foam',
        ]);
        $produk = Produk::create([
            'nama' => 'APAR Foam 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk test batch final',
            'stok' => 2,
        ]);
        $stokBatch = StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 2,
            'sisa_qty' => 2,
            'tgl_produksi' => '2026-05-01',
            'tgl_expired' => UnitApar::calculateExpiry('2026-05-01', '6 kg', 'Foam')->toDateString(),
            'keterangan' => 'Batch awal produk foam',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => '2026-06-15',
            'total' => 950000,
            'total_harga' => 950000,
            'sumber_pesanan' => 'website',
            'stok_dikurangi' => false,
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 950000,
            'subtotal' => 950000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pesanan.selesai-final', $pesanan));

        $response->assertRedirect();

        $unit = UnitApar::query()
            ->where('pesanan_id', $pesanan->id)
            ->first();

        $this->assertNotNull($unit);
        $this->assertSame('2026-06-15', $unit->tgl_beli->toDateString());
        $this->assertSame('2026-05-01', $unit->tgl_produksi->toDateString());
        $this->assertSame(
            UnitApar::calculateExpiry('2026-05-01', '6 kg', 'Foam')->toDateString(),
            $unit->tgl_expired->toDateString()
        );
        $this->assertSame(1, (int) $stokBatch->fresh()->sisa_qty);
        $this->assertSame(Pesanan::STATUS_SELESAI_FINAL, $pesanan->fresh()->status);
    }

    public function test_refill_unregistered_final_creates_multiple_units_with_customer_prefix(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('Akhmad Rizaldy', '628111110020', 'Jl Prefix Customer');
        $jenisApar = JenisApar::create([
            'nama' => 'Foam',
        ]);
        Produk::create([
            'nama' => 'APAR Foam 9 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '9 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk test foam 9 kg',
            'stok' => 0,
        ]);
        $jenisRefill = JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 30,
            'satuan' => 'liter',
            'harga' => 150000,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Foam',
            'service_ukuran_apar' => '9 kg',
            'service_jumlah_unit' => 2,
            'service_total_kg' => 18,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => '2026-06-14',
            'teknisi_selesai_at' => '2026-06-16 09:00:00',
            'total' => 300000,
            'total_harga' => 300000,
            'service_estimasi_biaya' => 300000,
            'sumber_pesanan' => 'website',
            'stok_dikurangi' => false,
        ]);

        Service::create([
            'pesanan_id' => $pesanan->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => '2026-06-14',
            'biaya' => 300000,
            'status_konfirmasi' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.refill.update-status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $response->assertRedirect();
        $units = UnitApar::query()
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('no_seri')
            ->get();

        $this->assertCount(2, $units);
        $this->assertSame([
            'AKHMAD-14062026-01',
            'AKHMAD-14062026-02',
        ], $units->pluck('no_seri')->all());
        $this->assertTrue($units->every(fn (UnitApar $unit) => $unit->bahan === 'Foam'));
        $this->assertTrue($units->every(fn (UnitApar $unit) => $unit->ukuran === '9 kg'));
        $this->assertTrue($units->every(fn (UnitApar $unit) => $unit->kondisi_awal === 'layak'));
        $this->assertTrue($units->every(fn (UnitApar $unit) => $unit->tgl_produksi->toDateString() === '2026-06-16'));
        $this->assertSame(12.0, (float) $jenisRefill->fresh()->stok);
    }

    public function test_refill_final_uses_technician_completion_date_for_registered_unit_expiry(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Refill Tanggal Kerja', '628111110025', 'Jl Refill Tanggal Kerja');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);
        $produk = Produk::create([
            'nama' => 'APAR Powder 1 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Panel',
            'harga' => 350000,
            'deskripsi' => 'Produk test refill terdaftar',
            'stok' => 0,
        ]);
        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'REFILL-REG-01',
            'tgl_beli' => '2026-01-10',
            'tgl_produksi' => '2026-01-10',
            'ukuran' => '1 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => '2026-07-10',
        ]);
        $jenisRefill = JenisRefill::create([
            'nama' => 'Powder',
            'stok' => 20,
            'satuan' => 'kg',
            'harga' => 100000,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '1 kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 1,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => '2026-06-14',
            'teknisi_selesai_at' => '2026-06-20 10:15:00',
            'total' => 100000,
            'total_harga' => 100000,
            'service_estimasi_biaya' => 100000,
            'sumber_pesanan' => 'website',
            'stok_dikurangi' => false,
        ]);

        Service::create([
            'pesanan_id' => $pesanan->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => '2026-06-14',
            'biaya' => 100000,
            'status_konfirmasi' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.refill.update-status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $response->assertRedirect();

        $unit->refresh();
        $refillLog = Refill::query()->where('unit_apar_id', $unit->id)->latest('id')->first();
        $serviceLog = Service::query()->where('pesanan_id', $pesanan->id)->first();

        $this->assertSame('2026-06-20', $unit->tgl_produksi->toDateString());
        $this->assertSame(
            UnitApar::calculateExpiry('2026-06-20', '1 kg', 'Powder')->toDateString(),
            $unit->tgl_expired->toDateString()
        );
        $this->assertNotNull($refillLog);
        $this->assertSame('2026-06-20', $refillLog->tgl_refill->toDateString());
        $this->assertSame('2026-06-20', $serviceLog->tgl_service->toDateString());
    }

    public function test_admin_can_create_refill_offline_for_multiple_registered_units(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Multi Refill', '628111110021', 'Jl Multi Refill');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);
        $produkKecil = Produk::create([
            'nama' => 'APAR Powder 1 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Panel',
            'harga' => 350000,
            'deskripsi' => 'Produk test 1 kg',
            'stok' => 0,
        ]);
        $produkBesar = Produk::create([
            'nama' => 'APAR Powder 9 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '9 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk test 9 kg',
            'stok' => 0,
        ]);
        $unitSatu = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkKecil->id,
            'no_seri' => 'MULTI-REFILL-01',
            'tgl_beli' => now()->subMonths(2),
            'tgl_produksi' => now()->subMonths(2),
            'ukuran' => '1 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(10),
        ]);
        $unitDua = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkBesar->id,
            'no_seri' => 'MULTI-REFILL-02',
            'tgl_beli' => now()->subMonths(2),
            'tgl_produksi' => now()->subMonths(2),
            'ukuran' => '9 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(10),
        ]);
        $jenisRefill = JenisRefill::create([
            'nama' => 'Powder',
            'stok' => 40,
            'satuan' => 'kg',
            'harga' => 100000,
            'service_price_rules_json' => [
                ['ukuran' => '1 kg', 'harga' => 100000],
                ['ukuran' => '9 kg', 'harga' => 200000],
            ],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.refill.store'), [
            'pelanggan_id' => $pelanggan->id,
            'status_unit' => 'terdaftar',
            'unit_apar_ids' => [$unitSatu->id, $unitDua->id],
            'jenis_refill_id' => $jenisRefill->id,
            'tgl_refill' => now()->toDateString(),
            'catatan_admin' => 'Refill offline multi-unit terdaftar.',
        ]);

        $response->assertRedirect(route('admin.refill.index'));
        $this->assertDatabaseCount('pesanans', 2);
        $this->assertDatabaseCount('services', 2);
        $this->assertDatabaseCount('refills', 2);
        $this->assertDatabaseCount('unit_apars', 2);
        $this->assertSame([100000.0, 200000.0], Pesanan::query()->orderBy('total')->pluck('total')->map(fn ($value) => (float) $value)->all());
    }

    public function test_admin_can_create_service_offline_for_multiple_registered_units(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Multi Service', '628111110022', 'Jl Multi Service');
        $jenisPowder = JenisApar::create(['nama' => 'Powder']);
        $jenisFoam = JenisApar::create(['nama' => 'Foam']);
        $produkPowder = Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisPowder->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Ruang arsip',
            'harga' => 650000,
            'deskripsi' => 'Produk powder 6 kg',
            'stok' => 0,
        ]);
        $produkFoam = Produk::create([
            'nama' => 'APAR Foam 9 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisFoam->id,
            'kapasitas' => '9 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk foam 9 kg',
            'stok' => 0,
        ]);
        $unitSatu = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkPowder->id,
            'no_seri' => 'MULTI-SERVICE-01',
            'tgl_beli' => now()->subMonths(3),
            'tgl_produksi' => now()->subMonths(3),
            'ukuran' => '6 kg',
            'bahan' => 'Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(9),
        ]);
        $unitDua = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produkFoam->id,
            'no_seri' => 'MULTI-SERVICE-02',
            'tgl_beli' => now()->subMonths(3),
            'tgl_produksi' => now()->subMonths(3),
            'ukuran' => '9 kg',
            'bahan' => 'Foam',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(9),
        ]);
        $paket = ServicePaket::create([
            'nama' => 'Hydrotest Ringan',
            'label' => 'paket a',
            'harga' => 180000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.service.store'), [
            'pelanggan_id' => $pelanggan->id,
            'status_unit' => 'terdaftar',
            'unit_apar_ids' => [$unitSatu->id, $unitDua->id],
            'service_paket_id' => $paket->id,
            'tgl_service' => now()->toDateString(),
            'catatan_admin' => 'Service offline multi-unit terdaftar.',
        ]);

        $response->assertRedirect(route('admin.service.index'));
        $this->assertDatabaseCount('pesanans', 2);
        $this->assertDatabaseCount('services', 2);
        $this->assertDatabaseCount('unit_apars', 2);
        $this->assertSame(2, Service::query()->whereIn('unit_apar_id', [$unitSatu->id, $unitDua->id])->count());
        $this->assertSame([1, 1], Pesanan::query()->orderBy('id')->pluck('service_jumlah_unit')->map(fn ($value) => (int) $value)->all());
    }

    public function test_refill_stock_is_reduced_only_once_when_final_status_is_saved_twice(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Stok Refill', '628111110003', 'Jl Stok Refill');
        $jenisApar = JenisApar::create([
            'nama' => 'Foam',
        ]);
        $produk = Produk::create([
            'nama' => 'APAR Foam 9 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '9 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk test refill',
            'stok' => 0,
        ]);
        $jenisRefill = JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 30,
            'satuan' => 'liter',
            'harga' => 150000,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Foam',
            'service_ukuran_apar' => '9 kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 9,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => now()->toDateString(),
            'total' => 150000,
            'total_harga' => 150000,
            'service_estimasi_biaya' => 150000,
            'sumber_pesanan' => 'website',
            'stok_dikurangi' => false,
        ]);

        $firstResponse = $this->actingAs($admin)->post(route('admin.refill.update-status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $firstResponse->assertRedirect();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'stok_dikurangi' => 1,
        ]);
        $this->assertSame('Kg', $jenisRefill->fresh()->satuan_label);
        $this->assertSame(21.0, (float) $jenisRefill->fresh()->stok);

        $secondResponse = $this->actingAs($admin)->post(route('admin.refill.update-status', $pesanan->fresh()), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $secondResponse->assertRedirect();
        $this->assertSame(21.0, (float) $jenisRefill->fresh()->stok);
        $this->assertDatabaseHas('produks', [
            'id' => $produk->id,
        ]);
    }

    public function test_service_equipment_stock_is_reduced_only_once_when_final_status_is_saved_twice(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pelanggan = $this->createLinkedCustomer('PT Stok Service', '628111110004', 'Jl Stok Service');
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);
        Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 650000,
            'deskripsi' => 'Produk test powder 6 kg',
            'stok' => 0,
        ]);
        $peralatan = Peralatan::create([
            'nama' => 'Segel APAR',
            'stok' => 10,
            'stok_minimum' => 1,
            'harga_standar' => 15000,
        ]);
        $paket = ServicePaket::create([
            'nama' => 'Service Segel',
            'label' => 'service_segel',
            'harga' => 200000,
        ]);
        $paket->peralatans()->attach($peralatan->id, ['jumlah_estimasi' => 2]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => $paket->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 1,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => now()->toDateString(),
            'total' => 200000,
            'total_harga' => 200000,
            'service_estimasi_biaya' => 200000,
            'sumber_pesanan' => 'website',
        ]);

        Service::create([
            'pesanan_id' => $pesanan->id,
            'service_paket_id' => $paket->id,
            'jenis_service' => $paket->nama,
            'tgl_service' => now()->toDateString(),
            'biaya' => 200000,
            'actual_peralatan_json' => json_encode([[
                'peralatan_id' => $peralatan->id,
                'nama' => $peralatan->nama,
                'jumlah' => 2,
            ]]),
            'status_konfirmasi' => 'pending',
        ]);

        $firstResponse = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $firstResponse->assertRedirect();
        $this->assertSame(8, $peralatan->fresh()->stok);

        $secondResponse = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan->fresh()), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $secondResponse->assertRedirect();
        $this->assertSame(8, $peralatan->fresh()->stok);
    }

    public function test_customer_history_hides_technician_name_for_service_transactions(): void
    {
        $pelanggan = $this->createLinkedCustomer('PT Riwayat Aman', '628111110023', 'Jl Riwayat Aman');
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
            'name' => 'Teknisi Tampil',
        ]);
        $jenisRefill = JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 20,
            'satuan' => 'kg',
            'harga' => 150000,
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Foam',
            'service_ukuran_apar' => '9 kg',
            'service_jumlah_unit' => 2,
            'service_total_kg' => 18,
            'status' => Pesanan::STATUS_SELESAI_FINAL,
            'tanggal' => now()->toDateString(),
            'total' => 300000,
            'total_harga' => 300000,
            'service_estimasi_biaya' => 300000,
            'sumber_pesanan' => 'website',
            'metode_pembayaran' => 'transfer',
            'pembayaran_terkonfirmasi_at' => now(),
            'teknisi_selesai_at' => now(),
            'teknisi_catatan' => 'Catatan teknisi rahasia',
        ]);

        $response = $this->actingAs($pelanggan->user)->get('/riwayat-apar');

        $response->assertOk();
        $response->assertSeeText('Foam');
        $response->assertSeeText('9 kg');
        $response->assertSeeText('2 unit');
        $response->assertDontSeeText('Teknisi Tampil');
        $response->assertDontSeeText('Catatan Teknisi');
        $response->assertDontSeeText('Catatan teknisi rahasia');
        $response->assertDontSeeText('Tanggal Selesai');
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
