<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Sistem APAR') . ' - Panel Teknisi')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon-apar.svg') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-100 text-slate-900 tailadmin-admin overflow-x-hidden">
    <div class="min-h-screen">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600">Panel Teknisi</p>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">Tugas Lapangan</h1>
                </div>
                <div class="flex w-full items-center justify-between gap-3 sm:w-auto sm:justify-end">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-black text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Akses Teknisi</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest hover:bg-slate-700 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            @yield('content')
        </main>
    </div>
    @include('layouts.partials.sweet-alerts')
</body>
</html>
