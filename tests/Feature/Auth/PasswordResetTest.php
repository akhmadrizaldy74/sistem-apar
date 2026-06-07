<?php

namespace Tests\Feature\Auth;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertStatus(200)
            ->assertSee('Lupa Password')
            ->assertSee('Masukkan email atau nomor WhatsApp yang terdaftar. Link reset password akan dikirim ke email akun Anda.')
            ->assertSee('Kembali ke Login');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['login' => $user->email]);

        $response->assertSessionHas('success', 'Link reset password berhasil dikirim ke email pemulihan akun Anda.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_request_requires_login_identifier(): void
    {
        Notification::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'login' => '',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasErrors([
            'login' => 'Email atau nomor WhatsApp wajib diisi.',
        ]);
        Notification::assertNothingSent();
    }

    public function test_reset_password_link_can_be_requested_with_08_phone_number_when_account_has_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'reset08@example.com',
            'no_telpon' => '087830665032',
        ]);

        $response = $this->post('/forgot-password', ['login' => '0878-3066-5032']);

        $response->assertSessionHas('success', 'Link reset password berhasil dikirim ke email pemulihan akun Anda.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_can_be_requested_with_whatsapp_number_when_account_has_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'reset@example.com',
            'no_telpon' => null,
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Reset Pelanggan',
            'no_wa' => '087830665030',
            'status' => 'tetap',
        ]);

        $response = $this->post('/forgot-password', ['login' => '+6287830665030']);

        $response->assertSessionHas('success', 'Link reset password berhasil dikirim ke email pemulihan akun Anda.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_link_can_be_requested_with_62_phone_number_when_account_has_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'reset62@example.com',
            'no_telpon' => null,
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama' => 'Reset Pelanggan 62',
            'no_wa' => '087830665033',
            'status' => 'tetap',
        ]);

        $response = $this->post('/forgot-password', ['login' => '6287830665033']);

        $response->assertSessionHas('success', 'Link reset password berhasil dikirim ke email pemulihan akun Anda.');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_request_fails_when_account_is_not_found(): void
    {
        Notification::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'login' => 'tidak-terdaftar@example.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasErrors([
            'login' => 'Akun tidak ditemukan. Pastikan email atau nomor WhatsApp sudah terdaftar.',
        ]);
        $response->assertSessionMissing('success');
        Notification::assertNothingSent();
    }

    public function test_reset_password_request_warns_when_phone_match_has_no_recovery_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'email' => null,
            'no_telpon' => '087830665031',
        ]);

        $response = $this->from('/forgot-password')->post('/forgot-password', ['login' => '6287830665031']);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('warning', 'Akun belum memiliki email pemulihan. Silakan hubungi admin untuk reset password.');
        Notification::assertNothingSent();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => null,
        ]);
    }

    public function test_reset_password_request_returns_mail_configuration_message_when_delivery_fails(): void
    {
        $user = User::factory()->create([
            'email' => 'mail-gagal@example.com',
        ]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andThrow(new RuntimeException('Mailer belum siap'));

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'login' => $user->email,
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('error', 'Email reset password gagal dikirim. Periksa konfigurasi SMTP pada file .env.');
    }

    public function test_failed_mail_delivery_clears_existing_reset_token_for_retry(): void
    {
        $user = User::factory()->create([
            'email' => 'retry-reset@example.com',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => bcrypt('token-lama'),
            'created_at' => now(),
        ]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andThrow(new RuntimeException('SMTP gagal'));

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'login' => $user->email,
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('error', 'Email reset password gagal dikirim. Periksa konfigurasi SMTP pada file .env.');
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['login' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['login' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'))
                ->assertSessionHas('success', 'Password berhasil diubah. Silakan login kembali.');

            return true;
        });
    }
}
