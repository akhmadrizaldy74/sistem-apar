<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ServicePaket;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderServicePricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_multi_item_refill_order_uses_item_totals_without_creating_units(): void
    {
        config(['broadcasting.default' => 'null']);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081200000001',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Refill Manual',
            'no_wa' => '081200000001',
            'alamat' => 'Jl. Manual Refill',
            'alamat_maps' => 'Jl. Manual Refill',
            'alamat_detail' => 'Ruko 1',
            'alamat_lat' => -6.2,
            'alamat_lng' => 106.8,
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        Produk::create([
            'nama' => 'APAR Powder 2 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 300000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        Produk::create([
            'nama' => 'APAR Powder 4 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '4 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $powder = JenisRefill::create([
            'nama' => 'Powder',
            'nama_label' => 'Powder',
            'harga' => 10000,
            'stok' => 100,
            'stok_minimum' => 5,
        ]);

        $foam = JenisRefill::create([
            'nama' => 'Foam',
            'nama_label' => 'Foam',
            'harga' => 15000,
            'stok' => 100,
            'stok_minimum' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('order.store'), [
            'nama' => $pelanggan->nama,
            'no_wa' => $pelanggan->no_wa,
            'alamat_maps' => $pelanggan->alamat_maps,
            'alamat_detail' => $pelanggan->alamat_detail,
            'alamat_lat' => $pelanggan->alamat_lat,
            'alamat_lng' => $pelanggan->alamat_lng,
            'tipe_layanan' => 'service',
            'metode_pengiriman' => 'pickup',
            'bank_tujuan' => 'bca',
            'service_jenis_layanan' => 'refill',
            'service_metode_penanganan' => 'antar sendiri',
            'service_keluhan' => 'Tolong refill campuran.',
            'service_refill_items' => [
                [
                    'jenis_refill_id' => $powder->id,
                    'ukuran_apar' => '2 kg',
                    'jumlah_unit' => 2,
                ],
                [
                    'jenis_refill_id' => $foam->id,
                    'ukuran_apar' => '4 kg',
                    'jumlah_unit' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $pesanan = Pesanan::query()->latest('id')->firstOrFail();

        $this->assertSame('service', $pesanan->tipe);
        $this->assertSame('refill', $pesanan->service_jenis_layanan);
        $this->assertSame(3, (int) $pesanan->service_jumlah_unit);
        $this->assertSame(8.0, (float) $pesanan->service_total_kg);
        $this->assertSame(100000.0, (float) $pesanan->service_estimasi_biaya);
        $this->assertSame(100000.0, (float) $pesanan->total);
        $this->assertNull($pesanan->service_jenis_refill_id);
        $this->assertStringContainsString('1. Powder | 2 kg | 2 unit - Rp40.000', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('2. Foam | 4 kg | 1 unit - Rp60.000', (string) $pesanan->service_keluhan);
        $this->assertStringNotContainsString('Status Unit:', (string) $pesanan->keterangan);
        $this->assertSame(0, UnitApar::query()->count());
    }

    public function test_manual_multi_item_service_order_uses_package_totals(): void
    {
        config(['broadcasting.default' => 'null']);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081200000002',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Service Manual',
            'no_wa' => '081200000002',
            'alamat' => 'Jl. Manual Service',
            'alamat_maps' => 'Jl. Manual Service',
            'alamat_detail' => 'Ruko 2',
            'alamat_lat' => -6.21,
            'alamat_lng' => 106.81,
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        Produk::create([
            'nama' => 'APAR Powder 3 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Gudang',
            'harga' => 400000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        Produk::create([
            'nama' => 'APAR CO2 6 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 700000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $paketRingan = ServicePaket::create([
            'nama' => 'Service Ringan',
            'label' => 'Paket A',
            'harga' => 35000,
        ]);

        $paketValve = ServicePaket::create([
            'nama' => 'Ganti Valve APAR',
            'label' => 'Valve',
            'harga' => 100000,
        ]);

        $response = $this->actingAs($user)->post(route('order.store'), [
            'nama' => $pelanggan->nama,
            'no_wa' => $pelanggan->no_wa,
            'alamat_maps' => $pelanggan->alamat_maps,
            'alamat_detail' => $pelanggan->alamat_detail,
            'alamat_lat' => $pelanggan->alamat_lat,
            'alamat_lng' => $pelanggan->alamat_lng,
            'tipe_layanan' => 'service',
            'metode_pengiriman' => 'pickup',
            'bank_tujuan' => 'bca',
            'service_jenis_layanan' => 'service',
            'service_metode_penanganan' => 'antar sendiri',
            'service_keluhan' => 'Mohon service dua item.',
            'service_service_items' => [
                [
                    'jenis_apar' => 'Powder',
                    'service_paket_id' => $paketRingan->id,
                    'ukuran_apar' => '3 kg',
                    'jumlah_unit' => 2,
                ],
                [
                    'jenis_apar' => 'CO2',
                    'service_paket_id' => $paketValve->id,
                    'ukuran_apar' => '6 kg',
                    'jumlah_unit' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $pesanan = Pesanan::query()->latest('id')->firstOrFail();

        $this->assertSame('service', $pesanan->tipe);
        $this->assertSame('service', $pesanan->service_jenis_layanan);
        $this->assertSame(3, (int) $pesanan->service_jumlah_unit);
        $this->assertSame(170000.0, (float) $pesanan->service_estimasi_biaya);
        $this->assertSame(170000.0, (float) $pesanan->total);
        $this->assertNull($pesanan->service_paket_id);
        $this->assertStringContainsString('1. Service Ringan | 3 kg | 2 unit - Rp70.000', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('2. Ganti Valve APAR | 6 kg | 1 unit - Rp100.000', (string) $pesanan->service_keluhan);
        $this->assertStringNotContainsString('Status Unit:', (string) $pesanan->keterangan);
    }

    public function test_registered_service_order_uses_distance_based_pickup_cost_for_each_selected_unit(): void
    {
        config(['broadcasting.default' => 'null']);
        config([
            'services.apar_service_pickup.store_lat' => -6.457629743293867,
            'services.apar_service_pickup.store_lng' => 106.84730349536345,
            'services.apar_service_pickup.rate_per_km' => 3500,
            'services.apar_service_pickup.min_cost' => 15000,
        ]);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081234567890',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Service',
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Service',
            'alamat_maps' => 'Jl. Service',
            'alamat_detail' => 'Gudang belakang',
            'alamat_lat' => -6.889,
            'alamat_lng' => 107.610,
            'rajaongkir_destination_id' => '11454',
            'rajaongkir_destination_label' => 'Kec. Sukajadi, Kota Bandung, Jawa Barat, 40161',
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk2Kg = Produk::create([
            'nama' => 'APAR Powder 2 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 300000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $produk4Kg = Produk::create([
            'nama' => 'APAR Powder 4 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '4 kg',
            'penggunaan' => 'Gudang',
            'harga' => 500000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $paketC = ServicePaket::create([
            'nama' => 'Service Lengkap',
            'label' => 'Paket C',
            'harga' => 150000,
        ]);

        $purchaseDate = now()->subMonth()->toDateString();

        $unit2Kg = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk2Kg->id,
            'no_seri' => 'SRV-2KG-001',
            'ukuran' => '2 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => $purchaseDate,
            'tgl_produksi' => now()->subYear()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        $unit4Kg = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk4Kg->id,
            'no_seri' => 'SRV-4KG-001',
            'ukuran' => '4 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => $purchaseDate,
            'tgl_produksi' => now()->subYear()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('order.store'), [
            'nama' => $pelanggan->nama,
            'no_wa' => $pelanggan->no_wa,
            'alamat_maps' => 'Jl. Service',
            'alamat_detail' => 'Gudang belakang',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bandung',
            'alamat_kecamatan' => 'Sukajadi',
            'alamat_kode_pos' => '40161',
            'alamat_lat' => '-6.88900000',
            'alamat_lng' => '107.61000000',
            'rajaongkir_destination_id' => '11454',
            'rajaongkir_destination_label' => 'Kec. Sukajadi, Kota Bandung, Jawa Barat, 40161',
            'tipe_layanan' => 'service',
            'metode_pengiriman' => 'diantar',
            'bank_tujuan' => 'bca',
            'service_jenis_layanan' => 'service',
            'service_unit_status' => 'terdaftar',
            'service_purchase_group' => $purchaseDate,
            'service_unit_apar_ids' => [$unit2Kg->id, $unit4Kg->id],
            'service_paket_id' => $paketC->id,
            'service_metode_penanganan' => 'dijemput',
            'service_keluhan' => 'Mohon service lengkap.',
            'shipping_weight' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(session('error'), (string) session('error'));
        $this->assertDatabaseCount('pesanans', 1);

        $pesanan = Pesanan::query()->latest('id')->firstOrFail();
        $oneWayDistance = $this->haversineKm(
            -6.457629743293867,
            106.84730349536345,
            -6.889,
            107.610,
        );
        $expectedDistance = round($oneWayDistance * 2, 2);
        $expectedPickupCost = round(max(15000, $oneWayDistance * 2 * 3500), 0);

        $this->assertSame('service', $pesanan->tipe);
        $this->assertSame('service', $pesanan->service_jenis_layanan);
        $this->assertSame('diantar_internal', $pesanan->metode_pengiriman);
        $this->assertSame('bca', $pesanan->bank);
        $this->assertSame(300000.0, (float) $pesanan->service_estimasi_biaya);
        $this->assertSame($expectedPickupCost, (float) $pesanan->ongkir);
        $this->assertSame(
            (float) $pesanan->service_estimasi_biaya + (float) $pesanan->ongkir,
            (float) $pesanan->total
        );
        $this->assertSame($expectedDistance, (float) $pesanan->shipping_distance_km);
        $this->assertNull($pesanan->shipping_courier);
        $this->assertNull($pesanan->shipping_service);
        $this->assertNull($pesanan->shipping_etd);
        $this->assertSame('11454', $pesanan->shipping_destination_id);
        $this->assertSame(0, (int) $pesanan->shipping_weight);
        $this->assertStringContainsString('2 kg', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('4 kg', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('Rp150.000', (string) $pesanan->service_keluhan);
    }

    public function test_service_pickup_quote_returns_round_trip_distance_and_pickup_cost(): void
    {
        config(['broadcasting.default' => 'null']);
        config([
            'services.apar_service_pickup.store_lat' => -6.457629743293867,
            'services.apar_service_pickup.store_lng' => 106.84730349536345,
            'services.apar_service_pickup.rate_per_km' => 3500,
            'services.apar_service_pickup.min_cost' => 15000,
        ]);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081234567892',
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Quote Service',
            'no_wa' => '081234567892',
            'alamat' => 'Jl. Pickup Quote',
            'alamat_maps' => 'Jl. Pickup Quote',
            'alamat_detail' => 'Gudang belakang',
            'alamat_lat' => -6.595038,
            'alamat_lng' => 106.816635,
            'status' => 'tetap',
        ]);

        $oneWayDistance = $this->haversineKm(
            -6.457629743293867,
            106.84730349536345,
            -6.595038,
            106.816635,
        );
        $roundTripDistance = round($oneWayDistance * 2, 2);
        $expectedCost = round(max(15000, $oneWayDistance * 2 * 3500), 0);

        $response = $this->actingAs($user)->postJson(route('rajaongkir.cost'), [
            'order_type' => 'service',
            'handling_method' => 'dijemput',
            'alamat_lat' => -6.595038,
            'alamat_lng' => 106.816635,
            'service_ukuran_apar' => '2 kg',
            'service_jumlah_unit' => 1,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'quote_type' => 'service_pickup',
                'cost' => $expectedCost,
                'distance_km' => round($oneWayDistance, 2),
                'round_trip_distance_km' => $roundTripDistance,
                'rate_per_km' => 3500,
                'minimum_cost' => 15000,
                'courier' => null,
                'service' => null,
                'etd' => null,
            ]);
    }

    public function test_registered_units_include_manual_admin_units_and_exclude_unfinished_orders(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081234567891',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Akhmad Rizaldy',
            'no_wa' => '081234567891',
            'alamat' => 'Jl. Monitoring',
            'alamat_maps' => 'Jl. Monitoring',
            'alamat_detail' => 'Ruko depan',
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk2Kg = Produk::create([
            'nama' => 'APAR GuardALL Powder 2 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 250000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $produk4Kg = Produk::create([
            'nama' => 'APAR GuardALL Powder 4 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '4 kg',
            'penggunaan' => 'Gudang',
            'harga' => 350000,
            'deskripsi' => 'Demo',
            'stok' => 10,
        ]);

        $finalPesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'status' => 'selesai final',
            'tanggal' => now()->subDays(10)->toDateString(),
            'total' => 350000,
            'total_harga' => 350000,
        ]);

        $pendingPesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'status' => 'diproses',
            'tanggal' => now()->subDays(2)->toDateString(),
            'total' => 250000,
            'total_harga' => 250000,
            'pembayaran_terkonfirmasi_at' => now()->subDay(),
        ]);

        $manualUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk2Kg->id,
            'no_seri' => 'AKHMAD-21052026-01',
            'ukuran' => '2 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => '2026-05-21',
            'tgl_produksi' => '2026-05-21',
            'tgl_expired' => '2026-11-21',
            'kondisi_awal' => 'layak',
        ]);

        $finishedOrderUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $finalPesanan->id,
            'produk_id' => $produk4Kg->id,
            'no_seri' => 'AKHMAD-25052026-03',
            'ukuran' => '4 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => '2026-05-25',
            'tgl_produksi' => '2026-05-25',
            'tgl_expired' => '2027-05-25',
            'kondisi_awal' => 'layak',
        ]);

        $unfinishedOrderUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'pesanan_id' => $pendingPesanan->id,
            'produk_id' => $produk2Kg->id,
            'no_seri' => 'AKHMAD-25052026-04',
            'ukuran' => '2 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => '2026-05-25',
            'tgl_produksi' => '2026-05-25',
            'tgl_expired' => '2026-11-25',
            'kondisi_awal' => 'layak',
        ]);

        $inactiveManualUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk4Kg->id,
            'no_seri' => 'AKHMAD-25052026-05',
            'ukuran' => '4 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => '2026-05-25',
            'tgl_produksi' => '2026-05-25',
            'tgl_expired' => '2027-05-25',
            'kondisi_awal' => 'tidak_aktif',
        ]);

        $hiddenManualUnit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk2Kg->id,
            'no_seri' => 'AKHMAD-25052026-06',
            'ukuran' => '2 kg',
            'bahan' => 'Dry Chemical Powder',
            'tgl_beli' => '2026-05-25',
            'tgl_produksi' => '2026-05-25',
            'tgl_expired' => '2026-11-25',
            'kondisi_awal' => 'layak',
            'hidden_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('order.create'));

        $response->assertOk();
        $response->assertSee('Lokasi Pengiriman');
        $response->assertSee('Biaya Pengiriman');
        $response->assertSee('Hitung Ongkir');
        $response->assertDontSee('RajaOngkir');
        $response->assertViewHas('registeredUnitApars', function ($units) use ($manualUnit, $finishedOrderUnit, $unfinishedOrderUnit, $inactiveManualUnit, $hiddenManualUnit) {
            $ids = collect($units)->pluck('id')->map(fn ($id) => (int) $id)->all();

            return in_array($manualUnit->id, $ids, true)
                && in_array($finishedOrderUnit->id, $ids, true)
                && !in_array($unfinishedOrderUnit->id, $ids, true)
                && !in_array($inactiveManualUnit->id, $ids, true)
                && !in_array($hiddenManualUnit->id, $ids, true)
                && count($ids) === 2;
        });
        $response->assertSee('AKHMAD-21052026-01');
        $response->assertSee('AKHMAD-25052026-03');
        $response->assertDontSee('AKHMAD-25052026-04');
        $response->assertDontSee('AKHMAD-25052026-05');
        $response->assertDontSee('AKHMAD-25052026-06');
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
