<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\FinalTransactionStockService;
use App\Support\RegisteredRefillUnitSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRefillFromHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_page_shows_service_on_every_unit_and_refill_only_when_h_minus_seven_or_expired(): void
    {
        [$user, $pelanggan] = $this->createCustomer();
        $produk = $this->createPowderProduct();

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'AMAN-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(2)->toDateString(),
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'tgl_expired' => now()->addDays(45)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'H10-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(4)->toDateString(),
            'tgl_produksi' => now()->subMonths(4)->toDateString(),
            'tgl_expired' => now()->addDays(10)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'REFILL-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(5)->toDateString(),
            'tgl_produksi' => now()->subMonths(5)->toDateString(),
            'tgl_expired' => now()->addDays(7)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'EXPIRED-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(6)->toDateString(),
            'tgl_produksi' => now()->subMonths(6)->toDateString(),
            'tgl_expired' => now()->subDay()->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $response = $this->actingAs($user)->get(route('riwayat-apar'));

        $response->assertOk();
        $response->assertSeeText('Aman');
        $response->assertSeeText('Perlu Refill');
        $response->assertSeeText('Service selalu tersedia untuk setiap unit APAR.');
        $response->assertDontSeeText('Unit APAR di halaman ini dipakai untuk pemantauan saja.');
        $this->assertStringContainsString('REFILL-001', $response->getContent());
        $this->assertStringContainsString('EXPIRED-001', $response->getContent());
        $this->assertSame(4, substr_count($response->getContent(), 'Ajukan Service'));
        $this->assertSame(2, substr_count($response->getContent(), 'Ajukan Refill'));
    }

    public function test_history_refill_route_still_redirects_with_prefill_session(): void
    {
        config(['broadcasting.default' => 'null']);

        [$user, $pelanggan] = $this->createCustomer();
        $produk = $this->createPowderProduct();
        $this->createPowderRefillMaster();

        $unitA = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'PREFILL-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(6)->toDateString(),
            'tgl_produksi' => now()->subMonths(6)->toDateString(),
            'tgl_expired' => now()->addDays(7)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $unitB = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'PREFILL-002',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(3)->toDateString(),
            'tgl_produksi' => now()->subMonths(3)->toDateString(),
            'tgl_expired' => now()->addDays(6)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $response = $this->actingAs($user)->post(route('riwayat-apar.ajukan-refill'), [
            'unit_ids' => [$unitA->id, $unitB->id],
        ]);

        $response->assertRedirect(route('order.create'));
        $response->assertSessionHas('prefill_registered_refill');
        $this->assertSame(
            [$unitA->id, $unitB->id],
            session('prefill_registered_refill.selected_unit_ids')
        );

        $orderPage = $this->actingAs($user)->get(route('order.create'));
        $orderPage->assertOk();
        $orderPage->assertSee('"service_jenis_layanan":"refill"', false);
        $orderPage->assertSee('PREFILL-001');
        $orderPage->assertSee('PREFILL-002');
    }

    public function test_history_service_route_redirects_with_prefill_session(): void
    {
        config(['broadcasting.default' => 'null']);

        [$user, $pelanggan] = $this->createCustomer();
        $produk = $this->createPowderProduct();

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'SERVICE-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(2)->toDateString(),
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'tgl_expired' => now()->addDays(45)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $response = $this->actingAs($user)->post(route('riwayat-apar.ajukan-service'), [
            'action_unit_id' => $unit->id,
        ]);

        $response->assertRedirect(route('order.create'));
        $response->assertSessionHas('prefill_registered_service');
        $this->assertSame([$unit->id], session('prefill_registered_service.selected_unit_ids'));

        $orderPage = $this->actingAs($user)->get(route('order.create'));
        $orderPage->assertOk();
        $orderPage->assertSee('"service_jenis_layanan":"service"', false);
        $orderPage->assertSee('SERVICE-001');
    }

    public function test_history_refill_route_rejects_unit_that_is_still_in_active_refill_process(): void
    {
        [$user, $pelanggan] = $this->createCustomer();
        $produk = $this->createPowderProduct();

        $lockedUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'DUPL-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(4)->toDateString(),
            'tgl_produksi' => now()->subMonths(4)->toDateString(),
            'tgl_expired' => now()->addDays(7)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $activeOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $user->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '1 kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 1,
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 50000,
            'total_harga' => 50000,
            'service_estimasi_biaya' => 50000,
            'alamat_maps' => $pelanggan->alamat_maps,
            'alamat_detail' => $pelanggan->alamat_detail,
        ]);

        Service::create([
            'pesanan_id' => $activeOrder->id,
            'unit_apar_id' => $lockedUnit->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => now()->toDateString(),
            'biaya' => 50000,
            'status_konfirmasi' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('riwayat-apar.ajukan-refill'), [
            'action_unit_id' => $lockedUnit->id,
        ]);

        $response->assertRedirect(route('riwayat-apar'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sedang dalam proses refill', (string) session('error'));
    }

    public function test_finalized_multi_unit_registered_refill_updates_all_units_and_reduces_stock(): void
    {
        config(['broadcasting.default' => 'null']);
        config([
            'services.apar_service_pickup.store_lat' => -6.457629743293867,
            'services.apar_service_pickup.store_lng' => 106.84730349536345,
            'services.apar_service_pickup.rate_per_km' => 3500,
            'services.apar_service_pickup.min_cost' => 15000,
        ]);

        [$user, $pelanggan] = $this->createCustomer();
        $produk = $this->createPowderProduct();
        $jenisRefill = $this->createPowderRefillMaster();

        $unitA = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'FINAL-001',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(6)->toDateString(),
            'tgl_produksi' => now()->subMonths(6)->toDateString(),
            'tgl_expired' => now()->addDays(5)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $unitB = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'FINAL-002',
            'ukuran' => '1 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => now()->subMonths(2)->toDateString(),
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'tgl_expired' => now()->addDays(4)->toDateString(),
            'kondisi_awal' => 'layak',
        ]);

        $response = $this->actingAs($user)->post(route('order.store'), [
            'nama' => $pelanggan->nama,
            'no_wa' => $pelanggan->no_wa,
            'alamat_maps' => $pelanggan->alamat_maps,
            'alamat_detail' => $pelanggan->alamat_detail,
            'alamat_provinsi' => $pelanggan->alamat_provinsi,
            'alamat_kota' => $pelanggan->alamat_kota,
            'alamat_kecamatan' => $pelanggan->alamat_kecamatan,
            'alamat_kode_pos' => $pelanggan->alamat_kode_pos,
            'alamat_lat' => $pelanggan->alamat_lat,
            'alamat_lng' => $pelanggan->alamat_lng,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_unit_status' => 'terdaftar',
            'service_purchase_group' => RegisteredRefillUnitSupport::PREFILL_GROUP_KEY,
            'service_unit_apar_ids' => [$unitA->id, $unitB->id],
            'service_metode_penanganan' => 'dijemput',
            'service_keluhan' => 'Tolong refill dua unit ini.',
            'shipping_weight' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $pesanan = Pesanan::query()->latest('id')->firstOrFail();
        $oneWayDistance = $this->haversineKm(
            -6.457629743293867,
            106.84730349536345,
            (float) $pelanggan->alamat_lat,
            (float) $pelanggan->alamat_lng,
        );
        $expectedDistance = round($oneWayDistance * 2, 2);
        $expectedPickupCost = round(max(15000, $oneWayDistance * 2 * 3500), 0);

        $this->assertSame('service', $pesanan->tipe);
        $this->assertSame('refill', $pesanan->service_jenis_layanan);
        $this->assertSame(100000.0, (float) $pesanan->service_estimasi_biaya);
        $this->assertSame($expectedPickupCost, (float) $pesanan->ongkir);
        $this->assertSame($expectedDistance, (float) $pesanan->shipping_distance_km);
        $this->assertNull($pesanan->shipping_courier);
        $this->assertNull($pesanan->shipping_service);
        $this->assertNull($pesanan->shipping_etd);
        $this->assertStringContainsString('FINAL-001', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('FINAL-002', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('Refill: Powder', (string) $pesanan->service_keluhan);

        $oldExpiryA = $unitA->tgl_expired->toDateString();
        $oldExpiryB = $unitB->tgl_expired->toDateString();

        $pesanan->update(['status' => Pesanan::STATUS_SELESAI_FINAL]);
        app(FinalTransactionStockService::class)->apply($pesanan->fresh());

        $this->assertSame(8.0, (float) $jenisRefill->fresh()->stok);
        $this->assertTrue((bool) $pesanan->fresh()->stok_dikurangi);
        $this->assertNotSame($oldExpiryA, $unitA->fresh()->tgl_expired?->toDateString());
        $this->assertNotSame($oldExpiryB, $unitB->fresh()->tgl_expired?->toDateString());
        $this->assertDatabaseHas('services', [
            'pesanan_id' => $pesanan->id,
        ]);
        $this->assertSame(2, UnitApar::query()->count());
        $this->assertDatabaseMissing('unit_apars', [
            'pesanan_id' => $pesanan->id,
        ]);
    }

    private function createCustomer(): array
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Refill',
            'no_telpon' => '081234567801',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Refill',
            'no_wa' => '081234567801',
            'alamat' => 'Jl. Refill Otomatis',
            'alamat_maps' => 'Jl. Refill Otomatis',
            'alamat_detail' => 'Ruko depan',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bogor',
            'alamat_kecamatan' => 'Bogor Tengah',
            'alamat_kode_pos' => '16121',
            'alamat_lat' => -6.595,
            'alamat_lng' => 106.816,
            'status' => 'tetap',
        ]);

        return [$user, $pelanggan];
    }

    private function createPowderProduct(): Produk
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        return Produk::create([
            'nama' => 'APAR GuardALL Powder 1 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Gudang',
            'harga' => 350000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);
    }

    private function createPowderRefillMaster(): JenisRefill
    {
        return JenisRefill::create([
            'nama' => 'Powder',
            'stok' => 10,
            'satuan' => 'kg',
            'harga' => 50000,
            'stok_minimum' => 2,
            'service_price_rules_json' => [
                ['ukuran' => '1 kg', 'harga' => 50000],
            ],
        ]);
    }

    private function haversineKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadiusKm = 6371;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat))
            * cos(deg2rad($toLat))
            * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }
}
