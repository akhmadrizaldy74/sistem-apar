<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceVisibilityAfterPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_history_shows_invoice_button_after_payment_proof_exists(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Invoice',
            'no_telpon' => '081234567800',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Invoice',
            'no_wa' => '081234567800',
            'alamat' => 'Jl. Invoice No. 1',
            'alamat_maps' => 'Jl. Invoice No. 1',
            'alamat_detail' => 'Ruko depan',
            'status' => 'tetap',
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $user->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_DIPROSES,
            'tanggal' => now()->toDateString(),
            'total' => 500000,
            'total_harga' => 500000,
            'bukti_pembayaran' => 'bukti-pembayaran/test.jpg',
            'metode_pengiriman' => 'pickup',
        ]);

        $response = $this->actingAs($user)->get(route('riwayat-apar'));

        $response->assertOk();
        $response->assertSeeText('Lihat Invoice');
    }
}
