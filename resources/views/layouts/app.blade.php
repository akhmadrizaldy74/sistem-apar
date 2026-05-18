<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Leaflet Global --}}
        <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
        <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

        <title>{{ config('app.name', 'Sistem APAR') }}</title>
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔥</text></svg>" />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')

        <!-- Alpine Sidebar State Manager -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    isExpanded: window.innerWidth >= 1280,
                    isMobileOpen: false,
                    isHovered: false,

                    toggleExpanded() {
                        this.isExpanded = !this.isExpanded;
                        this.isMobileOpen = false;
                    },
                    toggleMobileOpen() {
                        this.isMobileOpen = !this.isMobileOpen;
                    },
                    setMobileOpen(val) {
                        this.isMobileOpen = val;
                    },
                    setHovered(val) {
                        if (window.innerWidth >= 1280 && !this.isExpanded) {
                            this.isHovered = val;
                        }
                    }
                });

                Alpine.store('darkMode', {
                    on: localStorage.getItem('dark-mode') === 'true',
                    toggle() {
                        this.on = !this.on;
                        localStorage.setItem('dark-mode', this.on ? 'true' : 'false');
                        if (this.on) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    },
                    init() {
                        if (this.on) {
                            document.documentElement.classList.add('dark');
                        }
                    }
                });
            });
        </script>
        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            [x-cloak] { display: none !important; }

            /* ========= DARK MODE — COMPREHENSIVE ========= */
            .dark { --tw-bg-opacity: 1; }

            /* Base */
            .dark body, .dark body.antialiased { background-color: #0f172a !important; color: #e2e8f0; }

            /* White / near-white backgrounds */
            .dark .bg-white  { background-color: #1e293b !important; }
            .dark .bg-white\/80, .dark .bg-white\/95, .dark .bg-white\/90 { background-color: rgba(30,41,59,0.9) !important; }

            /* Slate backgrounds */
            .dark .bg-slate-50  { background-color: #1e293b !important; }
            .dark .bg-slate-100 { background-color: #334155 !important; }
            .dark .bg-slate-200 { background-color: #475569 !important; }
            .dark .bg-slate-800 { background-color: #0f172a !important; }
            .dark .bg-slate-900 { background-color: #020617 !important; }

            /* Gray backgrounds */
            .dark .bg-gray-50  { background-color: #1e293b !important; }
            .dark .bg-gray-100 { background-color: #334155 !important; }

            /* Colored tint backgrounds — darken them */
            .dark .bg-emerald-50 { background-color: #022c22 !important; }
            .dark .bg-blue-50    { background-color: #0c1a2e !important; }
            .dark .bg-amber-50   { background-color: #1c0f00 !important; }
            .dark .bg-red-50     { background-color: #1c0202 !important; }
            .dark .bg-sky-50     { background-color: #0c1a2e !important; }
            .dark .bg-violet-50  { background-color: #13002b !important; }
            .dark .bg-indigo-50  { background-color: #0d1526 !important; }
            .dark .bg-orange-50  { background-color: #1c0a00 !important; }
            .dark .bg-pink-50    { background-color: #1c0010 !important; }
            .dark .bg-purple-50  { background-color: #13002b !important; }

            /* Text — Slate scale */
            .dark .text-slate-900 { color: #f1f5f9 !important; }
            .dark .text-slate-800 { color: #e2e8f0 !important; }
            .dark .text-slate-700 { color: #cbd5e1 !important; }
            .dark .text-slate-600 { color: #94a3b8 !important; }
            .dark .text-slate-500 { color: #94a3b8 !important; }
            .dark .text-slate-400 { color: #64748b !important; }

            /* Text — Gray scale */
            .dark .text-gray-900 { color: #f1f5f9 !important; }
            .dark .text-gray-800 { color: #e2e8f0 !important; }
            .dark .text-gray-700 { color: #cbd5e1 !important; }
            .dark .text-gray-600 { color: #94a3b8 !important; }
            .dark .text-gray-500 { color: #94a3b8 !important; }
            .dark .text-gray-400 { color: #64748b !important; }

            /* Borders */
            .dark .border-slate-100 { border-color: #1e293b !important; }
            .dark .border-slate-200 { border-color: #334155 !important; }
            .dark .border-slate-300 { border-color: #475569 !important; }
            .dark .border-gray-100  { border-color: #1e293b !important; }
            .dark .border-gray-200  { border-color: #334155 !important; }

            /* Divide lines */
            .dark .divide-slate-50  > * + * { border-color: #1e293b !important; }
            .dark .divide-slate-100 > * + * { border-color: #334155 !important; }
            .dark .divide-gray-100  > * + * { border-color: #334155 !important; }

            /* Shadows */
            .dark .shadow-sm  { box-shadow: 0 1px 4px rgba(0,0,0,0.5) !important; }
            .dark .shadow     { box-shadow: 0 2px 8px rgba(0,0,0,0.5) !important; }
            .dark .shadow-md  { box-shadow: 0 4px 12px rgba(0,0,0,0.6) !important; }
            .dark .shadow-lg  { box-shadow: 0 6px 20px rgba(0,0,0,0.6) !important; }
            .dark .shadow-xl  { box-shadow: 0 10px 30px rgba(0,0,0,0.6) !important; }

            /* Form elements */
            .dark input, .dark select, .dark textarea {
                background-color: #1e293b !important;
                color: #f1f5f9 !important;
                border-color: #334155 !important;
            }
            .dark input::placeholder, .dark textarea::placeholder { color: #64748b !important; }
            .dark input:focus, .dark select:focus, .dark textarea:focus { border-color: #dc2626 !important; }

            /* Tables */
            .dark thead { background-color: #0f172a !important; }
            .dark thead th { color: #94a3b8 !important; border-color: #334155 !important; }
            .dark tbody td { border-color: #1e293b !important; color: #e2e8f0 !important; }
            .dark tbody tr { background-color: #1e293b !important; }
            .dark tbody tr:hover { background-color: #334155 !important; }
            .dark table  { border-color: #334155 !important; }

            /* Header / Topbar */
            .dark header.sticky { background-color: rgba(15,23,42,0.9) !important; border-color: #334155 !important; }

            /* Sidebar */
            .dark aside#sidebar { background-color: #0f172a !important; border-color: #1e293b !important; }

            /* Sidebar collapsed state */
            #sidebar .sidebar-label,
            #sidebar .sidebar-group-label,
            #sidebar .sidebar-footer-text {
                display: inline-block;
                min-width: 0;
                white-space: nowrap;
                transition: opacity .18s ease, max-width .18s ease, width .18s ease, margin .18s ease, visibility .18s ease;
            }
            #sidebar.sidebar-collapsed .sidebar-label,
            #sidebar.sidebar-collapsed .sidebar-group-label,
            #sidebar.sidebar-collapsed .sidebar-footer-text {
                opacity: 0;
                width: 0;
                max-width: 0;
                overflow: hidden;
                visibility: hidden;
                pointer-events: none;
            }
            #sidebar.sidebar-collapsed .sidebar-group-wrap,
            #sidebar.sidebar-collapsed .sidebar-footer-wrap {
                display: none;
            }
            #sidebar.sidebar-collapsed .relative.z-10 span,
            #sidebar.sidebar-collapsed .relative.z-10 p {
                display: none !important;
            }
            #sidebar.sidebar-collapsed .sidebar-brand-link,
            #sidebar.sidebar-collapsed .sidebar-nav-link {
                justify-content: center;
            }
            #sidebar.sidebar-collapsed .sidebar-brand-link {
                gap: 0;
            }
            #sidebar.sidebar-collapsed .sidebar-nav-link {
                gap: 0;
            }

            /* Colored badges — keep their colors but adjust to dark bg */
            .dark .text-emerald-700 { color: #6ee7b7 !important; }
            .dark .text-blue-700    { color: #93c5fd !important; }
            .dark .text-amber-700   { color: #fcd34d !important; }
            .dark .text-red-700     { color: #fca5a5 !important; }
            .dark .text-amber-800   { color: #fbbf24 !important; }
            .dark .text-blue-800    { color: #60a5fa !important; }
            .dark .text-red-800     { color: #f87171 !important; }

            /* Hover card backgrounds */
            .dark .hover\:bg-slate-50:hover { background-color: #334155 !important; }
            .dark .hover\:bg-slate-100:hover { background-color: #475569 !important; }
            .dark .hover\:bg-amber-100:hover { background-color: #292000 !important; }
            .dark .hover\:bg-blue-100:hover  { background-color: #0c1a2e !important; }
            .dark .hover\:bg-red-100:hover   { background-color: #1c0202 !important; }
        </style>
    </head>
    <body class="antialiased text-slate-900 bg-slate-50 font-sans h-full"
          x-data
          x-init="const checkMobile = () => {
              if (window.innerWidth < 1280) {
                  $store.sidebar.setMobileOpen(false);
                  $store.sidebar.isExpanded = false;
              } else {
                  $store.sidebar.isMobileOpen = false;
                  $store.sidebar.isExpanded = true;
              }
          };
          window.addEventListener('resize', checkMobile);
          checkMobile();">

        @php
            $isTeknisi = auth()->check() && auth()->user()->isTeknisi();
            $dashboardRoute = $isTeknisi ? route('teknisi.dashboard') : route('dashboard');
            $currentUserId = auth()->id();
        @endphp

        <div class="min-h-screen xl:flex">

            <!-- Mobile Overlay -->
            <div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
                 class="fixed inset-0 bg-slate-900/50 z-40 xl:hidden" x-cloak></div>

            <!-- Sidebar -->
            <aside id="sidebar"
                class="fixed flex flex-col mt-0 top-0 left-0 h-screen overflow-hidden transition-all duration-300 ease-in-out z-50 px-4"
                :class="{
                    'w-[280px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
                    'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                    'sidebar-collapsed': !$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen,
                    'translate-x-0': $store.sidebar.isMobileOpen,
                    '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
                }"
                @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
                @mouseleave="$store.sidebar.setHovered(false)">

                <!-- Sidebar Background -->
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900"></div>
                <!-- Subtle top accent -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-red-500 to-red-600"></div>
                <!-- Bottom fade -->
                <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-slate-900 to-transparent"></div>

                <div class="relative z-10 flex flex-col h-full">
                <!-- Brand Section -->
                <div class="pt-8 pb-7 flex items-center gap-3"
                    :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'justify-center xl:justify-center' : 'xl:justify-start justify-between'">

                    <a href="{{ $dashboardRoute }}" class="sidebar-brand-link flex items-center gap-3 min-w-0 overflow-hidden">
                        <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo" class="w-10 h-10 rounded-full shrink-0 object-cover border-2 border-red-500/50 shadow-lg shadow-red-900/30">
                        <span class="sidebar-label truncate text-red-400 font-black text-md leading-tight tracking-tight uppercase">
                            PD. ANUGRAH UTAMA
                        </span>
                    </a>
                </div>

                <!-- Navigation Items -->
                <nav class="flex-grow space-y-1 overflow-y-auto no-scrollbar py-4">
                    <x-nav-link-sidebar :href="$dashboardRoute" :active="request()->routeIs('dashboard') || request()->routeIs('teknisi.dashboard')" class="sidebar-nav-link">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        <span class="sidebar-label truncate">DASHBOARD</span>
                    </x-nav-link-sidebar>

                    @if($isTeknisi)
                        <x-nav-link-sidebar :href="route('teknisi.tugas-service-refill')" :active="request()->routeIs('teknisi.tugas-service-refill')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343" /></svg>
                            <span class="sidebar-label truncate">TUGAS SERVICE / REFIL</span>
                        </x-nav-link-sidebar>
                    @else
                        {{-- Group: MANAJEMEN --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Manajemen</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.pelanggan.index')" :active="request()->routeIs('admin.pelanggan.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span class="sidebar-label truncate">PELANGGAN</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.produk.index')" :active="request()->routeIs('admin.produk.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            <span class="sidebar-label truncate">PRODUK</span>
                        </x-nav-link-sidebar>
                        
                        <div x-data="{ open: {{ request()->routeIs('admin.jenis-apar.*', 'admin.jenis-refill.*', 'admin.peralatan.*') ? 'true' : 'false' }} }" class="sidebar-group-wrap relative">
                            <button @click="open = !open" class="sidebar-nav-link flex items-center justify-between w-full px-4 py-2.5 text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <span class="sidebar-label truncate font-semibold text-xs tracking-wider">MASTER DATA</span>
                                </div>
                                <svg class="w-4 h-4 sidebar-label transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" class="flex flex-col pl-12 pr-4 py-1 space-y-1 bg-slate-900/50">
                                <a href="{{ route('admin.jenis-apar.index') }}" class="text-xs font-semibold py-2 px-3 rounded-lg transition {{ request()->routeIs('admin.jenis-apar.*') ? 'text-red-400 bg-slate-800' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Jenis APAR</a>
                                <a href="{{ route('admin.jenis-refill.index') }}" class="text-xs font-semibold py-2 px-3 rounded-lg transition {{ request()->routeIs('admin.jenis-refill.*') ? 'text-red-400 bg-slate-800' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Jenis Refil</a>
                                <a href="{{ route('admin.peralatan.index') }}" class="text-xs font-semibold py-2 px-3 rounded-lg transition {{ request()->routeIs('admin.peralatan.*') ? 'text-red-400 bg-slate-800' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Data Peralatan</a>
                            </div>
                        </div>

                        {{-- Group: STOK / PERSEDIAAN --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Stok / Persediaan</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.stok.index')" :active="request()->routeIs('admin.stok.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
                            <span class="sidebar-label truncate">STOK</span>
                        </x-nav-link-sidebar>

                        {{-- Group: TRANSAKSI --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Transaksi</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.pesanan.index')" :active="request()->routeIs('admin.pesanan.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="sidebar-label truncate">PESANAN</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.pengeluaran.index')" :active="request()->routeIs('admin.pengeluaran.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            <span class="sidebar-label truncate">PENGELUARAN</span>
                        </x-nav-link-sidebar>

                        {{-- Group: UNIT & MASA BERLAKU --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Unit &amp; Masa Berlaku</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.unit-apar.index')" :active="request()->routeIs('admin.unit-apar.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="sidebar-label truncate">UNIT APAR</span>
                        </x-nav-link-sidebar>

                        {{-- Group: LAYANAN APAR --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Layanan APAR</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.service.index')" :active="request()->routeIs('admin.service.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343" /></svg>
                            <span class="sidebar-label truncate">SERVICE APAR</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.refill.index')" :active="request()->routeIs('admin.refill.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span class="sidebar-label truncate">REFIL APAR</span>
                        </x-nav-link-sidebar>

                        {{-- Group: FEEDBACK --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Feedback</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.complain.index')" :active="request()->routeIs('admin.complain.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                            <span class="sidebar-label truncate">KOMPLAIN</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.testimoni.index')" :active="request()->routeIs('admin.testimoni.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                            <span class="sidebar-label truncate">TESTIMONI</span>
                        </x-nav-link-sidebar>

                        {{-- Group: LAPORAN --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Laporan</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.laporan.index')" :active="request()->routeIs('admin.laporan.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            <span class="sidebar-label truncate">LAPORAN</span>
                        </x-nav-link-sidebar>
                    @endif
                </nav>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer-wrap pb-6 border-t border-slate-700/50 pt-4 px-2">
                    <div class="text-center">
                        <p class="sidebar-footer-text text-[10px] font-bold text-slate-500 uppercase tracking-widest">&copy; {{ date('Y') }} PD. Anugrah Utama</p>
                    </div>
                </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 transition-all duration-300 ease-in-out"
                :class="{
                    'xl:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                    'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                    'ml-0': $store.sidebar.isMobileOpen
                }">

                <!-- Header / Topbar -->
                <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200/70 h-20 flex items-center px-6 justify-between shadow-sm shadow-slate-200/30">
                    <!-- Toggle buttons & Search -->
                    <div class="flex items-center gap-4 flex-1">
                        <!-- Desktop Toggle -->
                        <button class="hidden xl:flex items-center justify-center w-10 h-10 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-xl transition"
                                @click="$store.sidebar.toggleExpanded()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                        <!-- Mobile Toggle -->
                        <button class="xl:hidden flex items-center justify-center w-10 h-10 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-xl transition"
                                @click="$store.sidebar.toggleMobileOpen()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>

                        <!-- Mobile Logo -->
                        <div class="xl:hidden flex items-center gap-2">
                            <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo" class="w-8 h-8 rounded-full shrink-0 object-cover">
                        </div>

                        <!-- Search Bar like TailAdmin -->
                        <div class="relative w-full max-w-xs hidden md:block">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </span>
                            <input type="text" placeholder="Cari..." class="w-full bg-slate-100 text-xs border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 focus:border-red-500 focus:bg-white focus:ring-1 focus:ring-red-500 transition outline-none text-slate-700 font-bold tracking-wide">
                        </div>
                    </div>

                    <!-- Right Side Header Content -->
                    <div class="flex items-center gap-3">

                        <!-- Dark Mode Toggle -->
                        <button
                            @click="$store.darkMode.toggle()"
                            class="flex items-center justify-center w-10 h-10 rounded-xl border transition shadow-sm"
                            :class="$store.darkMode.on
                                ? 'bg-slate-800 text-yellow-400 border-slate-700 hover:bg-slate-700'
                                : 'text-slate-500 border-slate-200 hover:bg-slate-100 hover:text-slate-700'"
                            title="Toggle Dark Mode"
                        >
                            <svg x-show="!$store.darkMode.on" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            <svg x-show="$store.darkMode.on" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </button>

                        <!-- Notification Bell -->
                        <div class="relative" x-data="{ open: false }">
                            @php
                                $notifStats = [
                                    'order_hari_ini' => \App\Models\Pesanan::whereNotIn('status', ['selesai final','ditolak'])->whereDate('created_at', now()->toDateString())->count(),
                                    'expiring_soon' => \App\Models\UnitApar::whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
                                    'already_expired' => \App\Models\UnitApar::whereDate('tgl_expired', '<', now()->toDateString())->count(),
                                ];
                                $totalBadge = $notifStats['order_hari_ini'] + $notifStats['expiring_soon'];
                                $initOrders = \App\Models\Pesanan::with('pelanggan')
                                    ->whereNotIn('status', ['selesai final', 'ditolak'])
                                    ->whereDate('created_at', now()->toDateString())
                                    ->orderBy('created_at', 'desc')->limit(5)->get();
                                $initExpiring = \App\Models\UnitApar::with('pelanggan')
                                    ->whereBetween('tgl_expired', [now()->toDateString(), now()->addDays(30)->toDateString()])
                                    ->orderBy('tgl_expired', 'asc')->limit(5)->get();
                                $initExpired = \App\Models\UnitApar::with('pelanggan')
                                    ->whereDate('tgl_expired', '<', now()->toDateString())
                                    ->orderBy('tgl_expired', 'asc')->limit(5)->get();
                            @endphp

                            <button @click="open = !open" class="relative flex items-center justify-center w-10 h-10 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-xl transition shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                @if($totalBadge > 0)
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[9px] font-black rounded-full flex items-center justify-center">{{ $totalBadge > 9 ? '9+' : $totalBadge }}</span>
                                @endif
                                @if($notifStats['already_expired'] > 0)
                                    <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-600 rounded-full"></span>
                                @endif
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-3 w-80 bg-white border border-slate-200 shadow-xl rounded-2xl overflow-hidden z-50">
                                <div class="px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-700 text-white flex items-center justify-between">
                                    <p class="text-[10px] font-black uppercase tracking-widest">Notifikasi</p>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 text-[9px] font-black rounded-full">{{ $notifStats['order_hari_ini'] }} pesanan</span>
                                        @if($notifStats['expiring_soon'] > 0)
                                            <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 text-[9px] font-black rounded-full">{{ $notifStats['expiring_soon'] }} hampir exp</span>
                                        @endif
                                        @if($notifStats['already_expired'] > 0)
                                            <span class="px-2 py-0.5 bg-red-500/20 text-red-300 text-[9px] font-black rounded-full">{{ $notifStats['already_expired'] }} exp</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-3 space-y-2 max-h-80 overflow-y-auto">
                                    @foreach($initOrders as $order)
                                    <a href="{{ route('admin.pesanan.show', $order->id) }}" class="block p-3 bg-emerald-50 border border-emerald-100 rounded-xl hover:bg-emerald-100 transition">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Pesanan Baru</p>
                                        </div>
                                        <p class="text-xs font-black text-slate-900">{{ $order->pelanggan?->nama ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">#{{ $order->id }}</p>
                                        <p class="text-xs font-black text-emerald-700 mt-1">Rp {{ number_format((float) ($order->total_harga ?: $order->total), 0, ',', '.') }}</p>
                                    </a>
                                    @endforeach

                                    @foreach($initExpiring as $apar)
                                    <a href="{{ route('admin.unit-apar.show', $apar->id) }}" class="block p-3 bg-amber-50 border border-amber-100 rounded-xl hover:bg-amber-100 transition">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                                            <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">APAR Akan Expired</p>
                                        </div>
                                        <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                                        <p class="text-xs font-black text-amber-700 mt-1">Exp: {{ $apar->tgl_expired->format('d M Y') }} ({{ now()->diffInDays($apar->tgl_expired) }} hari)</p>
                                    </a>
                                    @endforeach

                                    @foreach($initExpired as $apar)
                                    <a href="{{ route('admin.unit-apar.show', $apar->id) }}" class="block p-3 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 transition">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                            <p class="text-[10px] font-black text-red-700 uppercase tracking-widest">APAR Expired</p>
                                        </div>
                                        <p class="text-xs font-black text-slate-900">{{ $apar->no_seri }}</p>
                                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $apar->pelanggan?->nama ?? '-' }}</p>
                                        <p class="text-xs font-black text-red-700 mt-1">Expired: {{ $apar->tgl_expired->format('d M Y') }} ({{ now()->diffInDays($apar->tgl_expired) }} hari lalu)</p>
                                    </a>
                                    @endforeach

                                    @if($initOrders->isEmpty() && $initExpiring->isEmpty() && $initExpired->isEmpty())
                                        <div class="p-4 text-center">
                                            <p class="text-xs font-semibold text-slate-400">Tidak ada notifikasi saat ini.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 hover:bg-slate-100 p-1.5 border border-slate-200 rounded-2xl transition shadow-sm">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-br from-red-600 to-red-700 flex items-center justify-center shrink-0 shadow-inner">
                                    <span class="text-white font-black text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                                <div class="min-w-0 text-left hidden sm:block">
                                    <p class="text-xs font-black uppercase tracking-wide truncate max-w-[100px] text-slate-800">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $isTeknisi ? 'Teknisi' : 'Admin' }}</p>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-3 w-56 bg-white border border-slate-200 shadow-xl rounded-2xl overflow-hidden z-50">
                                <div class="px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                    <p class="text-xs font-black truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ $isTeknisi ? 'Teknisi' : 'Admin' }}</p>
                                </div>
                                <div class="p-2 space-y-1">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition tracking-wide">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Edit Profil
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition tracking-wide">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </header>

                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 3200)"
                        x-show="show"
                        x-transition:enter="transform ease-out duration-300"
                        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-8"
                        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                        x-transition:leave="transform ease-in duration-200"
                        x-transition:leave-start="opacity-100 sm:translate-x-0"
                        x-transition:leave-end="opacity-0 sm:translate-x-8"
                        class="fixed top-24 right-6 z-[70] w-[calc(100%-3rem)] max-w-md"
                    >
                        <div class="rounded-2xl border border-emerald-100 bg-white/95 backdrop-blur px-5 py-4 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">Berhasil</p>
                                    <p class="text-sm font-bold text-gray-900 mt-1">{{ session('success') }}</p>
                                </div>
                                <button type="button" @click="show = false" class="text-gray-300 hover:text-gray-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        x-data="{ show: true }"
                        x-init="setTimeout(() => show = false, 4200)"
                        x-show="show"
                        x-transition:enter="transform ease-out duration-300"
                        x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-8"
                        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                        x-transition:leave="transform ease-in duration-200"
                        x-transition:leave-start="opacity-100 sm:translate-x-0"
                        x-transition:leave-end="opacity-0 sm:translate-x-8"
                        class="fixed top-24 right-6 z-[70] w-[calc(100%-3rem)] max-w-md"
                    >
                        <div class="rounded-2xl border border-red-100 bg-white/95 backdrop-blur px-5 py-4 shadow-xl">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-xl bg-red-100 text-red-700 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.007v.008H12v-.008z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.29 3.86 1.82 18a2.25 2.25 0 0 0 1.93 3.38h16.5A2.25 2.25 0 0 0 22.18 18L13.71 3.86a2.25 2.25 0 0 0-3.42 0Z" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.2em]">Perlu Dicek</p>
                                    <p class="text-sm font-bold text-gray-900 mt-1">{{ session('error') }}</p>
                                </div>
                                <button type="button" @click="show = false" class="text-gray-300 hover:text-gray-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @isset($header)
                    <header class="px-6 lg:px-10 pt-8 pb-4 flex justify-between items-start">
                        <div>
                            {{ $header }}
                            @isset($description)
                                <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
                            @endisset
                            @if(View::hasSection('breadcrumb'))
                                @yield('breadcrumb')
                            @endif
                        </div>
                    </header>
                @endisset

                <main class="px-6 lg:px-10 pb-16 pt-4">
                    <div class="max-w-[1600px]">
                        {{ $slot }}
                    </div>
                </main>

                <footer class="border-t border-slate-200 bg-white px-6 lg:px-10 py-6 mt-auto">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <p class="text-xl font-black text-slate-900 tracking-tight">PD. Anugrah Utama</p>
                            <p class="text-sm text-slate-400 mt-1">&copy; {{ date('Y') }} PD. Anugrah Utama. Hak cipta dilindungi undang-undang.</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        @unless($isTeknisi)
            <a
                href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="fixed bottom-8 right-8 w-14 h-14 bg-[#25D366] rounded-2xl shadow-2xl shadow-[#25D366]/25 flex items-center justify-center text-white border border-white/10 transition-transform hover:scale-110 wa-bounce z-50"
                aria-label="WhatsApp"
            >
                <svg class="w-7 h-7" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                    <path d="M19.11 17.35c-.27-.14-1.58-.78-1.83-.87-.24-.09-.43-.14-.61.14-.18.27-.7.87-.86 1.05-.16.18-.32.2-.59.07-.27-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.64-1.53-1.91-.16-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.02-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.48.07-.73.34-.24.27-.95.92-.95 2.25 0 1.33.97 2.61 1.11 2.79.14.18 1.91 2.93 4.63 4.11.65.28 1.15.45 1.54.58.65.21 1.24.18 1.71.11.52-.08 1.58-.65 1.8-1.27.22-.62.22-1.15.16-1.27-.07-.12-.24-.2-.5-.34z"/>
                    <path d="M16 3C9.38 3 4 8.38 4 15c0 2.11.55 4.17 1.6 5.99L4 29l8.2-1.56A11.9 11.9 0 0 0 16 27c6.62 0 12-5.38 12-12S22.62 3 16 3zm0 21.83c-1.84 0-3.64-.49-5.22-1.42l-.37-.22-4.86.92.93-4.74-.24-.38A9.85 9.85 0 0 1 6.17 15c0-5.42 4.41-9.83 9.83-9.83S25.83 9.58 25.83 15 21.42 24.83 16 24.83z"/>
                </svg>
            </a>
        @endunless

        {{-- Reverb Real-Time Toast Container --}}
        <div id="reverb-toast-wrap" class="fixed bottom-6 right-6 z-[300] flex flex-col gap-2 items-end pointer-events-none [&>*]:pointer-events-auto max-w-xs w-full"></div>

        @if(session('wa_url'))
            <script>
                window.addEventListener('load', function () {
                    var waUrl = JSON.parse({!! json_encode(session('wa_url')) !!});
                    const waKey = 'wa-opened:' + waUrl;
                    if (sessionStorage.getItem(waKey) === '1') return;

                    window.open(waUrl, '_blank');
                    sessionStorage.setItem(waKey, '1');
                });
            </script>
        @endif

        @auth
            <script>
            (() => {
                const userId = @js((int) auth()->id());
                const userRole = @js($isTeknisi ? 'teknisi' : (auth()->user()->isAdmin() ? 'admin' : 'pelanggan'));
                const currentPath = window.location.pathname;
                const toastWrap = document.getElementById('reverb-toast-wrap');

                if (!window.Echo || !userId || !toastWrap) {
                    return;
                }

                let refreshTimer = null;
                const shouldRefreshAdminPage = currentPath === '/dashboard'
                    || currentPath.startsWith('/admin/pesanan')
                    || currentPath.startsWith('/admin/service');
                const shouldRefreshTeknisiPage = currentPath.startsWith('/teknisi');

                const colorMap = {
                    emerald: 'border-emerald-100 bg-white/95 shadow-emerald-200/40 text-emerald-700',
                    blue: 'border-blue-100 bg-white/95 shadow-blue-200/40 text-blue-700',
                    amber: 'border-amber-100 bg-white/95 shadow-amber-200/40 text-amber-700',
                    red: 'border-red-100 bg-white/95 shadow-red-200/40 text-red-700',
                    slate: 'border-slate-200 bg-white/95 shadow-slate-200/40 text-slate-700',
                };

                function renderToast(title, message, tone = 'slate') {
                    const palette = colorMap[tone] || colorMap.slate;
                    const toast = document.createElement('div');
                    toast.className = `w-80 rounded-2xl border backdrop-blur px-4 py-3 shadow-xl transition-all ${palette}`;
                    toast.innerHTML = `
                        <p class="text-[10px] font-black uppercase tracking-widest">${title}</p>
                        <p class="text-sm font-black text-slate-900 mt-1">${message}</p>
                    `;
                    toastWrap.appendChild(toast);
                    window.setTimeout(() => toast.remove(), 4200);
                }

                function scheduleRefresh(reason) {
                    if (refreshTimer) {
                        return;
                    }

                    renderToast('Realtime Aktif', `${reason} Halaman akan disinkronkan otomatis.`, 'blue');
                    refreshTimer = window.setTimeout(() => {
                        window.location.reload();
                    }, 900);
                }

                function dispatchRealtime(name, detail) {
                    window.dispatchEvent(new CustomEvent(name, { detail }));
                }

                if (userRole === 'admin') {
                    window.Echo.channel('admin-notifications')
                        .listen('.pesanan.baru', (event) => {
                            const payload = event.payload || event;
                            renderToast('Pesanan Baru', `${payload.pelanggan?.nama || payload.pelanggan || 'Pelanggan'} membuat pesanan baru.`, 'emerald');
                            dispatchRealtime('realtime:pesanan-baru', payload);
                            if (shouldRefreshAdminPage) {
                                scheduleRefresh('Pesanan baru masuk.');
                            }
                        })
                        .listen('.pesanan.status-diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Status Pesanan', `Pesanan #${payload.id} berubah ke status ${payload.status}.`, 'amber');
                            dispatchRealtime('realtime:status-pesanan', payload);
                            if (shouldRefreshAdminPage) {
                                scheduleRefresh('Status pesanan berubah.');
                            }
                        })
                        .listen('.tugas.diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Update Teknisi', `Tugas pesanan #${payload.id} diperbarui (${payload.status}).`, 'red');
                            dispatchRealtime('realtime:tugas-teknisi', payload);
                            if (shouldRefreshAdminPage) {
                                scheduleRefresh('Tugas teknisi diperbarui.');
                            }
                        });
                }

                if (userRole === 'teknisi') {
                    window.Echo.channel(`teknisi-${userId}`)
                        .listen('.tugas.diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Tugas Masuk', `Pesanan #${payload.id} untuk ${payload.pelanggan} diperbarui (${payload.status}).`, 'emerald');
                            dispatchRealtime('realtime:tugas-teknisi', payload);
                            if (shouldRefreshTeknisiPage) {
                                scheduleRefresh('Ada perubahan tugas teknisi.');
                            }
                        })
                        .listen('.pesanan.status-diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Status Tugas', `Pesanan #${payload.id} berubah ke status ${payload.status}.`, 'amber');
                            dispatchRealtime('realtime:status-pesanan', payload);
                            if (shouldRefreshTeknisiPage) {
                                scheduleRefresh('Status tugas berubah.');
                            }
                        });
                }
            })();
            </script>
        @endauth

        @stack('scripts')
    </body>
</html>
