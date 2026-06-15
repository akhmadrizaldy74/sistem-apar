<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderPickupCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickup_checkout_forces_zero_shipping_and_creates_order_details(): void
    {
        config(['broadcasting.default' => 'null']);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081234567890',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Pickup',
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Pickup No. 1',
            'alamat_maps' => 'Jl. Pickup No. 1',
            'alamat_detail' => 'Gudang depan',
            'alamat_lat' => -6.20000000,
            'alamat_lng' => 106.81666600,
            'status' => 'tetap',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 3 Kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 kg',
            'penggunaan' => 'Kantor',
            'harga' => 350000,
            'deskripsi' => 'Produk uji pickup',
            'stok' => 10,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 10,
            'sisa_qty' => 10,
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Batch uji pickup',
        ]);

        $response = $this->actingAs($user)->post(route('order.store'), [
            'nama' => $pelanggan->nama,
            'no_wa' => $pelanggan->no_wa,
            'alamat_maps' => $pelanggan->alamat_maps,
            'alamat_detail' => $pelanggan->alamat_detail,
            'alamat_lat' => $pelanggan->alamat_lat,
            'alamat_lng' => $pelanggan->alamat_lng,
            'tipe_layanan' => 'beli',
            'metode_pengiriman' => 'pickup',
            'bank_tujuan' => 'bca',
            'submit_source' => 'normal',
            'ongkir' => 999999,
            'shipping_distance_km' => 999,
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2,
                ],
            ],
        ]);

        $pesanan = Pesanan::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->where('tipe', 'produk')
            ->latest('id')
            ->first();

        $this->assertNotNull($pesanan);
        $response->assertRedirect(route('order.payment', $pesanan));

        $pesanan->load('details');

        $this->assertSame('pickup', $pesanan->metode_pengiriman);
        $this->assertSame(0.0, (float) $pesanan->ongkir);
        $this->assertNull($pesanan->shipping_distance_km);
        $this->assertCount(1, $pesanan->details);
        $this->assertSame(2, (int) $pesanan->details->first()->jumlah);
        $this->assertSame(700000.0, (float) $pesanan->total_harga);
    }
}
