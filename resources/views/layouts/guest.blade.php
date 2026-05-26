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
                background-color: #0f172a;
                background-image:
                    radial-gradient(ellipse at 20% 50%, rgba(185, 28, 28, 0.15) 0%, transparent 50%),
                    radial-gradient(ellipse at 80% 50%, rgba(220, 38, 38, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.03) 0%, transparent 40%);
            }
            .fire-pattern {
                position: absolute;
                inset: 0;
                opacity: 0.04;
                background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M30 10c0 5.5-4 10-10 10S10 15.5 10 10 14 0 20 0s10 4.5 10 10zm0 0c0 5.5 4 10 10 10s10-4.5 10-10-4-10-10-10-10 4.5-10 10zM30 40c0 5.5-4 10-10 10s-10-4.5-10-10 4-10 10-10 10 4.5 10 10zm0 0c0 5.5 4 10 10 10s10-4.5 10-10-4-10-10-10-10 4.5-10 10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased overflow-x-hidden">
        <div class="min-h-screen flex items-start justify-center auth-bg p-4 py-6 relative sm:items-center sm:p-6 sm:py-10">
            <div class="fire-pattern"></div>
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-red-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-red-900/10 rounded-full blur-3xl"></div>
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
        </div>
    </body>
</html>
