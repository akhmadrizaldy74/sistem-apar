@extends('layouts.public')

@section('title', 'Riwayat & Status APAR - PD. Anugrah Utama')

@push('styles')
<style>
    .compact-card {
        transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .compact-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 16px 36px -28px rgba(15, 23, 42, 0.28);
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
@php
    $pendingPaymentOrder = $pendingPaymentOrder ?? null;
    $canCreateOrder = ! $pendingPaymentOrder;
    $paymentWarning = 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.';

    $totalUnits = $pelanggan->units->count();
    $expiredUnits = $pelanggan->units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->isPast())->count();
    $expiringSoon = $pelanggan->units->filter(fn ($unit) => $unit->tgl_expired && ! $unit->tgl_expired->isPast() && now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false) <= 30)->count();
    $activeUnits = max(0, $totalUnits - $expiredUnits);

    $allOrders = $pelanggan->pesanan->sortByDesc(fn ($pesanan) => $pesanan->created_at);
    $activeOrderList = $allOrders->filter(fn ($pesanan) => $pesanan->isActiveOrder());
    $serviceOrders = $allOrders->filter(fn ($pesanan) => $pesanan->tipe === 'service');
    $productOrders = $allOrders->filter(fn ($pesanan) => $pesanan->tipe === 'produk');
    $attentionUnits = $pelanggan->units
        ->filter(fn ($unit) => $unit->tgl_expired && ($unit->tgl_expired->isPast() || now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false) <= 30))
        ->sortBy(fn ($unit) => $unit->tgl_expired ?? now()->addYears(20));
@endphp

