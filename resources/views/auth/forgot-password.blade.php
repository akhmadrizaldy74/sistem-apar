<x-guest-layout variant="login-showcase">
    @php
        $inputBase = 'block w-full min-w-0 rounded-2xl border bg-white px-4 py-4 pl-12 text-base font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:bg-white border-slate-200 focus:border-red-500 focus:ring-4 focus:ring-red-100';
        $loginInputClasses = $inputBase . ($errors->has('login')
            ? ' border-rose-300 focus:border-rose-400 focus:ring-4 focus:ring-rose-100'
            : ' border-slate-200 focus:border-red-500 focus:ring-4 focus:ring-red-100');
    @endphp

    <div class="login-shell grid w-full min-w-0 max-w-[calc(100vw-2rem)] overflow-hidden rounded-[2rem] border border-white/70 bg-white shadow-2xl sm:max-w-6xl md:grid-cols-[1.05fr_0.95fr] lg:min-h-[720px]">
        {{-- Panel kiri: branding --}}
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

        {{-- Panel kanan: form lupa password --}}
        <section class="relative flex min-h-[520px] min-w-0 flex-col overflow-hidden bg-white px-6 py-7 sm:min-h-[560px] sm:px-10 lg:px-12">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Login
                </a>
            </div>

            <div class="flex flex-1 items-center py-8">
                <div class="w-full min-w-0">
                    <div class="mb-8">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-red-600">Reset Password</p>
                        <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">
                            Lupa Password
                        </h1>
                        <p class="mt-3 max-w-sm text-sm leading-7 text-slate-500">
                            Masukkan email atau nomor WhatsApp yang terdaftar. Jika akun memiliki email pemulihan, link reset akan dikirim ke email tersebut.
                        </p>
                    </div>

                    <x-auth-session-status
                        class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                        :status="session('status')"
                    />

                    @if ($errors->has('login'))
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                            {{ $errors->first('login') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Email atau Nomor WhatsApp
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9 6 9-6m-18 10h18a2 2 0 002-2V9a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input
                                    id="login"
                                    class="{{ $loginInputClasses }}"
                                    type="text"
                                    name="login"
                                    value="{{ old('login') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nama@email.com atau 08xxxxxxxxxx"
                                    aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('login')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        {{-- Submit --}}
                        <div class="pt-1">
                            <button
                                type="submit"
                                class="group inline-flex w-full items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-red-700 via-red-600 to-orange-500 px-5 py-4 text-sm font-black text-white shadow-xl shadow-red-600/25 transition hover:-translate-y-0.5 hover:shadow-red-700/30 active:translate-y-0"
                            >
                                Kirim Link Reset Password
                                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}" class="text-sm font-black text-red-600 transition hover:text-red-700">
                            Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
