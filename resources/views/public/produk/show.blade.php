@extends('layouts.public')

@section('title', $produk->nama . ' - Produk')

@section('content')
    <section class="bg-gray-50">
        @php
            $stokSiapJual = (int) ($produk->catalog_ready_stock ?? 0);
            $isHabis = $stokSiapJual <= 0;
            $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
            $jenisNama = trim((string) ($produk->jenisApar?->nama ?? 'APAR'));
            $jenisBadge = str_contains(strtolower($jenisNama), 'carbon') || str_contains(strtolower($jenisNama), 'co2')
                ? 'CO2'
                : (str_contains(strtolower($jenisNama), 'foam') || str_contains(strtolower($jenisNama), 'busa') ? 'FOAM' : 'DRY CHEMICAL POWDER');
            $stockBadge = $isHabis ? 'HABIS' : 'TERSEDIA';
            $stockLabel = $isHabis ? 'Habis' : $stokSiapJual . ' unit tersedia';
            $productWaUrl = \App\Support\WhatsApp::companyLink('Halo PD Anugrah Utama, saya ingin menanyakan produk ' . $produk->nama . '.');
            $productExpiryMeta = $productExpiryMeta ?? [];
            $hasExpiryInfo = ($productExpiryMeta['expired_at_label'] ?? '-') !== '-';
            $productExpiryStatus = $hasExpiryInfo ? ($productExpiryMeta['status_label'] ?? '-') : '-';
            $productExpiryTone = match ($productExpiryMeta['status_key'] ?? null) {
                'expired' => 'text-red-700',
                'hampir' => 'text-amber-700',
                default => 'text-slate-700',
            };
        @endphp

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
            <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-900 transition hover:border-slate-300 hover:shadow-md" data-reveal>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Kembali ke Produk
            </a>

            <div class="mt-4 overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_60px_-36px_rgba(15,23,42,0.38)]" data-reveal>
                <div class="grid sm:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)]">
                    <div class="border-b border-slate-200 bg-gradient-to-b from-slate-50 via-white to-red-50/35 sm:border-b-0 sm:border-r">
                        <div class="flex h-full min-h-[9rem] items-center justify-center px-4 py-4 sm:min-h-[11rem] lg:px-6">
                            @if($produk->resolved_image_url)
                                <img src="{{ $produk->resolved_image_url }}" alt="{{ $produk->nama }}" class="w-full max-w-[12rem] max-h-[14rem] object-contain object-center sm:max-w-[16rem] sm:max-h-[18rem] md:max-w-[18rem] md:max-h-[22rem] lg:max-w-[22rem] lg:max-h-[26rem]">
                            @else
                                <div class="flex h-full min-h-[8rem] w-full items-center justify-center text-slate-300 sm:min-h-[10rem]">
                                    <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 sm:p-5 lg:p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-red-700 ring-1 ring-red-100">
                                {{ $jenisBadge }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] ring-1 {{ $isHabis ? 'bg-red-600 text-white ring-red-600/30' : 'bg-emerald-50 text-emerald-800 ring-emerald-200' }}">
                                {{ $stockBadge }}
                            </span>
                        </div>

                        <p class="mt-3 text-[0.85rem] font-extrabold uppercase tracking-[0.2em] text-emerald-700">{{ $produk->merek ?: '-' }}</p>
                        <h1 class="mt-1 text-[1.4rem] font-black leading-tight tracking-tight text-slate-950 sm:text-[1.6rem]">{{ $produk->nama }}</h1>
                        <p class="mt-2 text-[1.5rem] font-black leading-none tracking-tight text-red-700 sm:text-[1.7rem]">{{ $formatRupiah($produk->harga) }}</p>

                        <div class="mt-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/75 p-3 sm:p-4">
                            <dl class="divide-y divide-slate-200 text-[0.88rem] text-slate-700 sm:text-[0.92rem]">
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Merek:</dt>
                                    <dd class="font-semibold uppercase">{{ $produk->merek ?: '-' }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Jenis:</dt>
                                    <dd class="font-semibold leading-5">{{ $jenisNama }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Ukuran:</dt>
                                    <dd class="font-semibold">{{ $produk->kapasitas ?: '-' }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Stok:</dt>
                                    <dd class="font-semibold {{ $isHabis ? 'text-red-700' : 'text-slate-700' }}">{{ $stockLabel }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Masa Berlaku:</dt>
                                    <dd class="font-semibold {{ $productExpiryTone }}">{{ $productExpiryMeta['expired_at_label'] ?? '-' }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Sisa:</dt>
                                    <dd class="font-semibold {{ $productExpiryTone }}">{{ $hasExpiryInfo ? ($productExpiryMeta['remaining_label'] ?? '-') : '-' }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Status:</dt>
                                    <dd class="font-semibold {{ $productExpiryTone }}">{{ $productExpiryStatus }}</dd>
                                </div>
                                <div class="flex flex-col gap-0.5 py-2 first:pt-0 last:pb-0 sm:flex-row sm:gap-3">
                                    <dt class="font-bold text-slate-950 sm:w-20 sm:shrink-0">Fungsi:</dt>
                                    <dd class="font-semibold leading-5">{{ $produk->penggunaan ?: '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-4 border-t border-slate-200 pt-4">
                            @auth
                                @php
                                    $canCustomerOrder = !auth()->user()->isAdmin() && !auth()->user()->isTeknisi();
                                @endphp

                                @if($canCustomerOrder)
                                    <form action="{{ route('order.create') }}" method="GET" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="produk" value="{{ $produk->id }}">
                                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">

                                        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <label for="qty" class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-500">Jumlah</label>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" onclick="this.parentNode.querySelector('input').stepDown(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-base font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50">-</button>
                                                    <input type="number" name="qty" id="qty" value="{{ $isHabis ? 0 : 1 }}" min="{{ $isHabis ? 0 : 1 }}" max="{{ $stokSiapJual }}" @disabled($isHabis) class="h-11 w-16 rounded-xl border border-slate-200 bg-white text-center text-base font-black text-slate-900 focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-60">
                                                    <button type="button" onclick="this.parentNode.querySelector('input').stepUp(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-base font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50">+</button>
                                                </div>
                                            </div>
                                            <p class="text-sm font-semibold {{ $isHabis ? 'text-red-700' : 'text-slate-600' }}">Stok: {{ $stockLabel }}</p>
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <button type="submit" formaction="{{ route('keranjang.store') }}" formmethod="POST" @disabled($isHabis) class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-xs font-black uppercase tracking-[0.12em] transition {{ $isHabis ? 'cursor-not-allowed bg-slate-200 text-slate-400' : 'border-2 border-red-600 bg-red-50 text-red-700 hover:bg-red-100 hover:shadow-md' }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                Masukkan Keranjang
                                            </button>
                                            <button type="submit" @disabled($isHabis) class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-xs font-black uppercase tracking-[0.12em] transition {{ $isHabis ? 'cursor-not-allowed bg-slate-300 text-white' : 'bg-red-700 text-white shadow-lg shadow-red-700/20 hover:bg-red-800' }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                Pesan Sekarang
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50/70 p-5">
                                        <p class="text-sm font-black text-amber-900">Pemesanan pelanggan hanya tersedia untuk akun pelanggan.</p>
                                        <p class="mt-2 text-xs font-semibold leading-6 text-amber-800">Admin dan teknisi tetap dapat melihat katalog, tetapi tidak dapat membuat pesanan pelanggan dari halaman ini.</p>
                                    </div>
                                @endif
                            @else
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <label for="qty-guest" class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-500">Jumlah</label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="this.parentNode.querySelector('input').stepDown(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-base font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50">-</button>
                                                <input type="number" id="qty-guest" value="{{ $isHabis ? 0 : 1 }}" min="{{ $isHabis ? 0 : 1 }}" max="{{ $stokSiapJual }}" @disabled($isHabis) class="h-11 w-16 rounded-xl border border-slate-200 bg-white text-center text-base font-black text-slate-900 focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-60">
                                                <button type="button" onclick="this.parentNode.querySelector('input').stepUp(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-base font-black text-slate-700 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-50">+</button>
                                            </div>
                                        </div>
                                        <p class="text-sm font-semibold {{ $isHabis ? 'text-red-700' : 'text-slate-600' }}">Stok: {{ $stockLabel }}</p>
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-xs font-black uppercase tracking-[0.12em] transition {{ $isHabis ? 'pointer-events-none bg-slate-200 text-slate-400' : 'border-2 border-red-600 bg-red-50 text-red-700 hover:bg-red-100 hover:shadow-md' }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            Masukkan Keranjang
                                        </a>
                                        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-xs font-black uppercase tracking-[0.12em] transition {{ $isHabis ? 'pointer-events-none bg-slate-300 text-white' : 'bg-red-700 text-white shadow-lg shadow-red-700/20 hover:bg-red-800' }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            Pesan Sekarang
                                        </a>
                                    </div>
                                </div>
                            @endauth

                            <a href="{{ $productWaUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3.5 text-xs font-black text-green-700 transition hover:bg-green-100 sm:w-auto">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                Tanya Produk via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
