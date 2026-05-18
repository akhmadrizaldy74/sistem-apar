<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Sistem APAR'))</title>

    {{-- SEO Meta --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-anugrah.png') }}" />
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
    </style>
</head>
<body class="antialiased bg-white text-gray-900">
    @php
        $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
    @endphp

    {{-- ============================================================ --}}
    {{-- PUBLIC NAVBAR --}}
    {{-- ============================================================ --}}
    <header id="public-nav"
            class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-all duration-300"
            x-data="{ open: false, scrolled: false }"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20; document.getElementById('public-nav').classList.toggle('nav-scrolled', scrolled); })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-18">

                {{-- Brand --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-10 w-10 rounded-xl object-cover shadow-sm ring-1 ring-red-100 transition-transform group-hover:scale-105">
                    <div>
                        <p class="text-sm font-black text-gray-900 tracking-tight leading-none">PD. ANUGRAH UTAMA</p>
                        <p class="text-[9px] font-bold text-red-600 uppercase tracking-widest mt-0.5">Sistem APAR</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ url('/') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Beranda</a>
                    <a href="{{ route('produk.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Katalog</a>
                    <a href="{{ route('riwayat-apar') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-red-700 rounded-xl hover:bg-red-50 transition">Riwayat & Status APAR</a>
                </nav>

                {{-- Desktop CTA --}}
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @php
                            $user = auth()->user();
                            $dashRoute = $user->isTeknisi()
                                ? route('teknisi.dashboard')
                                : ($user->isAdmin() ? route('dashboard') : route('profile.edit'));
                            $dashLabel = $user->isAdmin() || $user->isTeknisi() ? 'Dashboard' : 'Profil Saya';
                            $cartCount = \App\Models\Keranjang::where('user_id', auth()->id())->sum('qty');
                        @endphp
                        <a href="{{ $dashRoute }}" class="px-4 py-2 text-sm font-bold text-gray-700 hover:text-red-700 rounded-xl border border-gray-200 hover:border-red-200 transition">
                            {{ $dashLabel }}
                        </a>
                    @else
                        @php $cartCount = 0; @endphp
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition">Masuk</a>
                    @endauth

                    {{-- Cart Icon with Badge (Always Visible) --}}
                    <a href="{{ auth()->check() ? route('keranjang.index') : route('login') }}" class="relative p-2.5 text-gray-600 hover:text-red-700 rounded-xl hover:bg-red-50 transition" title="Keranjang">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if(isset($cartCount) && $cartCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-md">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    @auth
                        <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 text-sm font-black rounded-xl hover:bg-gray-200 transition shadow-sm hover:-translate-y-0.5 transform flex items-center gap-2">
                            <i class="fa-solid fa-user"></i> Profil Pelanggan
                        </a>
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
             class="md:hidden border-t border-gray-100 bg-white px-4 pb-4 space-y-1 pt-2">
            <a href="{{ url('/') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Beranda</a>
            <a href="{{ route('produk.index') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Katalog</a>
            <a href="{{ route('riwayat-apar') }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:text-red-700 hover:bg-red-50 rounded-xl transition">Riwayat & Status APAR</a>
            <div class="pt-2 flex flex-col gap-2">
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

                {{-- Mobile Cart Menu (Always Visible) --}}
                <a href="{{ auth()->check() ? route('keranjang.index') : route('login') }}" class="w-full text-center px-4 py-2.5 text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Keranjang
                    @auth
                        @php $mCartCount = \App\Models\Keranjang::where('user_id', auth()->id())->sum('qty'); @endphp
                        @if($mCartCount > 0)
                            <span class="w-5 h-5 bg-red-600 text-white text-[10px] font-black rounded-full flex items-center justify-center">{{ $mCartCount > 99 ? '99+' : $mCartCount }}</span>
                        @endif
                    @endauth
                </a>

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
    <main class="pt-16">
        @if(session('success') || session('error'))
            <div class="mx-auto max-w-6xl px-4 pt-5 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                        <i class="fa-solid fa-circle-check me-2 text-emerald-600"></i>{{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-900">
                        <i class="fa-solid fa-triangle-exclamation me-2 text-red-600"></i>{{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ============================================================ --}}
    {{-- PUBLIC FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="bg-gray-950 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                {{-- Brand --}}
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-10 w-10 rounded-xl object-cover ring-1 ring-white/10">
                        <p class="text-white font-black text-sm tracking-tight">PD. ANUGRAH UTAMA</p>
                    </div>
                    <p class="text-sm leading-relaxed">Penyedia layanan APAR terpercaya di Bogor — penjualan, refill, dan service.</p>
                    <div class="flex items-center gap-3 mt-5">
                        <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-xl bg-[#25D366]/10 border border-[#25D366]/20 text-[#25D366] flex items-center justify-center hover:bg-[#25D366] hover:text-white transition">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </a>
                    </div>
                </div>

                {{-- Links --}}
                <div>
                    <h6 class="text-white font-black text-xs uppercase tracking-widest mb-5">Navigasi</h6>
                    <ul class="space-y-3">
                        <li><a href="{{ url('/') }}" class="text-sm hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('produk.index') }}" class="text-sm hover:text-white transition">Katalog Produk</a></li>
                        <li><a href="{{ route('riwayat-apar') }}" class="text-sm hover:text-white transition">Riwayat & Status APAR</a></li>
                        <li><a href="{{ $orderEntryUrl }}" class="text-sm hover:text-white transition">Pesan / Service</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h6 class="text-white font-black text-xs uppercase tracking-widest mb-5">Hubungi Kami</h6>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-[#25D366]"></i>
                            <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}" target="_blank" rel="noopener" class="hover:text-white transition">+62 851-2800-8030</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-red-500"></i>
                            <span>Bogor, Jawa Barat</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
                <p>&copy; {{ date('Y') }} PD. Anugrah Utama. Hak cipta dilindungi.</p>
                <p>Sistem Monitoring APAR</p>
            </div>
        </div>
    </footer>

    {{-- WhatsApp Float Button --}}
    <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}?text={{ urlencode('Halo, saya ingin konsultasi tentang APAR.') }}"
       target="_blank" rel="noopener noreferrer"
       class="fixed bottom-8 right-8 w-14 h-14 bg-[#25D366] rounded-2xl shadow-2xl shadow-[#25D366]/30 flex items-center justify-center text-white border border-white/10 z-50 hover:scale-110 transition-transform"
       aria-label="Chat WhatsApp">
        <svg class="w-7 h-7" viewBox="0 0 32 32" fill="currentColor">
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
