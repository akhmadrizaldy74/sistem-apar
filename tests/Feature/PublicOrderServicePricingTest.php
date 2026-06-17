<?php

namespace Tests\Feature;

use App\Models\JenisApar;
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

    public function test_registered_service_order_uses_price_per_selected_unit_size(): void
    {
        config(['broadcasting.default' => 'null']);

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
            'alamat_lat' => -6.889,
            'alamat_lng' => 107.610,
            'tipe_layanan' => 'service',
            'service_jenis_layanan' => 'service',
            'service_unit_status' => 'terdaftar',
            'service_purchase_group' => $purchaseDate,
            'service_unit_apar_ids' => [$unit2Kg->id, $unit4Kg->id],
            'service_paket_id' => $paketC->id,
            'service_metode_penanganan' => 'dijemput',
            'service_keluhan' => 'Mohon service lengkap.',
        ]);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(session('error'), (string) session('error'));
        $this->assertDatabaseCount('pesanans', 1);

        $pesanan = Pesanan::query()->latest('id')->firstOrFail();

        $this->assertSame('service', $pesanan->tipe);
        $this->assertSame('service', $pesanan->service_jenis_layanan);
        $this->assertSame(250000.0, (float) $pesanan->service_estimasi_biaya);
        $this->assertSame(250000.0, (float) $pesanan->total);
        $this->assertStringContainsString('2 kg', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('4 kg', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('Rp100.000', (string) $pesanan->service_keluhan);
        $this->assertStringContainsString('Rp150.000', (string) $pesanan->service_keluhan);
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

        $response = $this->actingAs($user)->get(route('order.create'));

        $response->assertOk();
        $response->assertViewHas('registeredUnitApars', function ($units) use ($manualUnit, $finishedOrderUnit, $unfinishedOrderUnit, $inactiveManualUnit) {
            $ids = collect($units)->pluck('id')->map(fn ($id) => (int) $id)->all();

            return in_array($manualUnit->id, $ids, true)
                && in_array($finishedOrderUnit->id, $ids, true)
                && !in_array($unfinishedOrderUnit->id, $ids, true)
                && !in_array($inactiveManualUnit->id, $ids, true)
                && count($ids) === 2;
        });
        $response->assertSee('AKHMAD-21052026-01');
        $response->assertSee('AKHMAD-25052026-03');
        $response->assertDontSee('AKHMAD-25052026-04');
        $response->assertDontSee('AKHMAD-25052026-05');
    }
}
