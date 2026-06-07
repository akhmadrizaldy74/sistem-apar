<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Leaflet Global --}}
        <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
        <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

        <title>{{ config('app.name', 'PD. ANUGRAH UTAMA') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')

        <!-- Alpine Sidebar State Manager -->
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    isExpanded: window.innerWidth >= 1024,
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
                        if (window.innerWidth >= 1024 && !this.isExpanded) {
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
    <body class="antialiased text-slate-900 bg-slate-50 font-sans h-full admin-surface tailadmin-admin overflow-x-hidden"
          x-data
          x-effect="document.body.classList.toggle('overflow-hidden', $store.sidebar.isMobileOpen && window.innerWidth < 1024); document.documentElement.classList.toggle('overflow-hidden', $store.sidebar.isMobileOpen && window.innerWidth < 1024)"
          x-init="const checkMobile = () => {
              if (window.innerWidth < 1024) {
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

        <div class="min-h-screen overflow-x-clip lg:flex">

            <!-- Mobile Overlay -->
            <div x-show="$store.sidebar.isMobileOpen"
                 @click="$store.sidebar.setMobileOpen(false)"
                 x-transition.opacity
                 class="fixed inset-0 z-[70] bg-slate-950/70 backdrop-blur-[2px] lg:hidden"
                 x-cloak></div>

            <!-- Sidebar -->
            <aside id="sidebar"
                class="fixed inset-y-0 left-0 z-[80] mt-0 flex h-screen w-full max-w-full flex-col overflow-hidden px-0 transition-all duration-300 ease-in-out sm:w-[340px] sm:max-w-[88vw] lg:px-4"
                :class="{
                    'lg:w-[280px] lg:max-w-[280px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
                    'lg:w-[90px] lg:max-w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                    'sidebar-collapsed': !$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen,
                    'translate-x-0': $store.sidebar.isMobileOpen,
                    '-translate-x-full lg:translate-x-0': !$store.sidebar.isMobileOpen
                }"
                @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
                @mouseleave="$store.sidebar.setHovered(false)">

                <!-- Sidebar Background -->
                <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900"></div>
                <!-- Subtle top accent -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-600 via-red-500 to-red-600"></div>
                <!-- Bottom fade -->
                <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-slate-900 to-transparent"></div>

                <div class="relative z-10 flex h-full flex-col">
                <!-- Brand Section -->
                <div class="flex items-center gap-3 px-4 pb-5 pt-6 sm:px-5 lg:px-0 lg:pb-7 lg:pt-8"
                    :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'justify-center lg:justify-center' : 'lg:justify-start justify-between'">

                    <a href="{{ $dashboardRoute }}" class="sidebar-brand-link flex items-center min-w-0 overflow-hidden">
                        <span class="sidebar-label truncate text-red-400 font-black text-md leading-tight tracking-tight uppercase">
                            PD. ANUGRAH UTAMA
                        </span>
                    </a>

                    <button type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-slate-900/40 text-slate-200 transition hover:bg-slate-800 lg:hidden"
                            @click="$store.sidebar.setMobileOpen(false)">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Items -->
                <nav class="flex-grow space-y-1 overflow-y-auto no-scrollbar px-2 py-4 sm:px-3 lg:px-0">
                    <x-nav-link-sidebar :href="$dashboardRoute" :active="request()->routeIs('dashboard') || request()->routeIs('teknisi.dashboard')" class="sidebar-nav-link">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        <span class="sidebar-label truncate">DASHBOARD</span>
                    </x-nav-link-sidebar>

                    @if($isTeknisi)
                        <x-nav-link-sidebar :href="route('teknisi.tugas-produk')" :active="request()->routeIs('teknisi.tugas-produk')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3m-4 0H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4m5-4h6" /></svg>
                            <span class="sidebar-label truncate">TUGAS PRODUK</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('teknisi.tugas-service-refill')" :active="request()->routeIs('teknisi.tugas-service-refill')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343" /></svg>
                            <span class="sidebar-label truncate">TUGAS SERVICE / REFIL</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('teknisi.riwayat-tugas')" :active="request()->routeIs('teknisi.riwayat-tugas')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9m-6-4 4 4m0 0-4 4m4-4H9" /></svg>
                            <span class="sidebar-label truncate">RIWAYAT TUGAS</span>
                        </x-nav-link-sidebar>
                    @else
                        {{-- Group: LAYANAN APAR --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Layanan APAR</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.pesanan.index')" :active="request()->routeIs('admin.pesanan.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            <span class="sidebar-label truncate">PESANAN</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.service.index')" :active="request()->routeIs('admin.service.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343" /></svg>
                            <span class="sidebar-label truncate">SERVICE APAR</span>
                        </x-nav-link-sidebar>
                        <x-nav-link-sidebar :href="route('admin.refill.index')" :active="request()->routeIs('admin.refill.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span class="sidebar-label truncate">REFILL APAR</span>
                        </x-nav-link-sidebar>

                        {{-- Group: MANAJEMEN --}}
                        <div class="sidebar-group-wrap px-4 pt-4 pb-1">
                            <p class="sidebar-group-label text-[9px] font-black text-slate-600 uppercase tracking-widest">Manajemen</p>
                        </div>
                        <x-nav-link-sidebar :href="route('admin.akun.index')" :active="request()->routeIs('admin.akun.*')" class="sidebar-nav-link">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            <span class="sidebar-label truncate">MANAJEMEN AKUN</span>
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

                        {{-- PENGELUARAN --}}
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
                <div class="sidebar-footer-wrap border-t border-slate-700/50 px-4 pb-6 pt-4 sm:px-5 lg:px-2">
                    <div class="text-center">
                        <p class="sidebar-footer-text text-[10px] font-bold text-slate-500 uppercase tracking-widest">&copy; {{ date('Y') }} PD. ANUGRAH UTAMA</p>
                    </div>
                </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 min-w-0 max-w-full transition-all duration-300 ease-in-out"
                :class="{
                    'lg:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                    'lg:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered
                }">

                <!-- Header / Topbar -->
                <header class="sticky top-0 z-40 flex min-h-20 items-center justify-between border-b border-slate-200/70 bg-white/80 px-4 shadow-sm shadow-slate-200/30 backdrop-blur-xl sm:px-6 lg:px-8">
                    <!-- Toggle buttons & Search -->
                    <div class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4">
                        <!-- Desktop Toggle -->
                        <button class="hidden lg:flex items-center justify-center w-10 h-10 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-xl transition"
                                @click="$store.sidebar.toggleExpanded()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                        <!-- Mobile Toggle -->
                        <button class="lg:hidden flex items-center justify-center w-10 h-10 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-xl transition"
                                @click="$store.sidebar.toggleMobileOpen()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>

                        <!-- Mobile Brand -->
                        <div class="lg:hidden flex items-center gap-2">
                            <span class="text-sm font-black uppercase tracking-tight text-slate-900">PD. ANUGRAH UTAMA</span>
                        </div>

                        <!-- Search Bar like TailAdmin -->
                        <div class="relative hidden w-full max-w-xs lg:block xl:max-w-sm">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </span>
                            <input type="text" placeholder="Cari..." class="w-full bg-slate-100 text-xs border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 focus:border-red-500 focus:bg-white focus:ring-1 focus:ring-red-500 transition outline-none text-slate-700 font-bold tracking-wide">
                        </div>
                    </div>

                    <!-- Right Side Header Content -->
                    <div class="flex shrink-0 items-center gap-2 sm:gap-3">

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



                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 rounded-2xl border border-slate-200 p-1.5 shadow-sm transition hover:bg-slate-100 sm:gap-3">
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

                @isset($header)
                    <header class="flex items-start justify-between px-4 pb-4 pt-6 sm:px-6 lg:px-10 lg:pt-8">
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

                <main class="overflow-x-hidden px-4 pb-10 pt-4 sm:px-6 sm:pb-12 lg:px-10 lg:pb-16">
                    <div class="w-full max-w-[1600px]">
                        {{ $slot }}
                    </div>
                </main>

                <footer class="mt-auto border-t border-slate-200 bg-white px-4 py-6 sm:px-6 lg:px-10">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <p class="text-xl font-black uppercase text-slate-900 tracking-tight">PD. ANUGRAH UTAMA</p>
                            <p class="text-sm text-slate-400 mt-1">&copy; {{ date('Y') }} PD. ANUGRAH UTAMA. Hak cipta dilindungi undang-undang.</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        @unless($isTeknisi || (auth()->check() && auth()->user()->isAdmin()))
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

        @include('layouts.partials.sweet-alerts')

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
                            renderToast('Status Pesanan', `Transaksi pesanan berubah ke status ${payload.status}.`, 'amber');
                            dispatchRealtime('realtime:status-pesanan', payload);
                            if (shouldRefreshAdminPage) {
                                scheduleRefresh('Status pesanan berubah.');
                            }
                        })
                        .listen('.tugas.diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Update Teknisi', `Tugas pesanan diperbarui (${payload.status}).`, 'red');
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
                            renderToast('Tugas Masuk', `Transaksi ${payload.pelanggan} diperbarui (${payload.status}).`, 'emerald');
                            dispatchRealtime('realtime:tugas-teknisi', payload);
                            if (shouldRefreshTeknisiPage) {
                                scheduleRefresh('Ada perubahan tugas teknisi.');
                            }
                        })
                        .listen('.pesanan.status-diperbarui', (event) => {
                            const payload = event.payload || event;
                            renderToast('Status Tugas', `Transaksi berubah ke status ${payload.status}.`, 'amber');
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
