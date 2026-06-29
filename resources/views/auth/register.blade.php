<x-guest-layout variant="login-showcase">
    @php
        $inputBase = 'block w-full min-w-0 rounded-2xl border bg-white px-4 py-4 pl-12 text-base font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:bg-white border-slate-200 focus:border-red-500 focus:ring-4 focus:ring-red-100';
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

        {{-- Panel kanan: form register --}}
        <section class="relative flex min-h-[520px] min-w-0 flex-col overflow-hidden bg-white px-6 py-7 sm:min-h-[560px] sm:px-10 lg:px-12">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-red-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="flex flex-1 items-center py-6">
                <div class="w-full min-w-0">
                    <div class="mb-7">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-red-600">Daftar</p>
                        <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">
                            Buat Akun Baru
                        </h1>
                        <p class="mt-3 max-w-sm text-sm leading-7 text-slate-500">
                            Buat akun untuk pemesanan APAR dan pantau riwayat layanan Anda.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <label for="name" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Nama Lengkap
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.51 0 4.865.661 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <input
                                    id="name"
                                    class="{{ $inputBase }}"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Masukkan nama lengkap"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>


                        {{-- Phone --}}
                        <div>
                            <label for="no_telpon" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Nomor WhatsApp
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a2 2 0 011.98 1.728l.418 2.928a2 2 0 01-.546 1.707l-1.54 1.54a16 16 0 006.364 6.364l1.54-1.54a2 2 0 011.707-.546l2.928.418A2 2 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input
                                    id="no_telpon"
                                    class="{{ $inputBase }}"
                                    type="tel"
                                    name="no_telpon"
                                    value="{{ old('no_telpon') }}"
                                    required
                                    autocomplete="tel"
                                    placeholder="08xxxxxxxxxx"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('no_telpon')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <label for="password" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Password
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    class="{{ $inputBase }}"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Buat password"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        {{-- Confirm password --}}
                        <div>
                            <label for="password_confirmation" class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                Konfirmasi Password
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <input
                                    id="password_confirmation"
                                    class="{{ $inputBase }}"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-sm font-medium text-rose-600" />
                        </div>

                        {{-- Submit --}}
                        <div class="pt-1">
                            <button
                                type="submit"
                                class="group inline-flex w-full items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-red-700 via-red-600 to-orange-500 px-5 py-4 text-sm font-black text-white shadow-xl shadow-red-600/25 transition hover:-translate-y-0.5 hover:shadow-red-700/30 active:translate-y-0"
                            >
                                Daftar Sekarang
                                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-500">
                            Sudah punya akun?
                            <a href="{{ route('login') }}" class="font-black text-red-600 transition hover:text-red-700">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