<div class="min-h-screen bg-slate-50/70" x-data="customerHistoryPage()" x-init="init()">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-11 w-11 rounded-xl object-cover ring-1 ring-slate-200">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-red-700">Riwayat & Status APAR</p>
                        <h1 class="truncate text-2xl font-black tracking-tight text-slate-950">{{ $pelanggan->nama }}</h1>
                    </div>
                </div>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    Pantau riwayat pembelian, transaksi berjalan, dan kondisi unit APAR Anda dalam tampilan yang lebih ringkas.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <i class="fa-regular fa-user text-slate-400"></i>
                    Profil
                </a>

                @if($canCreateOrder)
                    <a href="{{ route('order.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700">
                        <i class="fa-solid fa-plus text-xs"></i>
                        Pesan Baru
                    </a>
                @else
                    <button type="button" disabled title="{{ $paymentWarning }}" class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-bold text-slate-500">
                        <i class="fa-solid fa-lock text-xs"></i>
                        Pesan Baru
                    </button>
                @endif
            </div>
        </header>

        @if(session('warning') || $pendingPaymentOrder)
            <div class="mt-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 text-amber-600"></i>
                    <div>
                        <p class="font-black">{{ session('warning') ?: $paymentWarning }}</p>
                        @if($pendingPaymentOrder)
                            <p class="mt-0.5 text-xs font-semibold text-amber-800">
                                Transaksi {{ $pendingPaymentOrder->orderCode() }} masih berstatus {{ $pendingPaymentOrder->publicStatusLabel() }}.
                            </p>
                        @endif
                    </div>
                </div>

                @if($pendingPaymentOrder?->canPay())
                    <a href="{{ route('order.payment', $pendingPaymentOrder) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-amber-600 px-3 py-2 text-xs font-black text-white transition hover:bg-amber-700">
                        Bayar sekarang
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                @endif
            </div>
        @endif

        <section class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Transaksi</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-2xl font-black text-slate-950">{{ $totalTransaksi }}</span>
                    <i class="fa-solid fa-receipt text-slate-300"></i>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Sedang Berjalan</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-2xl font-black text-slate-950">{{ $activeOrders }}</span>
                    <i class="fa-solid fa-clock-rotate-left text-amber-400"></i>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Unit Aktif</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-2xl font-black text-slate-950">{{ $activeUnits }}</span>
                    <i class="fa-solid fa-fire-extinguisher text-emerald-400"></i>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Perlu Perhatian</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-2xl font-black text-slate-950">{{ $expiredUnits + $expiringSoon }}</span>
                    <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                </div>
            </div>
        </section>

        <section x-data="{ activeTab: 'berjalan', filterType: 'all' }" class="mt-6">
            <div class="flex gap-2 overflow-x-auto border-b border-slate-200 pb-2">
                <button
                    type="button"
                    @click="activeTab = 'berjalan'"
                    :class="activeTab === 'berjalan' ? 'border-red-600 bg-white text-red-700 shadow-sm' : 'border-transparent text-slate-500 hover:bg-white hover:text-slate-900'"
                    class="shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition"
                >
                    Transaksi Berjalan
                </button>
                <button
                    type="button"
                    @click="activeTab = 'riwayat'"
                    :class="activeTab === 'riwayat' ? 'border-red-600 bg-white text-red-700 shadow-sm' : 'border-transparent text-slate-500 hover:bg-white hover:text-slate-900'"
                    class="shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition"
                >
                    Riwayat Pembelian
                </button>
                <button
                    type="button"
                    @click="activeTab = 'unit'"
                    :class="activeTab === 'unit' ? 'border-red-600 bg-white text-red-700 shadow-sm' : 'border-transparent text-slate-500 hover:bg-white hover:text-slate-900'"
                    class="shrink-0 rounded-xl border px-4 py-2 text-sm font-black transition"
                >
                    Unit APAR Saya
                </button>
            </div>

            <div x-show="activeTab === 'berjalan'" x-cloak x-transition.opacity.duration.150ms class="mt-5">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-slate-950">Transaksi yang sedang berlangsung</h2>
                                <p class="text-sm text-slate-500">Status dibuat ringkas supaya mudah dipantau.</p>
                            </div>
                            <button type="button" @click="activeTab = 'riwayat'" class="hidden text-sm font-black text-red-600 hover:text-red-700 sm:inline-flex">
                                Lihat semua
                            </button>
                        </div>

                        @forelse($activeOrderList as $pesanan)
                            @include('public.riwayat-apar.partials.order-card')
                        @empty
                            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <h3 class="mt-3 text-base font-black text-slate-950">Belum ada transaksi berjalan</h3>
                                <p class="mt-1 text-sm text-slate-500">Pesanan aktif akan muncul di bagian ini.</p>
                            </div>
                        @endforelse
                    </div>

                    <aside class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-black text-slate-950">Unit Perlu Perhatian</h2>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600">{{ $attentionUnits->count() }}</span>
                        </div>

                        @if($attentionUnits->isNotEmpty())
                            <div class="mt-3 space-y-2">
                                @foreach($attentionUnits->take(4) as $unit)
                                    @php
                                        $daysUntilExpiry = $unit->tgl_expired
                                            ? (int) now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false)
                                            : null;
                                        $isExpired = ! is_null($daysUntilExpiry) && $daysUntilExpiry < 0;
                                    @endphp
                                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black text-slate-900">{{ $unit->produk?->nama ?? 'Unit APAR' }}</p>
                                                <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $unit->produk?->kapasitas ?? ($unit->ukuran ?? '-') }}</p>
                                            </div>
                                            <span class="shrink-0 text-xs font-black {{ $isExpired ? 'text-red-700' : 'text-amber-700' }}">
                                                {{ $isExpired ? 'Expired' : $daysUntilExpiry . ' hari' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-3 rounded-lg bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-500">Semua unit masih aman.</p>
                        @endif

                        @if($attentionUnits->isNotEmpty())
                            @if($canCreateOrder)
                                <a href="{{ route('order.create') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-red-700">
                                    <i class="fa-solid fa-wrench text-xs"></i>
                                    Ajukan Service
                                </a>
                            @else
                                <button type="button" disabled title="{{ $paymentWarning }}" class="mt-4 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-black text-slate-500">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                    Ajukan Service
                                </button>
                                <p class="mt-2 text-center text-xs font-semibold text-amber-700">{{ $paymentWarning }}</p>
                            @endif
                        @endif
                    </aside>
                </div>
            </div>

            <div x-show="activeTab === 'riwayat'" x-cloak x-transition.opacity.duration.150ms class="mt-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-950">Riwayat transaksi</h2>
                        <p class="text-sm text-slate-500">Daftar pembelian dan layanan yang pernah dibuat.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="filterType = 'all'" :class="filterType === 'all' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="rounded-lg border px-3 py-2 text-xs font-black transition">Semua {{ $allOrders->count() }}</button>
                        <button type="button" @click="filterType = 'produk'" :class="filterType === 'produk' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="rounded-lg border px-3 py-2 text-xs font-black transition">Pembelian {{ $productOrders->count() }}</button>
                        <button type="button" @click="filterType = 'service'" :class="filterType === 'service' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="rounded-lg border px-3 py-2 text-xs font-black transition">Service {{ $serviceOrders->count() }}</button>
                    </div>
                </div>

                <div x-show="filterType === 'all'" class="mt-4 space-y-3">
                    @forelse($allOrders as $pesanan)
                        @include('public.riwayat-apar.partials.order-card')
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                            <h3 class="text-base font-black text-slate-950">Belum ada transaksi</h3>
                            <p class="mt-1 text-sm text-slate-500">Riwayat pembelian dan layanan akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>

                <div x-show="filterType === 'produk'" class="mt-4 space-y-3">
                    @forelse($productOrders as $pesanan)
                        @include('public.riwayat-apar.partials.order-card')
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                            <h3 class="text-base font-black text-slate-950">Belum ada riwayat pembelian</h3>
                            <p class="mt-1 text-sm text-slate-500">Pesanan produk APAR akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>

                <div x-show="filterType === 'service'" class="mt-4 space-y-3">
                    @forelse($serviceOrders as $pesanan)
                        @include('public.riwayat-apar.partials.order-card')
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                            <h3 class="text-base font-black text-slate-950">Belum ada riwayat service</h3>
                            <p class="mt-1 text-sm text-slate-500">Layanan refill atau service akan tampil di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div x-show="activeTab === 'unit'" x-cloak x-transition.opacity.duration.150ms class="mt-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-950">Unit APAR Saya</h2>
                        <p class="text-sm text-slate-500">Monitoring unit dibuat kecil dan fokus ke informasi utama.</p>
                    </div>
                    <span class="text-sm font-bold text-slate-500">{{ $totalUnits }} unit terdaftar</span>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($pelanggan->units->sortBy(fn ($unit) => $unit->tgl_expired ?? now()->addYears(20)) as $unit)
                        @php
                            $daysUntilExpiry = $unit->tgl_expired
                                ? (int) now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false)
                                : null;
                            $isExpired = ! is_null($daysUntilExpiry) && $daysUntilExpiry < 0;
                            $isExpiringSoon = ! $isExpired && ! is_null($daysUntilExpiry) && $daysUntilExpiry <= 30;
                            $conditionLabel = $isExpired ? 'Expired' : ($isExpiringSoon ? 'Perlu Service' : 'Aman');
                            $conditionClass = $isExpired
                                ? 'bg-red-50 text-red-700 ring-red-100'
                                : ($isExpiringSoon ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-emerald-50 text-emerald-700 ring-emerald-100');
                        @endphp

                        <article class="compact-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-black text-slate-950">{{ $unit->produk?->nama ?? 'APAR' }}</h3>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $unit->produk?->kapasitas ?? ($unit->ukuran ?? '-') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black ring-1 {{ $conditionClass }}">{{ $conditionLabel }}</span>
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <dt class="font-bold uppercase tracking-wide text-slate-400">Expiry</dt>
                                    <dd class="mt-1 font-black text-slate-900">{{ $unit->tgl_expired?->format('d M Y') ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-bold uppercase tracking-wide text-slate-400">Sisa Waktu</dt>
                                    <dd class="mt-1 font-black {{ $isExpired ? 'text-red-700' : ($isExpiringSoon ? 'text-amber-700' : 'text-slate-900') }}">
                                        @if(is_null($daysUntilExpiry))
                                            -
                                        @elseif($isExpired)
                                            Lewat {{ abs($daysUntilExpiry) }} hari
                                        @else
                                            {{ $daysUntilExpiry }} hari
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if($isExpired || $isExpiringSoon)
                                @if($canCreateOrder)
                                    <a href="{{ route('order.create') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-700 transition hover:bg-red-100">
                                        <i class="fa-solid fa-wrench text-[10px]"></i>
                                        Service / Refill
                                    </a>
                                @else
                                    <button type="button" disabled title="{{ $paymentWarning }}" class="mt-4 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-black text-slate-500">
                                        <i class="fa-solid fa-lock text-[10px]"></i>
                                        Service / Refill
                                    </button>
                                @endif
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:col-span-2 xl:col-span-3">
                            <h3 class="text-base font-black text-slate-950">Belum ada unit APAR</h3>
                            <p class="mt-1 text-sm text-slate-500">Unit APAR akan tampil setelah transaksi selesai diproses.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-8 grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-950">Review & Balasan Admin</h2>
                        <p class="text-sm text-slate-500">Modelnya dibuat seperti marketplace: pelanggan kasih nilai, admin tetap bisa merespons.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $pelanggan->testimonis->count() }}</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($pelanggan->testimonis->sortByDesc(fn ($testimoni) => $testimoni->tanggal ?? $testimoni->created_at)->take(3) as $testimoni)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-1 text-amber-400 text-sm">
                                    @for($i = 0; $i < $testimoni->rating; $i++)
                                        <i class="fa-solid fa-star"></i>
                                    @endfor
                                    @for($i = $testimoni->rating; $i < 5; $i++)
                                        <i class="fa-regular fa-star text-slate-300"></i>
                                    @endfor
                                </div>
                                @php
                                    $reviewStatusClass = match($testimoni->status) {
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'rejected' => 'bg-red-50 text-red-700',
                                        default => 'bg-amber-50 text-amber-700',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $reviewStatusClass }}">{{ $testimoni->status }}</span>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-slate-700">{{ $testimoni->review }}</p>
                            <p class="mt-2 text-xs font-bold text-slate-400">{{ optional($testimoni->tanggal)->format('d M Y') ?? $testimoni->created_at?->format('d M Y') }}</p>

                            @if($testimoni->admin_note)
                                <div class="mt-3 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                    <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Balasan Admin</p>
                                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">{{ $testimoni->admin_note }}</p>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                            Belum ada review. Setelah transaksi selesai, Anda bisa beri bintang dan ulasan dari kartu transaksi.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-950">Bantuan & Komplain</h2>
                        <p class="text-sm text-slate-500">Komplain dicatat di sistem, lalu admin menindaklanjuti detailnya melalui WhatsApp.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $pelanggan->complains->count() }}</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($pelanggan->complains->sortByDesc(fn ($complain) => $complain->tanggal ?? $complain->created_at)->take(3) as $complain)
                        @php
                            $complainStatusClass = match($complain->status_penyelesaian) {
                                'selesai' => 'bg-emerald-50 text-emerald-700',
                                'diproses' => 'bg-amber-50 text-amber-700',
                                default => 'bg-red-50 text-red-700',
                            };
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $complain->pesanan?->orderCode() ?? 'Komplain Umum' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ optional($complain->tanggal)->format('d M Y') ?? $complain->created_at?->format('d M Y') }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $complainStatusClass }}">{{ $complain->status_penyelesaian }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-700">{{ $complain->isi_complain }}</p>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                            Belum ada komplain. Jika ada kendala pada transaksi, gunakan tombol bantuan di kartu pesanan.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <script>
        function customerHistoryPage() {
            return {
                lastUpdate: null,
                pollingInterval: 30000,

                init() {
                    this.pollForUpdates();
                    setInterval(() => this.pollForUpdates(), this.pollingInterval);
                    this.listenForUpdates();
                },

                async pollForUpdates() {
                    try {
                        const url = '{{ route('riwayat-apar.status') }}' + (this.lastUpdate ? '?since=' + encodeURIComponent(this.lastUpdate) : '');
                        const response = await fetch(url);

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.orders.length > 0) {
                                this.lastUpdate = data.server_time;
                                this.$dispatch('status-updated', data);
                            }
                        }
                    } catch (error) {
                        console.log('Polling error:', error);
                    }
                },

                listenForUpdates() {
                    if (typeof window.Echo !== 'undefined') {
                        const pelangganId = {{ $pelanggan->id }};

                        window.Echo.channel(`pelanggan-${pelangganId}`)
                            .listen('pesanan.status-diperbarui', (data) => {
                                this.$dispatch('status-updated', { order: data });
                            });
                    }
                }
            }
        }
    </script>
</div>
@endsection
