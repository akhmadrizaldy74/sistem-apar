<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Sistem APAR'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-apar.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon-apar.svg') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .hero-section { background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%); color: white; padding: 40px 0; margin-bottom: 40px; border-radius: 0 0 30px 30px; box-shadow: 0 10px 20px rgba(185, 28, 28, 0.2); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: #ef4444; box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.25); }
        .btn-primary { background-color: #dc2626; border-color: #dc2626; border-radius: 10px; padding: 10px 25px; font-weight: 600; }
        .btn-primary:hover { background-color: #b91c1c; border-color: #b91c1c; }
    </style>
</head>
<body>
    @php
        $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
    @endphp
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger" href="{{ route('home') }}">
                <i class="fa-solid fa-fire-extinguisher me-2"></i>PD. Anugrah Utama
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto fw-medium">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ $orderEntryUrl }}">Pesan / Service</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('complain.create') }}">Komplain</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('testimoni.create') }}">Testimoni</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-5 fw-bold">@yield('header_title')</h1>
            <p class="lead mb-0 opacity-75">@yield('header_subtitle')</p>
        </div>
    </div>

    <main class="container mb-5">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @include('layouts.partials.sweet-alerts')
    @stack('scripts')
</body>
</html>
