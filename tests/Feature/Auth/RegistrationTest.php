<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSee('Kembali ke Beranda')
            ->assertSee('Daftar Akun')
            ->assertSee('PD Anugrah Utama')
            ->assertSee('Sudah punya akun?');
    }

    public function test_new_users_can_register_as_customer_and_are_redirected_to_home(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Pelanggan',
            'no_telpon' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where('no_telpon', '081234567890')->first();
        $pelanggan = Pelanggan::where('user_id', $user?->id)->first();

        $this->assertNotNull($user);
        $this->assertNotNull($pelanggan);
        $this->assertSame('pelanggan', $user->role);
        $this->assertSame('081234567890', $pelanggan->no_wa);
    }
}
