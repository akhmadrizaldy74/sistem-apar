<?php

namespace Tests\Feature\Admin;

use App\Models\Pelanggan;
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
}
