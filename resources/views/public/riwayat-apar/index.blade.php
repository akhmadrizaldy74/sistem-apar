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
    $paymentWarning = $pendingPaymentOrder?->hasPendingPurchasePriceRequest()
        ? 'Pengajuan harga Anda sedang menunggu persetujuan admin.'
        : 'Selesaikan pembayaran sebelumnya sebelum membuat pesanan baru.';

    $totalUnits = $pelanggan->units->count();
    $expiredUnits = $pelanggan->units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->isPast())->count();
    $expiringSoon = $pelanggan->units->filter(fn ($unit) => $unit->tgl_expired && ! $unit->tgl_expired->isPast() && now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false) <= 30)->count();
    $activeUnits = max(0, $totalUnits - $expiredUnits);

    $allOrders = $pelanggan->pesanan->sortByDesc(fn ($pesanan) => $pesanan->created_at);
    $activeOrderList = $allOrders->filter(fn ($pesanan) => $pesanan->isActiveOrder());
    $serviceOrders = $allOrders->filter(fn ($pesanan) => $pesanan->tipe === 'service');
    $productOrders = $allOrders->filter(fn ($pesanan) => $pesanan->tipe === 'produk');
    $unitRefillLocks = $unitRefillLocks ?? [];
    $initialActiveTab = request('tab') === 'unit' ? 'unit' : 'riwayat';
    $attentionUnits = $pelanggan->units
        ->filter(fn ($unit) => $unit->tgl_expired && ($unit->tgl_expired->isPast() || now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false) <= 30))
        ->sortBy(fn ($unit) => $unit->tgl_expired ?? now()->addYears(20));
@endphp

