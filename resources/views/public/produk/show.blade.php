@extends('layouts.public')

@section('title', $produk->nama . ' - Produk')

@section('content')
    <section class="bg-gray-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="flex items-center justify-between gap-4" data-reveal>
                <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-3 px-5 py-3 bg-white border border-gray-200 rounded-2xl font-bold text-gray-900 hover:shadow-md transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Kembali
                </a>
                <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center gap-3 px-5 py-3 bg-gray-200 text-gray-900 rounded-2xl font-black hover:bg-gray-300 transition shadow-lg shadow-gray-200/50">
                    Kembali
                </a>
            </div>

            @php
                $stokSiapJual = (int) ($produk->stok_tersedia ?? 0);
                $isHabis = $stokSiapJual <= 0;
                $productWaUrl = \App\Support\WhatsApp::companyLink('Halo PD Anugrah Utama, saya ingin menanyakan produk ' . $produk->nama . '.');
            @endphp

            <div class="mt-10 grid lg:grid-cols-2 gap-10 items-start">
                <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-xl shadow-gray-200/60 overflow-hidden" data-reveal>
                    <div class="aspect-[4/4] bg-gray-50 overflow-hidden flex items-center justify-center">
                        @if($produk->resolved_image_url)
                            <img src="{{ $produk->resolved_image_url }}" alt="{{ $produk->nama }}" class="w-full h-full object-contain p-6">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                    </div>
                </div>

                <div data-reveal>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-gray-200 rounded-xl shadow-sm">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $produk->jenisApar?->nama ?? 'APAR' }}</span>
                        @if($produk->merek)
                            <span class="text-[10px] font-black text-gray-300">-</span>
                            <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">{{ $produk->merek }}</span>
                        @endif
                        @if($produk->kapasitas)
                            <span class="text-[10px] font-black text-gray-300">-</span>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ $produk->kapasitas }}</span>
                        @endif
                    </div>

                    <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-gray-900 mt-5">{{ $produk->nama }}</h1>
                    <p class="text-3xl font-black text-red-600 tracking-tight mt-6">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                    <p class="mt-4 text-sm font-black {{ $isHabis ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ $isHabis ? 'Stok habis' : 'Stok siap jual: ' . $stokSiapJual . ' unit' }}
                    </p>

                    @if($produk->penggunaan)
                        <div class="mt-8 bg-white rounded-[1.75rem] border border-gray-100 shadow-lg shadow-gray-200/50 p-8">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Penggunaan</p>
                            <p class="text-sm font-semibold text-gray-700 leading-relaxed mt-4">{{ $produk->penggunaan }}</p>
                        </div>
                    @endif

                    <div class="mt-8 space-y-4">
                        <a href="{{ $productWaUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-3 rounded-[1.75rem] border border-green-200 bg-green-50 px-6 py-4 text-sm font-black text-green-700 transition hover:bg-green-100">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            Tanya Produk via WhatsApp
                        </a>

                        @auth
                            @php
                                $canCustomerOrder = !auth()->user()->isAdmin() && !auth()->user()->isTeknisi();
                            @endphp
                            @if($canCustomerOrder)
                            <form action="{{ route('order.create') }}" method="GET" class="bg-white rounded-[1.75rem] border border-gray-100 shadow-lg shadow-gray-200/50 p-6">
                                @csrf
                                <input type="hidden" name="produk" value="{{ $produk->id }}">
                                <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                
                                <div class="flex items-center gap-4 mb-6">
                                    <label for="qty" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="this.parentNode.querySelector('input').stepDown(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 font-bold hover:bg-gray-200 transition disabled:cursor-not-allowed disabled:opacity-50">-</button>
                                        <input type="number" name="qty" id="qty" value="{{ $isHabis ? 0 : 1 }}" min="{{ $isHabis ? 0 : 1 }}" max="{{ $stokSiapJual }}" @disabled($isHabis) class="w-16 h-10 text-center bg-gray-50 border border-gray-200 rounded-xl font-black text-gray-900 focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-60">
                                        <button type="button" onclick="this.parentNode.querySelector('input').stepUp(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 font-bold hover:bg-gray-200 transition disabled:cursor-not-allowed disabled:opacity-50">+</button>
                                    </div>
                                    <span class="text-xs text-gray-400 font-medium">Stok siap jual: {{ $stokSiapJual }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button type="submit" formaction="{{ route('keranjang.store') }}" formmethod="POST" @disabled($isHabis) class="w-full px-5 py-4 {{ $isHabis ? 'bg-gray-200 cursor-not-allowed text-gray-400' : 'bg-red-50 border-2 border-red-600 hover:bg-red-100 text-red-700 hover:shadow-md' }} font-black rounded-2xl transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Masukkan Keranjang
                                    </button>
                                    <button type="submit" @disabled($isHabis) class="w-full px-5 py-4 {{ $isHabis ? 'bg-gray-300 cursor-not-allowed text-white' : 'bg-red-700 hover:bg-red-800 text-white shadow-xl shadow-red-700/25' }} font-black rounded-2xl transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Pesan Sekarang
                                    </button>
                                </div>
                            </form>
                            @else
                            <div class="bg-white rounded-[1.75rem] border border-amber-200 bg-amber-50/60 shadow-lg shadow-gray-200/50 p-6">
                                <p class="text-sm font-black text-amber-900">Pemesanan pelanggan hanya tersedia untuk akun pelanggan.</p>
                                <p class="mt-2 text-xs font-semibold text-amber-800">Admin dan teknisi tetap melihat katalog, tetapi tidak dapat membuat pesanan pelanggan dari halaman ini.</p>
                            </div>
                            @endif
                        @else
                            <div class="bg-white rounded-[1.75rem] border border-gray-100 shadow-lg shadow-gray-200/50 p-6">
                                <div class="flex items-center gap-4 mb-6">
                                    <label for="qty" class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah</label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="this.parentNode.querySelector('input').stepDown(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 font-bold hover:bg-gray-200 transition disabled:cursor-not-allowed disabled:opacity-50">-</button>
                                        <input type="number" id="qty" value="{{ $isHabis ? 0 : 1 }}" min="{{ $isHabis ? 0 : 1 }}" max="{{ $stokSiapJual }}" @disabled($isHabis) class="w-16 h-10 text-center bg-gray-50 border border-gray-200 rounded-xl font-black text-gray-900 focus:ring-2 focus:ring-red-600/20 disabled:cursor-not-allowed disabled:opacity-60">
                                        <button type="button" onclick="this.parentNode.querySelector('input').stepUp(); this.parentNode.querySelector('input').dispatchEvent(new Event('change'))" @disabled($isHabis) class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 font-bold hover:bg-gray-200 transition disabled:cursor-not-allowed disabled:opacity-50">+</button>
                                    </div>
                                    <span class="text-xs text-gray-400 font-medium">Stok siap jual: {{ $stokSiapJual }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <a href="{{ route('login') }}" class="w-full px-5 py-4 {{ $isHabis ? 'bg-gray-200 cursor-not-allowed text-gray-400 pointer-events-none' : 'bg-red-50 border-2 border-red-600 hover:bg-red-100 text-red-700 hover:shadow-md' }} font-black rounded-2xl transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Masukkan Keranjang
                                    </a>
                                    <a href="{{ route('login') }}" class="w-full px-5 py-4 {{ $isHabis ? 'bg-gray-300 cursor-not-allowed text-white pointer-events-none' : 'bg-red-700 hover:bg-red-800 text-white shadow-xl shadow-red-700/25' }} font-black rounded-2xl transition text-center flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        Pesan Sekarang
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
