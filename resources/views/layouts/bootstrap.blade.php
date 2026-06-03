<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'PD. ANUGRAH UTAMA'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --feedback-red: #b91c1c;
            --feedback-red-strong: #991b1b;
            --feedback-red-soft: #fef2f2;
            --feedback-red-border: rgba(220, 38, 38, 0.14);
            --feedback-ink: #0f172a;
            --feedback-text: #334155;
            --feedback-muted: #64748b;
            --feedback-surface: #ffffff;
            --feedback-surface-soft: #f8fafc;
            --feedback-shadow: 0 30px 70px rgba(15, 23, 42, 0.10);
            --feedback-shadow-soft: 0 18px 42px rgba(15, 23, 42, 0.08);
            --feedback-radius: 28px;
        }

        body.feedback-page {
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--feedback-text);
            background:
                radial-gradient(circle at top left, rgba(248, 113, 113, 0.08), transparent 24%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .feedback-navbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
        }

        .feedback-brand {
            color: var(--feedback-ink);
            font-size: 0.98rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            text-decoration: none;
            white-space: nowrap;
        }

        .feedback-nav-link {
            color: #475569;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 0.62rem 0.95rem !important;
            border-radius: 999px;
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
        }

        .feedback-nav-link:hover,
        .feedback-nav-link:focus {
            color: var(--feedback-red);
            background: rgba(254, 226, 226, 0.85);
        }

        .feedback-nav-cta {
            border: 1px solid rgba(220, 38, 38, 0.14);
            background: #fff;
            color: var(--feedback-red);
            font-size: 0.8rem;
            font-weight: 800;
            padding: 0.7rem 1.05rem;
            border-radius: 999px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .feedback-nav-cta:hover {
            background: var(--feedback-red-soft);
            color: var(--feedback-red-strong);
        }

        .feedback-hero {
            position: relative;
            overflow: hidden;
            padding: 52px 0 34px;
            background:
                radial-gradient(circle at 20% 20%, rgba(248, 113, 113, 0.18), transparent 24%),
                linear-gradient(135deg, #991b1b 0%, #b91c1c 58%, #dc2626 100%);
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .feedback-hero::before,
        .feedback-hero::after {
            content: "";
            position: absolute;
            inset: auto;
            border-radius: 999px;
            pointer-events: none;
        }

        .feedback-hero::before {
            width: 240px;
            height: 240px;
            top: -100px;
            right: -90px;
            background: rgba(255, 255, 255, 0.04);
        }

        .feedback-hero::after {
            width: 180px;
            height: 180px;
            left: -70px;
            bottom: -90px;
            background: rgba(248, 113, 113, 0.12);
        }

        .feedback-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.6rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.10);
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-bottom: 0.85rem;
        }

        .feedback-hero-dot {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background: #fecaca;
            box-shadow: 0 0 0 6px rgba(254, 202, 202, 0.16);
        }

        .feedback-hero-title {
            max-width: 760px;
            margin: 0 auto 0.7rem;
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .feedback-hero-subtitle {
            max-width: 640px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.95rem;
            line-height: 1.65;
            font-weight: 500;
        }

        .feedback-main {
            margin-top: -16px;
            padding-bottom: 56px;
        }

        .feedback-card {
            background: var(--feedback-surface);
            border: 1px solid rgba(226, 232, 240, 0.82);
            border-radius: var(--feedback-radius);
            box-shadow: var(--feedback-shadow);
            padding: 1.75rem;
        }

        .feedback-card-head {
            margin-bottom: 1.35rem;
        }

        .feedback-kicker {
            color: var(--feedback-red);
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 0.6rem;
        }

        .feedback-card-title {
            color: var(--feedback-ink);
            font-size: clamp(1.45rem, 2.4vw, 2rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0 0 0.5rem;
        }

        .feedback-card-subtitle {
            color: var(--feedback-muted);
            font-size: 0.94rem;
            line-height: 1.68;
            margin: 0;
        }

        .feedback-alert {
            border-radius: 22px;
            border: 1px solid transparent;
            padding: 1rem 1.1rem;
            margin-bottom: 1.15rem;
        }

        .feedback-alert--info {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e3a8a;
        }

        .feedback-alert--warn {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #9a3412;
        }

        .feedback-alert--danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .feedback-transaction {
            background: #fffaf9;
            border: 1px solid rgba(252, 165, 165, 0.22);
            border-radius: 22px;
            padding: 1.1rem 1.15rem;
            margin-bottom: 1.35rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.75);
        }

        .feedback-transaction-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem 1rem;
            margin-top: 0.9rem;
        }

        .feedback-meta-item {
            min-width: 0;
        }

        .feedback-meta-label {
            display: block;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            margin-bottom: 0.35rem;
        }

        .feedback-meta-value {
            color: var(--feedback-ink);
            font-size: 0.95rem;
            line-height: 1.55;
            font-weight: 800;
        }

        .feedback-meta-value--muted {
            color: var(--feedback-muted);
            font-weight: 700;
        }

        .feedback-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.5rem 0.9rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .feedback-status-badge--success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .feedback-status-badge--warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #b45309;
        }

        .feedback-status-badge--info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }

        .feedback-status-badge--neutral {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .feedback-form-group + .feedback-form-group {
            margin-top: 1rem;
        }

        .feedback-label {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--feedback-ink);
            font-size: 0.9rem;
            font-weight: 800;
            margin-bottom: 0.65rem;
        }

        .feedback-required {
            color: var(--feedback-red);
        }

        .feedback-input,
        .feedback-textarea {
            width: 100%;
            border: 1px solid rgba(203, 213, 225, 0.95);
            border-radius: 18px;
            background: #fff;
            color: var(--feedback-ink);
            font-size: 0.95rem;
            line-height: 1.6;
            padding: 0.92rem 1rem;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .feedback-textarea {
            min-height: 156px;
            resize: vertical;
        }

        .feedback-input::placeholder,
        .feedback-textarea::placeholder {
            color: #94a3b8;
        }

        .feedback-input:focus,
        .feedback-textarea:focus {
            outline: none;
            border-color: rgba(220, 38, 38, 0.36);
            box-shadow: 0 0 0 5px rgba(254, 226, 226, 0.8);
        }

        .feedback-input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .feedback-field-note {
            margin-top: 0.55rem;
            color: var(--feedback-muted);
            font-size: 0.83rem;
            line-height: 1.6;
            font-weight: 600;
        }

        .feedback-invalid {
            display: block;
            margin-top: 0.5rem;
            color: #dc2626;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .feedback-note {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border-radius: 18px;
            padding: 0.95rem 1rem;
            margin-top: 1.15rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: var(--feedback-surface-soft);
        }

        .feedback-note i {
            color: var(--feedback-red);
            margin-top: 0.15rem;
        }

        .feedback-note strong {
            display: block;
            color: var(--feedback-ink);
            font-size: 0.88rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .feedback-note span {
            color: var(--feedback-muted);
            font-size: 0.84rem;
            line-height: 1.65;
            font-weight: 600;
        }

        .feedback-submit {
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow: 0 18px 30px rgba(220, 38, 38, 0.22);
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        }

        .feedback-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 24px 36px rgba(220, 38, 38, 0.28);
        }

        .feedback-submit:disabled {
            opacity: 0.66;
            cursor: not-allowed;
            box-shadow: none;
        }

        .feedback-rating {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .feedback-rating-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .feedback-rating-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 104px;
            padding: 0.8rem 0.65rem;
            border: 1px solid rgba(203, 213, 225, 0.95);
            border-radius: 22px;
            background: #fff;
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .feedback-rating-label:hover {
            transform: translateY(-2px);
            border-color: rgba(251, 191, 36, 0.75);
            box-shadow: var(--feedback-shadow-soft);
        }

        .feedback-rating-option.is-active .feedback-rating-label {
            background: #fff8db;
            border-color: rgba(251, 191, 36, 0.85);
            box-shadow: 0 18px 30px rgba(234, 179, 8, 0.14);
        }

        .feedback-rating-stars {
            display: flex;
            gap: 0.12rem;
            font-size: 1.35rem;
            color: #cbd5e1;
        }

        .feedback-rating-option.is-active .feedback-rating-stars,
        .feedback-rating-label:hover .feedback-rating-stars {
            color: #fbbf24;
        }

        .feedback-rating-number {
            color: var(--feedback-ink);
            font-size: 0.88rem;
            font-weight: 900;
        }

        .feedback-rating-text {
            color: var(--feedback-muted);
            font-size: 0.74rem;
            font-weight: 700;
            text-align: center;
            line-height: 1.45;
            padding: 0 0.5rem;
        }

        .feedback-rating-hint,
        .feedback-counter {
            margin-top: 0.7rem;
            color: var(--feedback-muted);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .feedback-counter {
            text-align: right;
        }

        @media (max-width: 991.98px) {
            .feedback-hero {
                padding: 44px 0 28px;
            }

            .feedback-main {
                margin-top: -12px;
            }
        }

        @media (max-width: 767.98px) {
            .feedback-hero {
                padding: 38px 0 22px;
            }

            .feedback-card {
                padding: 1.15rem;
                border-radius: 24px;
            }

            .feedback-transaction-grid {
                grid-template-columns: 1fr;
            }

            .feedback-rating {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .feedback-rating-option:last-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .feedback-hero-title {
                font-size: 1.65rem;
            }

            .feedback-hero-subtitle {
                font-size: 0.88rem;
            }

            .feedback-rating {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="feedback-page">
    @php
        $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
        $authActionLabel = auth()->check() ? 'Profil Saya' : 'Masuk';
        $authActionRoute = auth()->check() ? route('profile.edit') : route('login');
    @endphp

    <nav class="navbar navbar-expand-lg feedback-navbar sticky-top py-2">
        <div class="container">
            <a class="feedback-brand" href="{{ route('home') }}">PD. ANUGRAH UTAMA</a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#feedbackNavbar" aria-controls="feedbackNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse mt-3 mt-lg-0" id="feedbackNavbar">
                <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link feedback-nav-link" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link feedback-nav-link" href="{{ route('produk.index') }}">Produk</a></li>
                    <li class="nav-item"><a class="nav-link feedback-nav-link" href="{{ route('riwayat-apar') }}">Riwayat & Status APAR</a></li>
                </ul>

                <div class="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0">
                    <a href="{{ $authActionRoute }}" class="feedback-nav-cta text-center">{{ $authActionLabel }}</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="feedback-hero text-center">
        <div class="container position-relative" style="z-index: 1;">
            <div class="feedback-hero-badge">
                <span class="feedback-hero-dot"></span>
                <span>@yield('hero_badge', 'Layanan Pelanggan')</span>
            </div>
            <h1 class="feedback-hero-title">@yield('header_title')</h1>
            <p class="feedback-hero-subtitle">@yield('header_subtitle')</p>
        </div>
    </section>

    <main class="feedback-main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('submit', function (event) {
            const form = event.target.closest('form[data-feedback-form]');
            if (!form) {
                return;
            }

            const button = form.querySelector('[data-submit-button]');
            if (!button || button.disabled || button.dataset.loading === 'true') {
                return;
            }

            button.dataset.loading = 'true';
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (button.dataset.loadingText || 'Memproses...');
        }, true);
    </script>
    @include('layouts.partials.sweet-alerts')
    @stack('scripts')
</body>
</html>