<div class="min-h-screen bg-slate-50/70" x-data="customerHistoryPage()" x-init="init()">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-red-700">Riwayat & Status APAR</p>
                    <h1 class="truncate text-2xl font-black tracking-tight text-slate-950">{{ $pelanggan->nama }}</h1>
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

        @if($pendingPaymentOrder)
            <div class="mt-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 text-amber-600"></i>
                    <div>
                        <p class="font-black">{{ $paymentWarning }}</p>
                        @if($pendingPaymentOrder)
                            <p class="mt-0.5 text-xs font-semibold text-amber-800">
                                {{ $pendingPaymentOrder->transactionDisplayName() }} pada {{ $pendingPaymentOrder->displayTransactionDateTime() }}
                                {{ $pendingPaymentOrder->hasPendingPurchasePriceRequest() ? 'masih menunggu keputusan admin untuk pengajuan harga.' : 'masih menunggu penyelesaian pembayaran.' }}
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

        @if(!empty($historyNotice))
            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 text-blue-600"></i>
                    <div>
                        <p class="font-black">Profil pelanggan belum lengkap</p>
                        <p class="mt-0.5 text-sm text-blue-800">{{ $historyNotice }}</p>
                    </div>
                </div>
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

        <section x-data="{ activeTab: '{{ $initialActiveTab }}', filterType: 'all' }" class="mt-6">
            <div class="flex gap-2 overflow-x-auto border-b border-slate-200 pb-2">
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

            <div x-show="activeTab === 'unit'" x-cloak x-transition.opacity.duration.150ms class="mt-5" x-data="{ selectedRefillUnits: [] }">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-950">Unit APAR Saya</h2>
                        <p class="text-sm text-slate-500">Monitoring unit dibuat kecil dan fokus ke informasi utama.</p>
                    </div>
                    <span class="text-sm font-bold text-slate-500">{{ $totalUnits }} unit terdaftar</span>
                </div>

                <form method="POST" action="{{ route('riwayat-apar.ajukan-refill') }}" class="mt-4">
                    @csrf

                    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">Ajukan refill beberapa unit sekaligus</p>
                            <p class="mt-1 text-sm font-medium text-slate-500">Centang unit yang berstatus perlu refill, lalu lanjutkan satu checkout refill tanpa harus satu per satu.</p>
                        </div>

                        @if($canCreateOrder)
                            <button
                                type="submit"
                                :disabled="selectedRefillUnits.length === 0"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-black text-white shadow-sm shadow-red-600/20 transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500 disabled:shadow-none"
                            >
                                <i class="fa-solid fa-fire-extinguisher text-xs"></i>
                                <span x-text="selectedRefillUnits.length > 0 ? `Ajukan Refill ${selectedRefillUnits.length} Unit` : 'Ajukan Refill Unit Terpilih'"></span>
                            </button>
                        @else
                            <button type="button" disabled title="{{ $paymentWarning }}" class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-200 px-4 py-3 text-sm font-black text-slate-500">
                                <i class="fa-solid fa-lock text-xs"></i>
                                Ajukan Refill Unit Terpilih
                            </button>
                        @endif
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse($pelanggan->units->sortBy(fn ($unit) => $unit->tgl_expired ?? now()->addYears(20)) as $unit)
                            @php
                                $daysUntilExpiry = $unit->tgl_expired
                                    ? (int) now()->startOfDay()->diffInDays($unit->tgl_expired->copy()->startOfDay(), false)
                                    : null;
                                $isExpired = ! is_null($daysUntilExpiry) && $daysUntilExpiry < 0;
                                $isExpiringSoon = ! $isExpired && ! is_null($daysUntilExpiry) && $daysUntilExpiry <= 30;
                                $needsRefill = $isExpired || $isExpiringSoon;
                                $conditionLabel = $needsRefill ? 'Perlu Refill' : 'Aman';
                                $conditionClass = $needsRefill
                                    ? 'bg-amber-50 text-amber-700 ring-amber-100'
                                    : 'bg-emerald-50 text-emerald-700 ring-emerald-100';
                                $refillLock = $unitRefillLocks[$unit->id] ?? null;
                                $unitCode = $unit->no_seri ?: ('UNIT-' . $unit->id);
                            @endphp

                            <article class="compact-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-black text-slate-950">{{ $unit->produk?->nama ?? 'APAR' }}</h3>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">Nomor Unit: {{ $unitCode }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black ring-1 {{ $conditionClass }}">{{ $conditionLabel }}</span>
                                </div>

                                <dl class="mt-4 grid grid-cols-1 gap-3 text-xs">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <dt class="font-bold uppercase tracking-wide text-slate-400">Jenis APAR</dt>
                                            <dd class="mt-1 font-black text-slate-900">{{ $unit->produk?->jenisApar?->nama ?? ($unit->bahan ?: '-') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-bold uppercase tracking-wide text-slate-400">Ukuran</dt>
                                            <dd class="mt-1 font-black text-slate-900">{{ $unit->produk?->kapasitas ?? ($unit->ukuran ?? '-') }}</dd>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <dt class="font-bold uppercase tracking-wide text-slate-400">Expired</dt>
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
                                    </div>
                                    <div>
                                        <dt class="font-bold uppercase tracking-wide text-slate-400">Status</dt>
                                        <dd class="mt-1 font-black {{ $needsRefill ? 'text-amber-700' : 'text-emerald-700' }}">{{ $conditionLabel }}</dd>
                                    </div>
                                </dl>

                                @if($needsRefill)
                                    <div class="mt-4 space-y-3">
                                        @if($refillLock)
                                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs font-bold leading-5 text-amber-800">
                                                {{ $refillLock['message'] ?? 'Unit ini sedang dalam proses refill.' }}
                                            </div>
                                        @elseif($canCreateOrder)
                                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-700">
                                                <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" x-model="selectedRefillUnits" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                                                <span>Pilih untuk Refill</span>
                                            </label>

                                            <button type="submit" name="action_unit_id" value="{{ $unit->id }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-black text-red-700 transition hover:bg-red-100">
                                                <i class="fa-solid fa-fire-extinguisher text-[10px]"></i>
                                                Ajukan Refill
                                            </button>
                                        @else
                                            <button type="button" disabled title="{{ $paymentWarning }}" class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-xs font-black text-slate-500">
                                                <i class="fa-solid fa-lock text-[10px]"></i>
                                                Ajukan Refill
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:col-span-2 xl:col-span-3">
                                <h3 class="text-base font-black text-slate-950">Belum ada unit APAR</h3>
                                <p class="mt-1 text-sm text-slate-500">Unit APAR akan tampil setelah transaksi selesai diproses.</p>
                            </div>
                        @endforelse
                    </div>
                </form>
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
                            <p class="mt-2 text-xs font-bold text-slate-400">{{ $testimoni->displaySubmittedDateTime() }}</p>

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
                                    <p class="text-sm font-black text-slate-900">{{ $complain->relatedTransactionLabel() }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $complain->relatedTransactionDateTime() }}</p>
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

        <!-- Custom Toast/Alert Modal -->
        <div x-show="toast.show" x-cloak class="relative z-[60]" aria-labelledby="toast-title" role="dialog" aria-modal="true">
            <div x-show="toast.show" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeToast()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="toast.show" x-transition class="relative transform overflow-hidden rounded-2xl bg-white text-center shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm p-6" @keydown.escape.window="closeToast()">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full mb-4" :class="toast.type === 'success' ? 'bg-emerald-100' : 'bg-red-100'">
                            <i class="text-3xl" :class="toast.type === 'success' ? 'fa-solid fa-circle-check text-emerald-600' : 'fa-solid fa-circle-exclamation text-red-600'"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mb-2" id="toast-title" x-text="toast.type === 'success' ? 'Sukses' : 'Gagal'"></h3>
                        <p class="text-sm text-slate-600 mb-6" x-text="toast.message"></p>
                        <button type="button" @click="closeToast()" class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm" :class="toast.type === 'success' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complain Modal -->
        <div x-show="showComplainModal" x-cloak class="relative z-50" aria-labelledby="complain-modal-title" role="dialog" aria-modal="true">
            <div x-show="showComplainModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModals()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showComplainModal" x-transition class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" @keydown.escape.window="closeModals()" @click.stop>
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50">
                                        <i class="fa-solid fa-headset text-red-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900" id="complain-modal-title">Butuh Bantuan / Komplain</h3>
                                        <p class="text-xs text-slate-500">Sampaikan kendala transaksi Anda, admin akan menindaklanjuti melalui WhatsApp.</p>
                                    </div>
                                </div>
                                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-500 transition">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="mb-5 rounded-xl bg-red-50/50 p-4 border border-red-100/50">
                                <h4 class="text-xs font-bold text-red-800 mb-2 uppercase tracking-wider">Ringkasan Transaksi</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <span class="block text-xs text-slate-500">Produk/Layanan</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.title"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Tanggal</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.date"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Status</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.status"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Total</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.total"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Jenis</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.tipe"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- WhatsApp Info -->
                            <div class="mb-4 rounded-xl bg-emerald-50/50 p-3 border border-emerald-100/50 flex items-center gap-3">
                                <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i>
                                <div class="flex-1">
                                    <span class="block text-xs text-slate-500">WhatsApp Pelanggan</span>
                                    <span class="text-sm font-semibold text-slate-800">{{ $pelanggan->no_wa ?? '-' }}</span>
                                </div>
                                <span class="text-[10px] text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full font-bold">Admin akan menghubungi Anda</span>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold leading-6 text-slate-900 mb-2">Detail Keluhan</label>
                                    <textarea x-model="complainForm.isi_complain" rows="5" class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-red-600 sm:text-sm sm:leading-6" :class="formErrors.isi_complain ? 'ring-red-500' : ''" placeholder="Ceritakan kendala yang Anda alami, misalnya barang belum diterima, hasil service belum sesuai, atau jadwal berubah..."></textarea>
                                    <p class="mt-1 text-xs text-red-600" x-show="formErrors.isi_complain" x-text="formErrors.isi_complain"></p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold leading-6 text-slate-900 mb-1">Foto Pendukung <span class="text-xs font-normal text-slate-500">(Opsional)</span></label>
                                    <p class="text-xs text-slate-500 mb-2">Upload foto kondisi APAR, bukti kendala, atau dokumen pendukung. Format JPG/PNG/WebP, max 5MB.</p>
                                    <p class="mt-1 text-xs text-red-600" x-show="formErrors.foto" x-text="formErrors.foto"></p>
                                    
                                    <div class="mt-2 flex justify-center rounded-xl border border-dashed border-slate-300 px-6 py-6 hover:border-red-300 transition" x-show="!complainForm.fotoPreview">
                                        <div class="text-center">
                                            <i class="fa-regular fa-image mx-auto text-3xl text-slate-300 mb-2"></i>
                                            <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                                <label for="complain-foto" class="relative cursor-pointer rounded-md bg-white font-semibold text-red-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-red-600 focus-within:ring-offset-2 hover:text-red-500">
                                                    <span>Pilih foto</span>
                                                    <input id="complain-foto" x-ref="complainFoto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handleComplainFoto">
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative mt-2 rounded-xl border border-slate-200 overflow-hidden inline-block" x-show="complainForm.fotoPreview">
                                        <img :src="complainForm.fotoPreview" class="h-32 w-auto object-cover" />
                                        <button type="button" @click="removeComplainFoto()" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 shadow-sm">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                            <button type="button" @click="submitComplain()" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 sm:w-40 disabled:opacity-50">
                                <span x-show="!isSubmitting">Kirim Komplain</span>
                                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengirim...</span>
                            </button>
                            <button type="button" @click="closeModals()" :disabled="isSubmitting" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-40">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Received Modal -->
        <div x-show="showConfirmModal" x-cloak class="relative z-50" aria-labelledby="confirm-modal-title" role="dialog" aria-modal="true">
            <div x-show="showConfirmModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModals()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showConfirmModal" x-transition class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl" @keydown.escape.window="closeModals()" @click.stop>
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50">
                                        <i class="fa-solid fa-box-open text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900" id="confirm-modal-title">Konfirmasi Pesanan Diterima</h3>
                                        <p class="text-xs text-slate-500">Gunakan tombol ini setelah pesanan benar-benar sudah Anda terima.</p>
                                    </div>
                                </div>
                                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-500 transition">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <h4 class="text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Ringkasan Transaksi</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <span class="block text-xs text-slate-500">Produk/Layanan</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.title"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Tanggal</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.date"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Status</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.status"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Total</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.total"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Jenis</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.tipe"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-medium leading-6 text-blue-900">
                                Setelah dikonfirmasi, Anda bisa langsung mengisi ulasan tanpa menunggu tombol lain.
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                            <button type="button" @click="submitConfirmReceived()" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 sm:w-52 disabled:opacity-50">
                                <span x-show="!isSubmitting">Ya, Sudah Diterima</span>
                                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...</span>
                            </button>
                            <button type="button" @click="closeModals()" :disabled="isSubmitting" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-40">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimoni Modal -->
        <div x-show="showTestimoniModal" x-cloak class="relative z-50" aria-labelledby="testimoni-modal-title" role="dialog" aria-modal="true">
            <div x-show="showTestimoniModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModals()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showTestimoniModal" x-transition class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl" @keydown.escape.window="closeModals()" @click.stop>
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50">
                                        <i class="fa-solid fa-star text-amber-500"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900" id="testimoni-modal-title">Isi Ulasan</h3>
                                        <p class="text-xs text-slate-500">Bagikan pengalaman Anda setelah pesanan selesai dan sudah diterima.</p>
                                    </div>
                                </div>
                                <button type="button" @click="closeModals()" class="text-slate-400 hover:text-slate-500 transition">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="mb-5 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <h4 class="text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Ringkasan Transaksi</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <span class="block text-xs text-slate-500">Produk/Layanan</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.title"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Tanggal</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.date"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Status</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.status"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Total</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.total"></span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-slate-500">Jenis</span>
                                        <span class="font-semibold text-slate-800" x-text="modalData.tipe"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex flex-col items-center justify-center py-2">
                                    <span class="text-sm font-semibold text-slate-700 mb-2">Kualitas Layanan</span>
                                    <div class="flex items-center gap-3">
                                        <template x-for="star in 5">
                                            <button type="button" @click="testimoniForm.rating = star" class="text-4xl transition hover:scale-110">
                                                <i class="fa-solid fa-star" :class="star <= testimoniForm.rating ? 'text-amber-400' : 'text-slate-200'"></i>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="mt-1 text-xs text-red-600" x-show="formErrors.rating" x-text="formErrors.rating"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold leading-6 text-slate-900 mb-2">Ulasan</label>
                                    <textarea x-model="testimoniForm.review" rows="5" class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 sm:text-sm sm:leading-6" :class="formErrors.review ? 'ring-red-500' : ''" placeholder="Ceritakan pengalaman Anda terhadap kualitas produk, layanan, dan respons admin..."></textarea>
                                    <p class="mt-1 text-xs text-red-600" x-show="formErrors.review" x-text="formErrors.review"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold leading-6 text-slate-900 mb-1">Foto Testimoni <span class="text-xs font-normal text-slate-500">(Opsional)</span></label>
                                    <p class="text-xs text-slate-500 mb-2">Upload foto APAR, hasil layanan, atau dokumentasi pendukung. Format JPG/PNG/WebP, max 5MB.</p>
                                    <p class="mt-1 text-xs text-red-600" x-show="formErrors.foto" x-text="formErrors.foto"></p>
                                    
                                    <div class="mt-2 flex justify-center rounded-xl border border-dashed border-slate-300 px-6 py-6 hover:border-amber-300 transition" x-show="!testimoniForm.fotoPreview">
                                        <div class="text-center">
                                            <i class="fa-regular fa-image mx-auto text-3xl text-slate-300 mb-2"></i>
                                            <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                                <label for="testimoni-foto" class="relative cursor-pointer rounded-md bg-white font-semibold text-amber-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-amber-600 focus-within:ring-offset-2 hover:text-amber-500">
                                                    <span>Pilih foto</span>
                                                    <input id="testimoni-foto" x-ref="testimoniFoto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="handleTestimoniFoto">
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative mt-2 rounded-xl border border-slate-200 overflow-hidden inline-block" x-show="testimoniForm.fotoPreview">
                                        <img :src="testimoniForm.fotoPreview" class="h-32 w-auto object-cover" />
                                        <button type="button" @click="removeTestimoniFoto()" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-700 shadow-sm">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Checkbox Anonim -->
                                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" x-model="testimoniForm.is_anonymous" class="mt-0.5 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                        <div>
                                            <span class="text-sm font-semibold text-slate-900">Sembunyikan nama saya pada testimoni</span>
                                            <p class="text-xs text-slate-500 mt-0.5">Nama Anda akan ditampilkan sebagai <strong>Pelanggan Anonim</strong> di halaman publik.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                            <button type="button" @click="submitTestimoni()" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 sm:w-40 disabled:opacity-50">
                                <span x-show="!isSubmitting">Kirim Ulasan</span>
                                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengirim...</span>
                            </button>
                            <button type="button" @click="closeModals()" :disabled="isSubmitting" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-40">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
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



                // Form Errors
                formErrors: {},
                clearErrors() {
                    this.formErrors = {};
                },

                // Modal State
                showComplainModal: false,
                showConfirmModal: false,
                showTestimoniModal: false,
                isSubmitting: false,
                modalData: { id: null, title: '', date: '', total: '', status: '', tipe: '' },
                complainForm: { isi_complain: '', foto: null, fotoPreview: null },
                testimoniForm: { rating: 5, review: '', foto: null, fotoPreview: null, is_anonymous: false },

                openComplainModal(id, title, date, total, status, tipe) {
                    this.clearErrors();
                    this.modalData = { id, title, date, total, status, tipe };
                    this.complainForm = { isi_complain: '', foto: null, fotoPreview: null };
                    if (this.$refs.complainFoto) this.$refs.complainFoto.value = '';
                    this.showComplainModal = true;
                },

                openConfirmModal(id, title, date, total, status, tipe) {
                    this.clearErrors();
                    this.modalData = { id, title, date, total, status, tipe };
                    this.showConfirmModal = true;
                },

                openTestimoniModal(id, title, date, total, status, tipe) {
                    this.clearErrors();
                    this.modalData = { id, title, date, total, status, tipe };
                    this.testimoniForm = { rating: 5, review: '', foto: null, fotoPreview: null, is_anonymous: false };
                    if (this.$refs.testimoniFoto) this.$refs.testimoniFoto.value = '';
                    this.showTestimoniModal = true;
                },

                handleComplainFoto(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            this.formErrors = { ...this.formErrors, foto: 'Ukuran file maksimal 5MB.' };
                            return;
                        }
                        this.formErrors = { ...this.formErrors, foto: null };
                        this.complainForm.foto = file;
                        this.complainForm.fotoPreview = URL.createObjectURL(file);
                    }
                },
                removeComplainFoto() {
                    this.complainForm.foto = null;
                    this.complainForm.fotoPreview = null;
                    if (this.$refs.complainFoto) this.$refs.complainFoto.value = '';
                },

                handleTestimoniFoto(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            this.formErrors = { ...this.formErrors, foto: 'Ukuran file maksimal 5MB.' };
                            return;
                        }
                        this.formErrors = { ...this.formErrors, foto: null };
                        this.testimoniForm.foto = file;
                        this.testimoniForm.fotoPreview = URL.createObjectURL(file);
                    }
                },
                removeTestimoniFoto() {
                    this.testimoniForm.foto = null;
                    this.testimoniForm.fotoPreview = null;
                    if (this.$refs.testimoniFoto) this.$refs.testimoniFoto.value = '';
                },

                closeModals() {
                    this.showComplainModal = false;
                    this.showConfirmModal = false;
                    this.showTestimoniModal = false;
                    this.clearErrors();
                },

                async submitConfirmReceived() {
                    this.clearErrors();
                    this.isSubmitting = true;

                    const snapshot = { ...this.modalData };

                    try {
                        const response = await fetch(`{{ url('/riwayat-apar') }}/${snapshot.id}/confirm-received`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.closeModals();
                            window.showAppAlert(result.message || 'Pesanan berhasil dikonfirmasi.', 'success');
                            this.pollForUpdates();

                            if (result.open_review) {
                                window.setTimeout(() => {
                                    this.openTestimoniModal(snapshot.id, snapshot.title, snapshot.date, snapshot.total, snapshot.status, snapshot.tipe);
                                }, 250);
                            } else {
                                setTimeout(() => location.reload(), 1200);
                            }

                            return;
                        }

                        window.showAppAlert(result.message || 'Terjadi kesalahan saat mengonfirmasi pesanan.', 'error');
                    } catch (e) {
                        window.showAppAlert('Gagal menyimpan konfirmasi pesanan. Periksa koneksi internet Anda.', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitComplain() {
                    this.clearErrors();
                    if (!this.complainForm.isi_complain) {
                        this.formErrors = { isi_complain: 'Detail keluhan wajib diisi.' };
                        return;
                    }
                    this.isSubmitting = true;
                    
                    const formData = new FormData();
                    formData.append('pesanan_id', this.modalData.id);
                    formData.append('isi_complain', this.complainForm.isi_complain);
                    if (this.complainForm.foto) {
                        formData.append('foto', this.complainForm.foto);
                    }

                    try {
                        const response = await fetch('{{ route("complain.store") }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.closeModals();
                            window.showAppAlert(result.message || 'Komplain berhasil dikirim. Admin akan menindaklanjuti melalui WhatsApp.', 'success');
                            this.pollForUpdates();
                            setTimeout(() => location.reload(), 2000);
                        } else if (response.status === 422 && result.errors) {
                            const errors = {};
                            for (const [key, msgs] of Object.entries(result.errors)) {
                                errors[key] = Array.isArray(msgs) ? msgs[0] : msgs;
                            }
                            this.formErrors = errors;
                        } else {
                            window.showAppAlert(result.message || 'Terjadi kesalahan saat mengirim komplain.', 'error');
                        }
                    } catch (e) {
                        window.showAppAlert('Gagal mengirim komplain. Periksa koneksi internet Anda.', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async submitTestimoni() {
                    this.clearErrors();
                    let hasError = false;
                    const errors = {};
                    if (!this.testimoniForm.rating || this.testimoniForm.rating < 1) {
                        errors.rating = 'Rating wajib dipilih.';
                        hasError = true;
                    }
                    if (!this.testimoniForm.review || !this.testimoniForm.review.trim()) {
                        errors.review = 'Ulasan wajib diisi.';
                        hasError = true;
                    }
                    if (hasError) {
                        this.formErrors = errors;
                        return;
                    }
                    this.isSubmitting = true;

                    const formData = new FormData();
                    formData.append('pesanan_id', this.modalData.id);
                    formData.append('rating', this.testimoniForm.rating);
                    formData.append('review', this.testimoniForm.review);
                    formData.append('is_anonymous', this.testimoniForm.is_anonymous ? '1' : '0');
                    if (this.testimoniForm.foto) {
                        formData.append('foto', this.testimoniForm.foto);
                    }

                    try {
                        const response = await fetch('{{ route("testimoni.store") }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.closeModals();
                            window.showAppAlert(result.message || 'Terima kasih, testimoni Anda berhasil dikirim dan akan direview admin.', 'success');
                            this.pollForUpdates();
                            setTimeout(() => location.reload(), 2000);
                        } else if (response.status === 422 && result.errors) {
                            const errors = {};
                            for (const [key, msgs] of Object.entries(result.errors)) {
                                errors[key] = Array.isArray(msgs) ? msgs[0] : msgs;
                            }
                            this.formErrors = errors;
                        } else {
                            window.showAppAlert(result.message || 'Terjadi kesalahan saat mengirim testimoni.', 'error');
                        }
                    } catch (e) {
                        window.showAppAlert('Gagal mengirim testimoni. Periksa koneksi internet Anda.', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async pollForUpdates() {
                    try {
                        const url = '{{ route("riwayat-apar.status") }}' + (this.lastUpdate ? '?since=' + encodeURIComponent(this.lastUpdate) : '');
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
