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

    public function test_registration_still_works_when_session_contains_deleted_customer_id(): void
    {
        $deletedUser = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => '081200000001',
        ]);

        $deletedUserId = $deletedUser->id;
        $deletedUser->delete();

        $response = $this
            ->withSession([
                app('auth')->guard()->getName() => $deletedUserId,
            ])
            ->post('/register', [
                'name' => 'Pelanggan Baru',
                'no_telpon' => '081234567891',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));

        $user = User::where('no_telpon', '081234567891')->first();
        $pelanggan = Pelanggan::where('user_id', $user?->id)->first();

        $this->assertNotNull($user);
        $this->assertNotNull($pelanggan);
        $this->assertDatabaseHas('activity_logs', [
            'description' => 'Membuat Pelanggan #' . $pelanggan->id,
            'user_id' => null,
        ]);
    }
}
