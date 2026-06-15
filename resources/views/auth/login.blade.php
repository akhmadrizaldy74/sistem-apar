<x-guest-layout variant="login-showcase">
    @php
        $waContact = preg_replace('/\D+/', '', env('WHATSAPP_CONTACT', '6285128008030')) ?: '6285128008030';
        $waMessage = urlencode('Halo PD Anugrah Utama, saya mengalami kendala saat login.');
        $inputBase = 'block w-full min-w-0 rounded-2xl border bg-white px-4 py-4 pl-12 text-base font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:bg-white';
        $loginInputClasses = $inputBase . ($errors->has('login')
            ? ' border-rose-300 focus:border-rose-400 focus:ring-4 focus:ring-rose-100'
            : ' border-slate-200 focus:border-red-500 focus:ring-4 focus:ring-red-100');
        $passwordInputClasses = str_replace('pl-12', 'pl-12 pr-12', $inputBase) . ($errors->has('password')
            ? ' border-rose-300 focus:border-rose-400 focus:ring-4 focus:ring-rose-100'
            : ' border-slate-200 focus:border-red-500 focus:ring-4 focus:ring-red-100');
    @endphp

    <div class="login-shell grid w-full min-w-0 max-w-[calc(100vw-2rem)] overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl sm:max-w-6xl md:grid-cols-[1.05fr_0.95fr] lg:min-h-[720px]">
        <section class="login-visual relative min-h-[330px] min-w-0 overflow-hidden p-6 text-white sm:min-h-[430px] sm:p-8 lg:p-10">
            <div class="login-visual-bubble one" aria-hidden="true"></div>
            <div class="login-visual-bubble two" aria-hidden="true"></div>

            <div class="relative z-10 flex h-full min-h-[330px] flex-col items-center justify-center text-center sm:min-h-[430px]">
                {{-- Favicon besar di tengah --}}
                <div class="mb-7">
                    <span class="inline-flex h-24 w-24 items-center justify-center overflow-hidden rounded-3xl shadow-[0_16px_50px_rgba(0,0,0,0.25)] ring-4 ring-white/20 sm:h-28 sm:w-28">
                        <img src="{{ asset('favicon-apar.svg') }}" alt="Logo PD Anugrah Utama" class="h-full w-full">
                    </span>
                </div>


                {{-- Nama brand besar --}}
                <h2 class="text-2xl font-black uppercase tracking-wider text-white sm:text-3xl lg:text-4xl">
                    PD Anugrah Utama
                </h2>

                {{-- Tagline singkat --}}
                <p class="mx-auto mt-3 max-w-xs text-sm leading-relaxed text-white/60">
                    Melayani penjualan, refill, dan service APAR dengan proses yang mudah.
                </p>

                {{-- 3 poin layanan ringan --}}
                <ul class="mt-8 space-y-3 text-left">
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-amber-300">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white/80">Penjualan APAR</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-amber-300">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white/80">Refill & Service APAR</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-amber-300">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-white/80">Konsultasi WhatsApp</span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="relative flex min-h-[520px] min-w-0 flex-col overflow-hidden bg-white px-6 py-7 sm:min-h-[560px] sm:px-10 lg:px-12">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="flex flex-1 items-center py-8">
                <div class="w-full min-w-0">
                    <div class="mb-8">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-red-600">Login</p>
                        <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">
                            Login ke Akun Anda
                        </h1>
                        <p class="mt-3 max-w-sm text-sm leading-7 text-slate-500">
                            Silakan masuk untuk melanjutkan pemesanan dan layanan APAR.
                        </p>
                    </div>

                    <x-auth-session-status
                        class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                        :status="session('status')"
                    />

                    @if ($errors->has('login') || $errors->has('password'))
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                            {{ $errors->first('login') ?: $errors->first('password') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Email atau Nomor WhatsApp
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6m-18 10h18a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2H3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2z" />
                                    </svg>
                                </div>
                                <input
                                    id="login"
                                    class="{{ $loginInputClasses }}"
                                    type="text"
                                    name="login"
                                    value="{{ old('login') }}"
                                    required
                                    autocomplete="username"
                                    placeholder="nama@email.com atau 08xxxxxxxxxx"
                                    aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('login')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        <div>
                            <div class="mb-2 flex min-w-0 items-center justify-between gap-4">
                                <label for="password" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a class="shrink-0 text-xs font-black text-red-600 transition hover:text-red-700 hover:underline" href="{{ route('password.request') }}">
                                        Lupa Password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    class="{{ $passwordInputClasses }}"
                                    placeholder="Masukkan password"
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                >
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-600"
                                    aria-label="Tampilkan atau sembunyikan password"
                                >
                                    <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-off-icon" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 0 1 1.563-3.029m5.858.908a3 3 0 1 1 4.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 0 1-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        <label for="remember_me" class="inline-flex items-center gap-2.5 text-sm font-semibold text-slate-600">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                @checked(old('remember'))
                                class="h-4 w-4 rounded border-slate-300 text-red-600 shadow-sm transition focus:ring-2 focus:ring-red-200"
                            >
                            Ingat Saya
                        </label>

                        <div class="pt-1">
                            <button
                                type="submit"
                                class="group inline-flex w-full items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-red-700 via-red-600 to-orange-500 px-5 py-4 text-sm font-black text-white shadow-xl shadow-red-600/25 transition hover:-translate-y-0.5 hover:shadow-red-700/30 active:translate-y-0"
                            >
                                Masuk ke Akun
                                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    @if (Route::has('register'))
                        <div class="mt-6 text-center">
                            <p class="text-sm text-slate-500">
                                Belum punya akun?
                                <a href="{{ route('register') }}" class="font-black text-red-600 transition hover:text-red-700">
                                    Daftar Sekarang
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center text-xs font-semibold text-slate-500 sm:text-right">
                Jika ada masalah,
                <a href="https://wa.me/{{ $waContact }}?text={{ $waMessage }}" target="_blank" rel="noopener noreferrer" class="font-black text-red-600 transition hover:text-red-700 hover:underline">
                    hubungi WhatsApp
                </a>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');

            if (!toggleBtn || !passwordInput) {
                return;
            }

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                eyeIcon.classList.toggle('hidden', isHidden);
                eyeOffIcon.classList.toggle('hidden', !isHidden);
            });
        });
    </script>
</x-guest-layout>
