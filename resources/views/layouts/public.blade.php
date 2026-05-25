<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Sistem APAR'))</title>

    {{-- SEO Meta --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon-apar.svg') }}" />
    <meta name="description" content="PD. Anugrah Utama - Sistem Monitoring APAR (Alat Pemadam Api Ringan). Penyedia layanan penjualan, isi ulang, dan service APAR terpercaya di Bogor." />
    <meta name="keywords" content="APAR, Alat Pemadam Api Ringan, service APAR, refill APAR, monitoring APAR, fire extinguisher, PD. Anugrah Utama, Bogor" />
    <meta property="og:title" content="PD. Anugrah Utama - Sistem Monitoring APAR" />
    <meta property="og:description" content="Platform pencatatan dan monitoring APAR untuk operasional toko: data pelanggan, riwayat service & refill, katalog produk." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="PD. Anugrah Utama - Sistem Monitoring APAR" />
    <meta name="twitter:description" content="Platform pencatatan dan monitoring APAR untuk operasional toko." />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Leaflet Map --}}
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @yield('styles')

    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }

        /* Navbar scroll effect */
        .nav-scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 1px 20px rgba(0,0,0,0.08) !important;
        }

        /* Animate on scroll */
        [data-reveal] {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        [data-reveal].revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Progress bar animation */
        .progress-bar {
            width: 0%;
            transition: width 1.2s ease;
        }

        /* Solid High-Contrast Footer */
        footer.public-footer {
            background-color: #0b1220 !important;
            color: #e2e8f0 !important; /* text-slate-200 */
            opacity: 1 !important;
            filter: none !important;
            border-top: 1px solid #334155 !important; /* border-slate-700 */
        }
        footer.public-footer a {
            color: #cbd5e1 !important; /* text-slate-300 */
            opacity: 1 !important;
            transition: color 0.15s ease-in-out;
        }
        footer.public-footer a:hover {
            color: #f87171 !important; /* text-red-400 on hover */
        }
        footer.public-footer h6,
        footer.public-footer p.font-bold,
        footer.public-footer .text-white {
            color: #ffffff !important;
            opacity: 1 !important;
        }
        footer.public-footer p,
        footer.public-footer span,
        footer.public-footer li {
            color: #e2e8f0 !important; /* text-slate-200 */
            opacity: 1 !important;
        }
        footer.public-footer .border-slate-800 {
            border-color: #334155 !important; /* border-slate-700 */
        }
        footer.public-footer .bg-slate-900 {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        footer.public-footer .text-red-300 {
            color: #fca5a5 !important;
        }
        footer.public-footer .text-slate-400 {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="antialiased bg-white text-gray-900 tailadmin-public overflow-x-hidden">
    @php
        $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
    @endphp

    {{-- ============================================================ --}}
    {{-- PUBLIC NAVBAR --}}
    {{-- ============================================================ --}}
    <header id="public-nav"
            class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-all duration-300"
            x-data="{ open: false, scrolled: false }"
            @keydown.escape.window="open = false"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20; document.getElementById('public-nav').classList.toggle('nav-scrolled', scrolled); })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-18">

                {{-- Brand --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-10 w-10 rounded-xl object-cover shadow-sm ring-1 ring-red-100 transition-transform group-hover:scale-105">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black tracking-tight leading-none text-gray-900 sm:text-sm">PD. ANUGRAH UTAMA</p>
                        <p class="mt-0.5 hidden text-[9px] font-bold uppercase tracking-widest text-red-600 sm:block">Sistem APAR</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ url('/') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Beranda</a>
                    <a href="{{ route('produk.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Produk</a>
                    <a href="{{ route('riwayat-apar') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Riwayat & Status APAR</a>
                </nav>

                {{-- Desktop CTA --}}
                <div class="hidden md:flex items-center gap-3">
                    @php
                        $cartCount = auth()->check() ? \App\Support\SessionCart::count() : 0;
                        $user = auth()->user();
                        $dashRoute = '';
                        $dashLabel = 'Masuk';
                        if ($user) {
                            $dashRoute = $user->isTeknisi()
                                ? route('teknisi.dashboard')
                                : ($user->isAdmin() ? route('dashboard') : route('profile.edit'));
                            $dashLabel = $user->isAdmin() || $user->isTeknisi() ? 'Dashboard' : 'Profil Saya';
                        }
                    @endphp

                    {{-- Cart Icon with Badge (Always Visible) --}}
                    <a href="{{ auth()->check() ? route('keranjang.index') : route('login') }}" class="relative p-2.5 text-gray-600 hover:text-red-700 rounded-xl hover:bg-red-50 transition" title="Keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-md">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    @auth
                        <a href="{{ $dashRoute }}" class="px-4 py-2 text-sm font-bold text-gray-700 hover:text-red-700 rounded-xl border border-gray-200 hover:border-red-200 transition">
                            {{ $dashLabel }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition">Masuk</a>
                    @endauth

                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-red-600 hover:text-red-800 hover:bg-red-50 rounded-xl transition">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Keluar
                            </button>
                        </form>
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="open = !open" class="md:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="md:hidden border-t border-gray-100 bg-white px-4 pb-4 pt-2 space-y-2">
            <a href="{{ url('/') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Beranda</a>
            <a href="{{ route('produk.index') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Produk</a>
            <a href="{{ route('riwayat-apar') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Riwayat & Status APAR</a>
            <div class="pt-2 flex flex-col gap-2">
                {{-- Mobile Cart Menu (Always Visible) --}}
                <a href="{{ auth()->check() ? route('keranjang.index') : route('login') }}" class="w-full text-center px-4 py-2.5 text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Keranjang
                    @auth
                        @php $mCartCount = \App\Support\SessionCart::count(); @endphp
                        @if($mCartCount > 0)
                            <span class="w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center">{{ $mCartCount > 99 ? '99+' : $mCartCount }}</span>
                        @endif
                    @endauth
                </a>

                @auth
                    @php
                        $mobileUser = auth()->user();
                        $mobileDashRoute = $mobileUser->isTeknisi()
                            ? route('teknisi.dashboard')
                            : ($mobileUser->isAdmin() ? route('dashboard') : route('profile.edit'));
                        $mobileDashLabel = $mobileUser->isAdmin() || $mobileUser->isTeknisi() ? 'Dashboard' : 'Profil Saya';
                    @endphp
                    <a href="{{ $mobileDashRoute }}" class="w-full text-center px-4 py-2.5 text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition">{{ $mobileDashLabel }}</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2.5 text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition">Masuk</a>
                @endauth

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-center px-4 py-2.5 text-sm font-bold text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition">
                            <i class="fa-solid fa-right-from-bracket me-1.5"></i> Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================================ --}}
    <main class="pt-16 overflow-x-hidden">
        @yield('content')
    </main>

    {{-- ============================================================ --}}
    {{-- PUBLIC FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="public-footer border-t border-slate-800 bg-slate-950 text-slate-200">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-2 xl:grid-cols-4">
                {{-- Brand --}}
                <div class="xl:pr-6">
                    <div class="mb-4 flex items-center gap-3">
                        <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-11 w-11 rounded-2xl object-cover ring-1 ring-white/10">
                        <div>
                            <p class="text-sm font-black tracking-tight text-white">PD. ANUGRAH UTAMA</p>
                            <p class="mt-0.5 text-[10px] font-black uppercase tracking-[0.28em] text-red-300">Sistem APAR</p>
                        </div>
                    </div>
                    <p class="text-sm leading-7 text-slate-300">Penyedia layanan APAR untuk penjualan produk, refill, service, inspeksi, dan monitoring riwayat unit APAR pelanggan.</p>
                    <div class="mt-5 flex items-center gap-3">
                        <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-400/20 bg-emerald-500/10 text-emerald-300 transition hover:bg-emerald-500 hover:text-white">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </a>
                        <span class="rounded-2xl border border-slate-800 bg-slate-900 px-3 py-2 text-xs font-bold text-slate-300">Bogor, Jawa Barat</span>
                    </div>
                </div>

                {{-- Links --}}
                <div>
                    <h6 class="mb-5 text-xs font-black uppercase tracking-[0.25em] text-white">Navigasi</h6>
                    <ul class="space-y-3">
                        <li><a href="{{ url('/') }}" class="text-sm text-slate-300 transition hover:text-white">Beranda</a></li>
                        <li><a href="{{ route('produk.index') }}" class="text-sm text-slate-300 transition hover:text-white">Produk</a></li>
                        <li><a href="{{ route('riwayat-apar') }}" class="text-sm text-slate-300 transition hover:text-white">Riwayat & Status APAR</a></li>
                        <li><a href="{{ $orderEntryUrl }}" class="text-sm text-slate-300 transition hover:text-white">Pesan / Checkout</a></li>
                    </ul>
                </div>

                <div>
                    <h6 class="mb-5 text-xs font-black uppercase tracking-[0.25em] text-white">Layanan</h6>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li>Penjualan APAR</li>
                        <li>Refill APAR</li>
                        <li>Service APAR</li>
                        <li>Inspeksi & Testing</li>
                        <li>Konsultasi APAR</li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h6 class="mb-5 text-xs font-black uppercase tracking-[0.25em] text-white">Kontak</h6>
                    <ul class="space-y-4 text-sm text-slate-300">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-2xl bg-red-500/10 text-red-300">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <div>
                                <p class="font-bold text-white">Lokasi</p>
                                <p class="mt-1 leading-6 text-slate-300">Bogor, Jawa Barat</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-300">
                                <i class="fa-brands fa-whatsapp"></i>
                            </span>
                            <div>
                                <p class="font-bold text-white">WhatsApp</p>
                                <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block leading-6 text-slate-300 transition hover:text-white">+62 851-2800-8030</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-300">
                                <i class="fa-solid fa-clock"></i>
                            </span>
                            <div>
                                <p class="font-bold text-white">Jam Operasional</p>
                                <p class="mt-1 leading-6 text-slate-300">Senin - Sabtu, 08.00 - 17.00 WIB</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-slate-800 pt-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} PD. Anugrah Utama. Hak cipta dilindungi.</p>
                <p>Sistem APAR untuk operasional toko, layanan, dan riwayat unit pelanggan.</p>
            </div>
        </div>
    </footer>

    {{-- WhatsApp Float Button --}}
    <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}?text={{ urlencode('Halo, saya ingin konsultasi tentang APAR.') }}"
       target="_blank" rel="noopener noreferrer"
       class="fixed bottom-4 right-4 z-50 flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-[#25D366] text-white shadow-2xl shadow-[#25D366]/30 transition-transform hover:scale-110 sm:bottom-8 sm:right-8 sm:h-14 sm:w-14"
       aria-label="Chat WhatsApp">
        <svg class="h-6 w-6 sm:h-7 sm:w-7" viewBox="0 0 32 32" fill="currentColor">
            <path d="M19.11 17.35c-.27-.14-1.58-.78-1.83-.87-.24-.09-.43-.14-.61.14-.18.27-.7.87-.86 1.05-.16.18-.32.2-.59.07-.27-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.64-1.53-1.91-.16-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.02-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.48.07-.73.34-.24.27-.95.92-.95 2.25 0 1.33.97 2.61 1.11 2.79.14.18 1.91 2.93 4.63 4.11.65.28 1.15.45 1.54.58.65.21 1.24.18 1.71.11.52-.08 1.58-.65 1.8-1.27.22-.62.22-1.15.16-1.27-.07-.12-.24-.2-.5-.34z"/>
            <path d="M16 3C9.38 3 4 8.38 4 15c0 2.11.55 4.17 1.6 5.99L4 29l8.2-1.56A11.9 11.9 0 0016 27c6.62 0 12-5.38 12-12S22.62 3 16 3zm0 21.83c-1.84 0-3.64-.49-5.22-1.42l-.37-.22-4.86.92.93-4.74-.24-.38A9.85 9.85 0 016.17 15c0-5.42 4.41-9.83 9.83-9.83S25.83 9.58 25.83 15 21.42 24.83 16 24.83z"/>
        </svg>
    </a>

    {{-- Reveal Animation Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Scroll reveal
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));

            // Progress bar animation
            document.querySelectorAll('[data-progress]').forEach(bar => {
                setTimeout(() => {
                    bar.style.width = bar.getAttribute('data-progress') + '%';
                }, 600);
            });
        });
    </script>

    {{-- Leaflet Map Init --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var mapEl = document.getElementById('location-map');
            if (!mapEl) return;

            // Koordinat PD. Anugrah Utama area Bogor
            var lat  = -6.5971;
            var lng  = 106.8060;

            var map = L.map('location-map', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);

            var icon = L.divIcon({
                html: '<div style="background:#b91c1c;width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 4px 12px rgba(185,28,28,0.4);"></div>',
                iconAnchor: [18, 36],
                popupAnchor: [0, -36],
                className: ''
            });

            L.marker([lat, lng], { icon: icon })
                .addTo(map)
                .bindPopup('<strong style="font-size:13px;">PD. Anugrah Utama</strong><br><span style="font-size:12px;color:#555;">Jl. Raya Bogor, Kota Bogor</span><br><a href="https://maps.google.com/?q=' + lat + ',' + lng + '" target="_blank" style="font-size:11px;color:#b91c1c;font-weight:600;">Buka di Google Maps →</a>')
                .openPopup();
        });
    </script>

    @stack('scripts')
</body>
</html>
