<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Testimoni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_confirm_received_before_submitting_review(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Final',
            'no_telpon' => '081234567801',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Final',
            'no_wa' => '081234567801',
            'alamat' => 'Jl. Final No. 2',
            'alamat_maps' => 'Jl. Final No. 2',
            'alamat_detail' => 'Gudang samping',
            'status' => 'tetap',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $user->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_SIAP_DIKIRIM,
            'tanggal' => now()->toDateString(),
            'total' => 725000,
            'total_harga' => 725000,
            'pembayaran_terkonfirmasi_at' => now()->subDay(),
            'metode_pengiriman' => 'diantar_internal',
        ]);

        $confirmResponse = $this->actingAs($user)->postJson(route('riwayat-apar.confirm-received', $pesanan));

        $confirmResponse->assertOk()
            ->assertJson([
                'success' => true,
                'open_review' => true,
            ]);

        $this->assertNotNull($pesanan->fresh()->customer_confirmed_at);
        $this->assertSame(Pesanan::STATUS_SELESAI_FINAL, $pesanan->fresh()->status);

        $reviewResponse = $this->actingAs($user)->postJson(route('testimoni.store'), [
            'pesanan_id' => $pesanan->id,
            'rating' => 5,
            'review' => 'Pesanan sudah diterima dan layanan memuaskan.',
            'is_anonymous' => false,
        ]);

        $reviewResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $pesanan->refresh();

        $this->assertNotNull($pesanan->testimonial_submitted_at);
        $this->assertDatabaseHas('testimonis', [
            'pelanggan_id' => $pelanggan->id,
            'transaksi_type' => Pesanan::class,
            'transaksi_id' => $pesanan->id,
            'rating' => 5,
        ]);

        $this->assertInstanceOf(Testimoni::class, Testimoni::query()->latest('id')->first());
    }
}
