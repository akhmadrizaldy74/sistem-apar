<?php

namespace Tests\Feature\Admin;

use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajemenAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_customer_email_and_phone_and_sync_linked_pelanggan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111119',
        ]);

        $pelangganUser = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'lama@example.com',
            'no_telpon' => '081111111120',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $pelangganUser->id,
            'nama' => 'Nama Lama',
            'no_wa' => '081111111120',
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.akun.update', $pelangganUser), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'no_telpon' => '+6281111111121',
            'role' => 'pelanggan',
        ]);

        $response->assertRedirect(route('admin.akun.index'));

        $pelangganUser->refresh();
        $pelanggan->refresh();

        $this->assertSame('Nama Baru', $pelangganUser->name);
        $this->assertSame('baru@example.com', $pelangganUser->email);
        $this->assertSame('081111111121', $pelangganUser->no_telpon);
        $this->assertSame('Nama Baru', $pelanggan->nama);
        $this->assertSame('081111111121', $pelanggan->no_wa);
    }

    public function test_deleting_customer_account_keeps_pelanggan_data_and_hides_it_from_directory(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111122',
        ]);

        $pelangganUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Hapus',
            'no_telpon' => '081111111123',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $pelangganUser->id,
            'nama' => 'Pelanggan Hapus',
            'no_wa' => '081111111123',
            'alamat' => 'Jl. Aman No. 1',
            'status' => 'tetap',
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelangganUser->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'alamat_pengiriman' => $pelanggan->alamat,
            'tipe' => 'produk',
            'sumber_pesanan' => 'input_admin',
            'total' => 250000,
            'total_harga' => 250000,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => 'diproses',
            'tanggal' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.akun.destroy', $pelangganUser));

        $response->assertRedirect(route('admin.akun.index'));
        $this->assertDatabaseMissing('users', ['id' => $pelangganUser->id]);

        $pelanggan->refresh();
        $this->assertNull($pelanggan->user_id);

        $directoryResponse = $this->actingAs($admin)->get(route('admin.pelanggan.index'));
        $directoryResponse->assertOk();
        $directoryResponse->assertDontSee('Pelanggan Hapus');
    }
}
