<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '081111111111',
        ]);

        $response = $this->post('/login', [
            'login' => $user->no_telpon,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_pelanggan_can_authenticate_using_email(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'pelanggan@example.com',
            'no_telpon' => '081111111115',
        ]);

        $response = $this->post('/login', [
            'login' => 'pelanggan@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_authenticate_using_plus_62_phone_number(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '087830665027',
        ]);

        $response = $this->post('/login', [
            'login' => '+6287830665027',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_authenticate_using_62_phone_number(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '087830665028',
        ]);

        $response = $this->post('/login', [
            'login' => '6287830665028',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_authenticate_using_linked_pelanggan_phone_number(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'no_telpon' => null,
            'email' => 'linked@example.com',
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Pelanggan Terkait',
            'no_wa' => '087830665029',
            'status' => 'tetap',
        ]);

        $response = $this->post('/login', [
            'login' => '0878-3066-5029',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_admin_users_are_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111112',
        ]);

        $response = $this->post('/login', [
            'login' => $admin->no_telpon,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_technician_users_are_redirected_to_technician_dashboard(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
            'no_telpon' => '081111111113',
        ]);

        $response = $this->post('/login', [
            'login' => $teknisi->no_telpon,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teknisi.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'no_telpon' => '081111111114',
        ]);

        $this->post('/login', [
            'login' => $user->no_telpon,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
