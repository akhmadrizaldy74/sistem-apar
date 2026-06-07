<?php

namespace Tests\Feature\Admin;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_pelanggan_with_email_and_linked_customer_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111130',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pelanggan.store'), [
            'nama' => 'Pelanggan Email',
            'no_wa' => '+6281234567800',
            'email' => 'pelanggan-email@example.com',
            'alamat_maps' => 'Jl. Raya Contoh No. 1',
            'alamat_detail' => 'Ruko depan gerbang',
            'alamat_lat' => '-6.20000000',
            'alamat_lng' => '106.80000000',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bogor',
            'alamat_kecamatan' => 'Bogor Barat',
            'alamat_kode_pos' => '16111',
            'sumber_data' => 'manual',
            'kategori_pelanggan' => 'baru_manual',
        ]);

        $response->assertRedirect(route('admin.pelanggan.index'));

        $user = User::where('email', 'pelanggan-email@example.com')->first();
        $pelanggan = Pelanggan::where('no_wa', '081234567800')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($pelanggan);
        $this->assertSame('pelanggan', $user->role);
        $this->assertSame('081234567800', $user->no_telpon);
        $this->assertSame($user->id, $pelanggan->user_id);
    }

    public function test_admin_can_update_pelanggan_email_from_pelanggan_form_and_create_linked_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111131',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Lama',
            'no_wa' => '081234567801',
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pelanggan.update', $pelanggan), [
            'nama' => 'Pelanggan Lama Update',
            'no_wa' => '0812-3456-7801',
            'email' => 'pelanggan-lama@example.com',
            'alamat_maps' => 'Jl. Update No. 2',
            'alamat_detail' => 'Sebelah minimarket',
            'alamat_lat' => '-6.21000000',
            'alamat_lng' => '106.81000000',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bogor',
            'alamat_kecamatan' => 'Bogor Selatan',
            'alamat_kode_pos' => '16122',
        ]);

        $response->assertRedirect(route('admin.pelanggan.edit', $pelanggan));

        $pelanggan->refresh();
        $user = $pelanggan->user;

        $this->assertNotNull($user);
        $this->assertSame('pelanggan-lama@example.com', $user->email);
        $this->assertSame('081234567801', $user->no_telpon);
        $this->assertSame('Pelanggan Lama Update', $user->name);
    }
}
