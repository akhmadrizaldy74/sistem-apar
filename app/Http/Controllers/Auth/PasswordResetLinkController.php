<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Email atau nomor WhatsApp wajib diisi.',
        ]);

        $identifier = trim((string) $validated['login']);
        $user = null;

        if (str_contains($identifier, '@')) {
            if (! filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return back()
                    ->withInput($request->only('login'))
                    ->withErrors(['login' => 'Masukkan email atau nomor WhatsApp yang valid.']);
            }

            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($identifier)])
                ->first();
        } else {
            $normalizedPhone = PhoneNumber::normalize($identifier);

            if (! $normalizedPhone) {
                return back()
                    ->withInput($request->only('login'))
                    ->withErrors(['login' => 'Masukkan email atau nomor WhatsApp yang valid.']);
            }

            $user = User::findByPhone($normalizedPhone);
        }

        if (! $user) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Akun tidak ditemukan. Pastikan email atau nomor WhatsApp sudah terdaftar.']);
        }

        if (blank($user->email)) {
            return back()
                ->withInput($request->only('login'))
                ->with('warning', 'Akun belum memiliki email pemulihan. Silakan hubungi admin untuk reset password.');
        }

        try {
            // Untuk testing lokal saat MAIL_MAILER=log, Laravel akan menulis link
            // reset password ke storage/logs/laravel.log alih-alih mengirim email asli.
            $status = Password::sendResetLink([
                'email' => $user->email,
            ]);
        } catch (Throwable $exception) {
            $this->deleteResetToken($user->email);
            report($exception);

            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Email reset password gagal dikirim. Periksa konfigurasi SMTP pada file .env.');
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Link reset password berhasil dikirim ke email pemulihan akun Anda.');
        }

        if (defined(Password::class.'::RESET_THROTTLED') && $status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('login'))
                ->with('warning', 'Link reset password baru saja dikirim. Silakan tunggu beberapa saat sebelum mencoba lagi.');
        }

        return back()
            ->withInput($request->only('login'))
            ->with('error', 'Email reset password gagal dikirim. Periksa konfigurasi SMTP pada file .env.');
    }

    private function deleteResetToken(string $email): void
    {
        $broker = config('auth.defaults.passwords', 'users');
        $table = config("auth.passwords.{$broker}.table", 'password_reset_tokens');

        DB::table($table)->where('email', $email)->delete();
    }
}
