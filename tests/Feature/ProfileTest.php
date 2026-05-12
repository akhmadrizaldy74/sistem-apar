<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()->assertSee('Profil Saya');
    }

    public function test_profile_information_can_be_updated_and_customer_address_is_saved(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '081111111111',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'no_telpon' => '628123456789',
                'alamat_maps' => 'Jl. Raya Bogor, Kota Bogor, Jawa Barat, Indonesia',
                'alamat_detail' => 'Blok A2 No. 10',
                'alamat_lat' => '-6.59503800',
                'alamat_lng' => '106.81663500',
                'alamat_provinsi' => 'Jawa Barat',
                'alamat_kota' => 'Kota Bogor',
                'alamat_kecamatan' => 'Bogor Timur',
                'alamat_kode_pos' => '16143',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('08123456789', $user->no_telpon);

        $pelanggan = Pelanggan::where('user_id', $user->id)->first();

        $this->assertNotNull($pelanggan);
        $this->assertSame('Test User', $pelanggan->nama);
        $this->assertSame('08123456789', $pelanggan->no_wa);
        $this->assertSame('Jl. Raya Bogor, Kota Bogor, Jawa Barat, Indonesia | Detail: Blok A2 No. 10', $pelanggan->alamat);
        $this->assertSame('Jl. Raya Bogor, Kota Bogor, Jawa Barat, Indonesia', $pelanggan->alamat_maps);
        $this->assertSame('Blok A2 No. 10', $pelanggan->alamat_detail);
        $this->assertSame('Jawa Barat', $pelanggan->alamat_provinsi);
        $this->assertSame('Kota Bogor', $pelanggan->alamat_kota);
        $this->assertSame('Bogor Timur', $pelanggan->alamat_kecamatan);
        $this->assertSame('16143', $pelanggan->alamat_kode_pos);
        $this->assertEquals(-6.595038, (float) $pelanggan->alamat_lat);
        $this->assertEquals(106.816635, (float) $pelanggan->alamat_lng);
    }

    public function test_existing_customer_with_same_phone_is_linked_to_the_user_profile(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '081222222222',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Lama',
            'no_wa' => '081222222222',
            'alamat' => 'Alamat lama',
            'status' => 'calon',
        ]);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Nama Baru',
                'no_telpon' => '081222222222',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $pelanggan->refresh();

        $this->assertSame($user->id, $pelanggan->user_id);
        $this->assertSame('Nama Baru', $pelanggan->nama);
    }

    public function test_email_verification_status_is_unchanged_when_profile_email_is_not_updated(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '081333333333',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'no_telpon' => '081333333333',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
