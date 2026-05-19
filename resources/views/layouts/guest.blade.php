<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk — {{ config('app.name', 'Sistem APAR') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('favicon-apar.svg') }}" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .auth-bg {
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(185, 28, 28, 0.4)), url('https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?q=80&w=2000&auto=format&fit=crop');
                background-size: cover;
                background-position: center;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased overflow-x-hidden">
        <div class="min-h-screen flex items-start justify-center auth-bg p-4 py-6 relative sm:items-center sm:p-6 sm:py-10">
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-red-500/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/20 to-red-900/30"></div>
            <div class="w-full max-w-md">
                <!-- Branding -->
                <div class="text-center mb-8 sm:mb-10 relative" data-reveal>
                    <a href="/" class="inline-block">
                        <h1 class="text-white font-black text-xl sm:text-2xl tracking-tight uppercase leading-tight">
                            PD. Anugrah Utama
                        </h1>
                        <p class="mt-3 text-[10px] font-bold text-white/60 uppercase tracking-[0.25em]">Sistem informasi APAR</p>
                    </a>
                </div>

                <!-- Auth Card -->
                <div class="bg-white/90 backdrop-blur-2xl p-6 sm:p-10 rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-black/40 border border-white/15 relative" data-reveal>
                    {{ $slot }}
                </div>
                
                <!-- Footer Info -->
                <p class="mt-6 sm:mt-8 text-center text-[11px] sm:text-xs font-bold text-white/50 uppercase tracking-[0.15em] relative" data-reveal>
                    Akses terbatas untuk tim operasional PD. Anugrah Utama
                </p>
            </div>
        </div>
    </body>
</html>
