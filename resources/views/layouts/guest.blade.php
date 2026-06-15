@props([
    'variant' => 'default',
])

@php
    $routeName = request()->route()?->getName();
    $pageTitle = match ($routeName) {
        'login' => 'Masuk',
        'register' => 'Daftar Akun',
        'password.request' => 'Lupa Password',
        'password.reset' => 'Atur Ulang Password',
        'password.confirm' => 'Konfirmasi Password',
        'verification.notice' => 'Verifikasi Email',
        default => 'Akses Akun',
    };
    $hasHero = isset($hero) && trim((string) $hero) !== '';
    $isSplit = $variant === 'split' && $hasHero;
    $isLoginShowcase = $variant === 'login-showcase';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle }} - {{ config('app.name', 'PD. ANUGRAH UTAMA') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @safeVite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-page {
                background: linear-gradient(170deg, #fef7f2 0%, #faf5f0 40%, #f5f7fa 100%);
            }

            .auth-page::before {
                content: "";
                position: absolute;
                top: -10rem;
                left: -8rem;
                width: 24rem;
                height: 24rem;
                border-radius: 9999px;
                background: rgba(248, 113, 113, 0.12);
                filter: blur(90px);
                pointer-events: none;
            }

            .auth-page::after {
                content: "";
                position: absolute;
                right: -8rem;
                bottom: -10rem;
                width: 26rem;
                height: 26rem;
                border-radius: 9999px;
                background: rgba(251, 191, 36, 0.10);
                filter: blur(90px);
                pointer-events: none;
            }

            .auth-shell {
                box-shadow: 0 24px 80px -20px rgba(15, 23, 42, 0.12);
            }

            .auth-brand-panel {
                background: linear-gradient(155deg, #b91c1c 0%, #991b1b 35%, #7f1d1d 70%, #1e1e2e 100%);
            }

            .auth-dot-grid {
                background-image: radial-gradient(rgba(255, 255, 255, 0.10) 1px, transparent 1px);
                background-size: 24px 24px;
            }

            .auth-brand-ring {
                position: absolute;
                border-radius: 9999px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                pointer-events: none;
            }

            .auth-brand-ring.one {
                top: 3rem;
                right: -4rem;
                width: 14rem;
                height: 14rem;
            }

            .auth-brand-ring.two {
                bottom: -3rem;
                left: -3rem;
                width: 10rem;
                height: 10rem;
            }

            @media (max-width: 1279px) {
                .auth-brand-ring.one {
                    width: 10rem;
                    height: 10rem;
                    right: -2rem;
                }
                .auth-brand-ring.two {
                    width: 7rem;
                    height: 7rem;
                }
            }

            .login-page {
                background:
                    radial-gradient(circle at 18% 12%, rgba(248, 113, 113, 0.20), transparent 26%),
                    radial-gradient(circle at 82% 88%, rgba(127, 29, 29, 0.16), transparent 30%),
                    linear-gradient(135deg, #fff7ed 0%, #fffafa 45%, #f8fafc 100%);
            }

            .login-page::before,
            .login-page::after {
                content: "";
                position: absolute;
                border-radius: 9999px;
                pointer-events: none;
            }

            .login-page::before {
                inset: -18rem auto auto -12rem;
                width: 36rem;
                height: 36rem;
                background: rgba(185, 28, 28, 0.12);
                filter: blur(80px);
            }

            .login-page::after {
                right: -12rem;
                bottom: -16rem;
                width: 34rem;
                height: 34rem;
                background: rgba(251, 146, 60, 0.14);
                filter: blur(90px);
            }

            .login-shell {
                box-shadow: 0 34px 90px rgba(69, 10, 10, 0.18);
            }

            .login-visual {
                background:
                    radial-gradient(circle at 20% 15%, rgba(255, 255, 255, 0.17), transparent 24%),
                    radial-gradient(circle at 80% 80%, rgba(254, 202, 202, 0.18), transparent 28%),
                    linear-gradient(150deg, #7f1d1d 0%, #991b1b 42%, #dc2626 100%);
            }

            .login-visual::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px);
                background-size: 22px 22px;
                opacity: 0.42;
                pointer-events: none;
            }




            .login-visual-bubble {
                position: absolute;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.16);
                border: 1px solid rgba(255, 255, 255, 0.12);
            }

            .login-visual-points {
                position: relative;
                z-index: 20;
            }

            .login-visual-bubble.one {
                top: 2.4rem;
                right: 4rem;
                width: 5rem;
                height: 5rem;
            }

            .login-visual-bubble.two {
                left: 2.4rem;
                bottom: 4.4rem;
                width: 7.5rem;
                height: 7.5rem;
            }

            .login-visual-bubble.three {
                right: 2.1rem;
                bottom: 2.1rem;
                width: 2.7rem;
                height: 2.7rem;
            }

            @media (max-width: 767px) {
                .login-visual-curve {
                    inset: 4.75rem 1.35rem 2.25rem 1.35rem;
                    border-radius: 1.8rem;
                }
            }
        </style>
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="{{ $isLoginShowcase ? 'login-page' : 'auth-page' }} relative min-h-screen overflow-x-hidden">
            <div class="relative min-h-screen w-full">
                @if ($isLoginShowcase)
                    <main class="relative z-10 flex min-h-screen items-start justify-center px-4 py-4 sm:px-6 md:items-center md:py-6 lg:px-8">
                        {{ $slot }}
                    </main>
                @elseif ($isSplit)
                    <div class="min-h-screen w-full lg:grid lg:grid-cols-2">
                        {{-- Left column: form --}}
                        <section class="relative flex min-h-screen items-center justify-center px-6 py-10 sm:px-10 lg:px-16 xl:px-20">
                            <div class="relative z-10 w-full max-w-md">
                                {{ $slot }}
                            </div>
                        </section>

                        {{-- Right column: branding --}}
                        <aside class="auth-brand-panel relative hidden min-h-screen overflow-hidden border-l border-white/10 lg:flex">
                            <div class="auth-dot-grid absolute inset-0 opacity-30" aria-hidden="true"></div>
                            <div class="auth-brand-ring one" aria-hidden="true"></div>
                            <div class="auth-brand-ring two" aria-hidden="true"></div>

                            <div class="relative flex h-full w-full items-center justify-center px-10 py-12 xl:px-16">
                                <div class="w-full max-w-sm">
                                    {{ $hero }}
                                </div>
                            </div>
                        </aside>
                    </div>
                @else
                    <div class="mx-auto flex min-h-screen w-full items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
                        <div class="w-full max-w-md">
                        <div class="relative mb-8 text-center sm:mb-10" data-reveal>
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-full border border-white/60 bg-white/70 px-4 py-2 shadow-sm backdrop-blur">
                                <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. ANUGRAH UTAMA" class="h-8 w-8 object-contain">
                                <span class="text-sm font-black uppercase tracking-[0.18em] text-slate-700">PD. ANUGRAH UTAMA</span>
                            </a>
                        </div>

                        <div class="auth-shell rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-2xl backdrop-blur-xl sm:rounded-[2.5rem] sm:p-10" data-reveal>
                            {{ $slot }}
                        </div>
                    </div>
                    </div>
                @endif
            </div>

            @include('layouts.partials.sweet-alerts')
        </div>
    </body>
</html>
