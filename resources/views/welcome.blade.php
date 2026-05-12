@extends('layouts.public')

@section('title', 'PD. Anugrah Utama — Penjualan & Layanan APAR Profesional')

@section('styles')
<style>
    /* ===== UTILITY ===== */
    .container { max-width: 1140px; margin: 0 auto; padding: 0 15px; }
    .section-pad { padding: 70px 0; }
    .section-pad-sm { padding: 50px 0; }
    .btn-orange {
        display: inline-flex; align-items: center; gap: 8px;
        background: #DC2626; color: #fff; font-weight: 700; font-size: 14px;
        padding: 12px 28px; border-radius: 8px; text-decoration: none;
        transition: background .3s, transform .2s;
        border: none; cursor: pointer;
    }
    .btn-orange:hover { background: #B91C1C; transform: translateY(-1px); }
    .btn-outline {
        display: inline-flex; align-items: center; gap: 8px;
        background: transparent; color: #DC2626; font-weight: 700; font-size: 14px;
        padding: 12px 28px; border-radius: 8px; text-decoration: none;
        border: 2px solid #DC2626;
        transition: background .3s, color .3s;
    }
    .btn-outline:hover { background: #DC2626; color: #fff; }
    .btn-green {
        display: inline-flex; align-items: center; gap: 8px;
        background: #16A34A; color: #fff; font-weight: 700; font-size: 14px;
        padding: 12px 28px; border-radius: 8px; text-decoration: none;
        transition: background .3s;
    }
    .btn-green:hover { background: #15803D; }

    /* ===== HERO ===== */
    .hero-section {
        background: linear-gradient(135deg, rgba(15,23,42,.85) 40%, rgba(220,38,38,.70) 100%),
            url('https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?w=1600&q=80') center/cover no-repeat;
        padding: 100px 0 80px;
        position: relative;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        bottom: -1px; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #DC2626 0%, #F97316 100%);
    }
    .hero-inner { display: flex; align-items: center; justify-content: space-between; gap: 60px; }
    .hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255,255,255,.12); backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.2);
        padding: 6px 16px; border-radius: 50px; margin-bottom: 20px;
    }
    .hero-badge span { font-size: 11px; font-weight: 700; color: #fff; letter-spacing: 2px; text-transform: uppercase; }
    .hero-title {
        font-size: 52px; font-weight: 900; color: #fff; line-height: 1.1;
        margin-bottom: 20px; letter-spacing: -1px;
    }
    .hero-title span { color: #FBBF24; }
    .hero-sub {
        font-size: 18px; color: rgba(255,255,255,.75); line-height: 1.7;
        margin-bottom: 35px; max-width: 500px;
    }
    .hero-cta { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-right { flex-shrink: 0; }
    .hero-image-card {
        width: 340px;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 20px 60px rgba(0,0,0,.30);
    }
    .hero-image-card img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        display: block;
    }

    /* ===== SECTION HEADER ===== */
    .section-head { text-align: center; margin-bottom: 50px; }
    .section-tag {
        display: inline-block; font-size: 11px; font-weight: 700;
        color: #DC2626; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px;
    }
    .section-title { font-size: 36px; font-weight: 900; color: #1F2937; margin-bottom: 14px; letter-spacing: -.5px; }
    .section-sub { font-size: 16px; color: #6B7280; max-width: 600px; margin: 0 auto; line-height: 1.7; }

    /* ===== PROSES (STEPS) ===== */
    .steps-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; }
    .step-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        padding: 28px 20px; text-align: center; position: relative;
        transition: box-shadow .3s, transform .3s;
    }
    .step-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,.08); transform: translateY(-3px); }
    .step-num {
        width: 48px; height: 48px; border-radius: 12px;
        background: #DC2626; color: #fff; font-size: 18px; font-weight: 900;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
    }
    .step-icon {
        width: 44px; height: 44px; background: #FEF2F2; color: #DC2626; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
        margin: 0 auto 16px;
        transition: background .3s, color .3s;
    }
    .step-card:hover .step-icon { background: #DC2626; color: #fff; }
    .step-title { font-size: 14px; font-weight: 800; color: #1F2937; margin-bottom: 8px; }
    .step-desc { font-size: 12px; color: #6B7280; line-height: 1.6; }

    /* ===== KEUNGGULAN ===== */
    .feat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .feat-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        padding: 28px; text-align: center;
        transition: box-shadow .3s, transform .3s;
    }
    .feat-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,.08); transform: translateY(-3px); }
    .feat-icon {
        width: 52px; height: 52px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin: 0 auto 20px;
    }
    .feat-title { font-size: 14px; font-weight: 800; color: #1F2937; margin-bottom: 10px; }
    .feat-desc { font-size: 12px; color: #6B7280; line-height: 1.7; }

    /* ===== KATALOG ===== */
    .katalog-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .katalog-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        overflow: hidden; transition: box-shadow .3s, transform .3s;
    }
    .katalog-card:hover { box-shadow: 0 16px 48px rgba(0,0,0,.10); transform: translateY(-3px); }
    .katalog-img {
        height: 200px; background: #F3F4F6; display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }
    .katalog-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s; }
    .katalog-card:hover .katalog-img img { transform: scale(1.05); }
    .katalog-badge {
        position: absolute; top: 12px; left: 12px;
        background: #fff; color: #DC2626; font-size: 9px; font-weight: 800;
        padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 1px;
    }
    .katalog-body { padding: 20px; }
    .katalog-name { font-size: 14px; font-weight: 800; color: #1F2937; margin-bottom: 4px; }
    .katalog-spec { font-size: 11px; color: #9CA3AF; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #F3F4F6; }
    .katalog-footer { display: flex; align-items: center; justify-content: space-between; }
    .katalog-price { font-size: 18px; font-weight: 900; color: #DC2626; }
    .katalog-order {
        font-size: 11px; font-weight: 700; color: #9CA3AF; text-decoration: none;
        display: flex; align-items: center; gap: 4px; transition: color .3s;
    }
    .katalog-card:hover .katalog-order { color: #DC2626; }

    /* ===== TESTIMONI ===== */
    .testi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .testi-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 12px;
        padding: 28px; transition: box-shadow .3s, transform .3s;
    }
    .testi-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,.08); transform: translateY(-3px); }
    .testi-stars { color: #F59E0B; margin-bottom: 14px; font-size: 14px; }
    .testi-text { font-size: 13px; color: #4B5563; line-height: 1.7; font-style: italic; margin-bottom: 20px; }
    .testi-text::before { content: '"'; }
    .testi-text::after { content: '"'; }
    .testi-divider { height: 1px; background: #F3F4F6; margin-bottom: 16px; }
    .testi-author { display: flex; align-items: center; gap: 10px; }
    .testi-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: #FEF2F2;
        color: #DC2626; font-size: 14px; font-weight: 900;
        display: flex; align-items: center; justify-content: center;
    }
    .testi-name { font-size: 13px; font-weight: 800; color: #1F2937; }
    .testi-time { font-size: 11px; color: #9CA3AF; margin-top: 2px; }

    /* ===== TENTANG KAMI ===== */
    .about-section { background: #1F2937; }
    .about-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
    .about-tag { font-size: 11px; font-weight: 700; color: #DC2626; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px; }
    .about-title { font-size: 36px; font-weight: 900; color: #fff; margin-bottom: 16px; letter-spacing: -.5px; }
    .about-desc { font-size: 15px; color: #9CA3AF; line-height: 1.8; margin-bottom: 0; }
    .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .about-card {
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        border-radius: 12px; padding: 24px; text-align: center;
        transition: background .3s, border-color .3s;
    }
    .about-card:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.2); }
    .about-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px; font-size: 20px;
    }
    .about-card-title { font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 6px; }
    .about-card-desc { font-size: 12px; color: #9CA3AF; line-height: 1.6; }
    .about-cta { margin-top: 40px; display: flex; gap: 14px; flex-wrap: wrap; }

    /* ===== LOKASI ===== */
    .lokasi-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; align-items: start; }
    .lokasi-info { display: flex; flex-direction: column; gap: 16px; }
    .lokasi-card {
        background: #fff; border: 1px solid #E5E7EB; border-radius: 10px;
        padding: 20px; display: flex; align-items: flex-start; gap: 14px;
        transition: box-shadow .3s;
    }
    .lokasi-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.06); }
    .lokasi-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .lokasi-label { font-size: 10px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .lokasi-text { font-size: 13px; font-weight: 700; color: #1F2937; }
    .lokasi-sub { font-size: 11px; color: #9CA3AF; margin-top: 2px; }
    .lokasi-map { border-radius: 12px; overflow: hidden; border: 1px solid #E5E7EB; height: 340px; }

    /* ===== SERVICE SECTION (Purple Gradient) ===== */
    .service-section {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e3a5f 100%);
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }
    .service-section::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .service-tag {
        display: inline-block; font-size: 11px; font-weight: 700;
        color: #a5b4fc; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px;
    }
    .service-title { font-size: 36px; font-weight: 900; color: #fff; margin-bottom: 14px; letter-spacing: -.5px; }
    .service-sub { font-size: 16px; color: #c7d2fe; max-width: 600px; margin: 0 auto; line-height: 1.7; }
    .service-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-top: 50px; }
    .service-card {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        transition: all .4s ease;
        backdrop-filter: blur(10px);
    }
    .service-card:hover {
        background: rgba(255,255,255,.12);
        border-color: rgba(167,139,250,.4);
        transform: translateY(-5px);
    }
    .service-icon {
        width: 64px; height: 64px;
        border-radius: 16px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        transition: all .4s ease;
    }
    .service-card:hover .service-icon {
        background: linear-gradient(135deg, #818cf8, #a78bfa);
        transform: scale(1.1);
    }
    .service-card-title { font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .service-card-desc { font-size: 12px; color: #a5b4fc; line-height: 1.6; }
    .service-cta-section {
        margin-top: 60px;
        text-align: center;
    }
    .service-cta-title { font-size: 28px; font-weight: 900; color: #fff; margin-bottom: 20px; }
    .btn-service {
        display: inline-flex; align-items: center; gap: 10px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; font-weight: 700; font-size: 14px;
        padding: 14px 32px; border-radius: 10px; text-decoration: none;
        transition: all .3s ease;
        border: none; cursor: pointer;
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
    }
    .btn-service:hover {
        background: linear-gradient(135deg, #818cf8, #a78bfa);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.5);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
        .hero-inner { flex-direction: column; }
        .hero-right { display: none; }
        .hero-title { font-size: 38px; }
        .steps-grid { grid-template-columns: repeat(3, 1fr); }
        .feat-grid { grid-template-columns: repeat(2, 1fr); }
        .katalog-grid { grid-template-columns: repeat(2, 1fr); }
        .testi-grid { grid-template-columns: repeat(2, 1fr); }
        .about-inner { grid-template-columns: 1fr; gap: 40px; }
        .lokasi-grid { grid-template-columns: 1fr; }
        .service-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 767px) {
        .hero-title { font-size: 30px; }
        .section-title { font-size: 28px; }
        .service-title { font-size: 26px; }
        .steps-grid { grid-template-columns: repeat(2, 1fr); }
        .feat-grid { grid-template-columns: 1fr; }
        .feat-wide { grid-column: span 1; }
        .katalog-grid { grid-template-columns: 1fr; }
        .testi-grid { grid-template-columns: 1fr; }
        .about-grid { grid-template-columns: 1fr; }
        .service-grid { grid-template-columns: 1fr; }
        .service-cta-title { font-size: 22px; }
    }
</style>
@endsection

@section('content')

@php
    $orderEntryUrl = auth()->check() ? route('order.create') : route('login');
@endphp

{{-- ======================================================= --}}
{{-- HERO --}}
{{-- ======================================================= --}}
<section class="hero-section">
    <div class="container">
        <div class="hero-inner">
            <div>
                <div class="hero-badge">
                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                    <span>Layanan APAR Profesional</span>
                </div>
                <h1 class="hero-title">
                    Pusat Penjualan &amp;<br>
                    <span>Layanan APAR</span> Profesional
                </h1>
                <p class="hero-sub">
                    Melayani pembelian APAR baru, refill, servis, dan perawatan berkala dengan proses profesional, bergaransi, dan terpercaya.
                </p>
                <div class="hero-cta">
                    <a href="{{ $orderEntryUrl }}" class="btn-orange">
                        <i class="fa-solid fa-shopping-cart"></i> Pesan Sekarang
                    </a>
                    <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}?text={{ urlencode('Halo, saya ingin layanan APAR. Mohon info harga dan jadwal.') }}"
                       target="_blank" class="btn-green">
                        <i class="fa-brands fa-whatsapp"></i> Hubungi WhatsApp
                    </a>
                </div>
            </div>

            {{-- Right: APAR product image (desktop only) --}}
            <div class="hero-right">
                <div class="hero-image-card">
                    <img src="{{ asset('storage/produk/Y7b42cUZToTEcYZS2F74nWERmF3VlKnpLfrKNbEA.jpg') }}" alt="APAR Profesional PD. Anugrah Utama">
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- LAYANAN KAMI (SERVICE SECTION) --}}
{{-- ======================================================= --}}
<section class="service-section">
    <div class="container">
        <div class="section-head">
            <p class="service-tag">Layanan Kami</p>
            <h2 class="service-title">Solusi Lengkap untuk Kebutuhan APAR Anda</h2>
            <p class="service-sub">Dari penjualan hingga perawatan berkala, kami menyediakan semua yang Anda butuhkan untuk menjaga keselamatan fire safety di lingkungan Anda.</p>
        </div>

        <div class="service-grid">
            @php
            $services = [
                ['title' => 'Penjualan APAR', 'desc' => 'Pilihan lengkap APAR berbagai kapasitas dan jenis untuk rumah, kantor, dan industri.', 'icon' => 'fa-shopping-cart'],
                ['title' => 'Refill APAR', 'desc' => 'Isi ulang powder, CO2, atau foam dengan standar prosedur yang ketat dan bergaransi.', 'icon' => 'fa-sync-alt'],
                ['title' => 'Service & Perbaikan', 'desc' => 'Perbaikan dan perawatan APAR oleh teknisi bersertifikasi untuk performa optimal.', 'icon' => 'fa-tools'],
                ['title' => ' Inspeksi & Testing', 'desc' => 'Pengecekan berkala tekanan, fisik, dan kelayakan APAR sesuai standar.', 'icon' => 'fa-clipboard-check'],
                ['title' => 'Konsultasi & Training', 'desc' => 'Bantuan pilih produk dan edukasi penggunaan APAR yang tepat untuk tim Anda.', 'icon' => 'fa-headset'],
            ];
            @endphp
            @foreach($services as $s)
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa-solid {{ $s['icon'] }}" style="color:#fff; font-size:22px;"></i>
                </div>
                <h4 class="service-card-title">{{ $s['title'] }}</h4>
                <p class="service-card-desc">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- ALUR LAYANAN (5 STEPS) --}}
{{-- ======================================================= --}}
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head">
            <p class="section-tag">Bagaimana Kami Bekerja</p>
            <h2 class="section-title">Alur Layanan</h2>
            <p class="section-sub">Proses service &amp; refill APAR yang transparan dan mudah — dari order hingga selesai.</p>
        </div>

        <div class="steps-grid">
            @php
            $steps = [
                ['n' => '01', 'title' => 'Hubungi Kami',        'desc' => 'Chat via WhatsApp atau isi form pemesanan online.',                                              'icon' => 'fa-comment-dots'],
                ['n' => '02', 'title' => 'Konsultasi Kebutuhan', 'desc' => 'Kami bantu arahkan pilihan APAR, refill, atau servis sesuai kebutuhan Anda.',                     'icon' => 'fa-headset'],
                ['n' => '03', 'title' => 'Penawaran Harga',      'desc' => 'Dapatkan estimasi harga terbaik sesuai produk atau layanan yang dibutuhkan.',                    'icon' => 'fa-file-invoice-dollar'],
                ['n' => '04', 'title' => 'Proses Pesanan',       'desc' => 'Pesanan APAR, refill, atau servis diproses sesuai standar dan prosedur.',                          'icon' => 'fa-box-open'],
                ['n' => '05', 'title' => 'Selesai & Garansi',    'desc' => 'Unit siap digunakan kembali dengan layanan yang bergaransi.',                                    'icon' => 'fa-check-circle'],
            ];
            @endphp
            @foreach($steps as $s)
            <div class="step-card">
                <div class="step-num">{{ $s['n'] }}</div>
                <div class="step-icon"><i class="fa-solid {{ $s['icon'] }}"></i></div>
                <h4 class="step-title">{{ $s['title'] }}</h4>
                <p class="step-desc">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- KEUNGGULAN (FEATURES) --}}
{{-- ======================================================= --}}
<section class="section-pad" style="background:#F9FAFB;">
    <div class="container">
        <div class="section-head">
            <p class="section-tag">Mengapa Memilih Kami</p>
            <h2 class="section-title">Keunggulan Layanan</h2>
        </div>

        <div class="feat-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 800px; margin: 0 auto;">
            @php
            $feats = [
                ['title' => 'Layanan Cepat & Responsif',    'desc' => 'Respon cepat untuk pembelian, refill, servis, dan konsultasi kebutuhan APAR.',     'icon' => 'fa-bolt',         'bg' => 'bg-red-50',     'col' => 'text-red-600'],
                ['title' => 'Konsultasi Gratis',            'desc' => 'Bantu pilih jenis dan kapasitas APAR yang sesuai dengan kebutuhan lokasi Anda.',       'icon' => 'fa-comments',     'bg' => 'bg-blue-50',    'col' => 'text-blue-600'],
                ['title' => 'Produk APAR Berkualitas',       'desc' => 'Tersedia berbagai jenis APAR untuk rumah, kantor, gudang, proyek, dan industri.',    'icon' => 'fa-fire-extinguisher', 'bg' => 'bg-amber-50', 'col' => 'text-amber-600'],
                ['title' => 'Refill & Servis Profesional',  'desc' => 'Proses refill dan servis dilakukan sesuai prosedur agar APAR tetap aman dan siap digunakan.', 'icon' => 'fa-tools',  'bg' => 'bg-emerald-50', 'col' => 'text-emerald-600'],
            ];
            @endphp
            @foreach($feats as $f)
            <div class="feat-card">
                <div class="feat-icon {{ $f['bg'] }} {{ $f['col'] }}"><i class="fa-solid {{ $f['icon'] }}"></i></div>
                <h4 class="feat-title">{{ $f['title'] }}</h4>
                <p class="feat-desc">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- KATALOG PRODUK --}}
{{-- ======================================================= --}}
<section class="section-pad bg-white">
    <div class="container">
        <div style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:50px; gap:20px; flex-wrap:wrap;">
            <div>
                <p class="section-tag" style="text-align:left;">Katalog Produk</p>
                <h2 class="section-title" style="text-align:left; margin-bottom:0;">Produk APAR Kami</h2>
            </div>
            <a href="{{ route('produk.index') }}" class="btn-outline" style="border-color:#DC2626; color:#DC2626; padding:10px 22px; font-size:13px;">
                Lihat Semua <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
            </a>
        </div>

        <div class="katalog-grid">
            @forelse($produks as $p)
            <a href="{{ $orderEntryUrl }}" class="katalog-card">
                <div class="katalog-img">
                    @if($p->gambar)
                        <img src="{{ asset('storage/' . $p->gambar) }}" alt="{{ $p->nama }}">
                    @else
                        <i class="fa-solid fa-fire-extinguisher text-4xl text-gray-300"></i>
                    @endif
                    <span class="katalog-badge">{{ $p->jenisApar?->nama ?? 'APAR' }}</span>
                </div>
                <div class="katalog-body">
                    <h3 class="katalog-name">{{ $p->nama }}</h3>
                    <p class="katalog-spec">{{ $p->kapasitas ?? '-' }}</p>
                    <div class="katalog-footer">
                        <span class="katalog-price">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                        <span class="katalog-order">Pesan <i class="fa-solid fa-arrow-right text-[10px]"></i></span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-4 text-center py-16 text-gray-400">
                <i class="fa-solid fa-box-open text-4xl mb-4"></i>
                <p class="font-medium">Belum ada produk tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- TESTIMONI --}}
{{-- ======================================================= --}}
<section class="section-pad" style="background:#F9FAFB;">
    <div class="container">
        <div class="section-head">
            <p class="section-tag">Ulasan</p>
            <h2 class="section-title">Apa Kata Pelanggan</h2>
        </div>

        <div class="testi-grid">
            @php
            $reviews = [
                ['nama'=>'Budi Santoso',  'in'=>'B', 'rat'=>5, 'waktu'=>'3 minggu lalu',
                 'text'=>'Pelayanan sangat profesional dan cepat. APAR terpasang sesuai standar SNI. Teknisi datang tepat waktu dan ramah.'],
                ['nama'=>'Siti Rahayu',    'in'=>'S', 'rat'=>5, 'waktu'=>'1 bulan lalu',
                 'text'=>'Sudah 2 tahun jadi pelanggan, tidak pernah kecewa. Refill APAR selalu beres dalam 1 hari. Harga kompetitif.'],
                ['nama'=>'Ahmad Fauzi',   'in'=>'A', 'rat'=>4, 'waktu'=>'2 bulan lalu',
                 'text'=>'Sistem monitoring online memudahkan pantau masa berlaku APAR. Tidak perlu cek manual lagi. Tim sangat responsif.'],
            ];
            @endphp
            @foreach($reviews as $r)
            <div class="testi-card">
                <div class="testi-stars">
                    @for($i=0;$i<$r['rat'];$i++)<i class="fa-solid fa-star"></i>@endfor
                    @for($i=$r['rat'];$i<5;$i++)<i class="fa-regular fa-star text-gray-300"></i>@endfor
                </div>
                <p class="testi-text">{{ $r['text'] }}</p>
                <div class="testi-divider"></div>
                <div class="testi-author">
                    <div class="testi-avatar">{{ $r['in'] }}</div>
                    <div>
                        <p class="testi-name">{{ $r['nama'] }}</p>
                        <p class="testi-time">{{ $r['waktu'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- TENTANG KAMI --}}
{{-- ======================================================= --}}
<section class="section-pad about-section">
    <div class="container">
        <div class="about-inner">
            <div>
                <p class="about-tag">Tentang Kami</p>
                <h2 class="about-title">PD. Anugrah Utama</h2>
                <p class="about-desc">
                    Penyedia layanan APAR terpercaya di Bogor — penjualan, refill, dan service untuk rumah, kantor, dan industri. Dengan pengalaman lebih dari 10 tahun dan teknisi bersertifikasi, kami siap membantu kebutuhan safety Anda.
                </p>
                <div class="about-cta">
                    <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}?text={{ urlencode('Halo, saya ingin konsultasi tentang APAR.') }}"
                       target="_blank" class="btn-green">
                        <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                    </a>
                    <a href="{{ $orderEntryUrl }}" class="btn-outline" style="border-color:#fff; color:#fff;">
                        Pesan Sekarang
                    </a>
                </div>
            </div>
            <div class="about-grid">
                @php
                $abouts = [
                    ['icon'=>'fa-fire-extinguisher','bg'=>'bg-red-600/20',   'col'=>'text-red-400',    'title'=>'Produk APAR Berkualitas', 'desc'=>'Menyediakan berbagai pilihan APAR untuk kebutuhan rumah, toko, kantor, gudang, dan usaha.'],
                    ['icon'=>'fa-handshake',         'bg'=>'bg-amber-600/20','col'=>'text-amber-400',  'title'=>'Layanan Terpercaya',       'desc'=>'Melayani pembelian, refill, dan servis APAR dengan proses yang jelas dan profesional.'],
                    ['icon'=>'fa-bolt',              'bg'=>'bg-blue-600/20', 'col'=>'text-blue-400',   'title'=>'Respon Cepat',             'desc'=>'Kami siap membantu pertanyaan dan pemesanan melalui WhatsApp dengan respons yang cepat.'],
                    ['icon'=>'fa-tags',              'bg'=>'bg-emerald-600/20','col'=>'text-emerald-400','title'=>'Harga Kompetitif',        'desc'=>'Menawarkan produk dan layanan APAR dengan harga yang menyesuaikan kebutuhan pelanggan.'],
                ];
                @endphp
                @foreach($abouts as $a)
                <div class="about-card">
                    <div class="about-icon {{ $a['bg'] }} {{ $a['col'] }}"><i class="fa-solid {{ $a['icon'] }}"></i></div>
                    <h4 class="about-card-title">{{ $a['title'] }}</h4>
                    <p class="about-card-desc">{{ $a['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


{{-- ======================================================= --}}
{{-- LOKASI --}}
{{-- ======================================================= --}}
<section class="section-pad bg-white">
    <div class="container">
        <div class="section-head">
            <p class="section-tag">Lokasi Kami</p>
            <h2 class="section-title">Temukan Kami di Bogor</h2>
        </div>

        <div class="lokasi-grid">
            <div class="lokasi-info">
                <div class="lokasi-card">
                    <div class="lokasi-icon bg-red-50 text-red-600">
                        <i class="fa-solid fa-map-marker-alt text-lg"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">Alamat</p>
                        <p class="lokasi-text">Jl. Raya Bogor, Kota Bogor</p>
                        <p class="lokasi-sub">Jawa Barat, Indonesia</p>
                    </div>
                </div>
                <div class="lokasi-card">
                    <div class="lokasi-icon bg-green-50 text-green-600">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">WhatsApp</p>
                        <a href="https://wa.me/{{ env('WHATSAPP_CONTACT', '6285128008030') }}" target="_blank"
                           class="lokasi-text" style="color:#16A34A;">+62 851-2800-8030</a>
                        <p class="lokasi-sub">Senin – Sabtu, 08.00 – 17.00</p>
                    </div>
                </div>
                <div class="lokasi-card">
                    <div class="lokasi-icon bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-clock text-lg"></i>
                    </div>
                    <div>
                        <p class="lokasi-label">Jam Operasional</p>
                        <p class="lokasi-text">Senin – Sabtu</p>
                        <p class="lokasi-sub">08.00 – 17.00 WIB</p>
                    </div>
                </div>
            </div>
            <div class="lokasi-map">
                <div id="location-map" style="width:100%; height:100%;"></div>
            </div>
        </div>
    </div>
</section>

@endsection
