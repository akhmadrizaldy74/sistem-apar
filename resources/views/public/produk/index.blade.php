@extends('layouts.public')

@section('title', 'Produk - PD. Anugrah Utama')

@section('content')
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6" data-reveal>
                <div class="max-w-3xl">
                    <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.3em] mb-4">Katalog</p>
                    <h1 class="text-4xl sm:text-5xl font-black tracking-tight">Produk APAR</h1>
                    <p class="text-gray-600 font-medium leading-relaxed mt-5">
                        Semua produk APAR dan perlengkapan terkait yang tersedia di sistem.
                    </p>
                </div>
                <a href="{{ route('cek-apar') }}" class="px-6 py-3 bg-gray-50 border border-gray-100 text-gray-900 font-bold rounded-2xl hover:shadow-md transition">
                    Cek APAR
                </a>
            </div>

            @if(session('success'))
                <div class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 font-semibold text-sm flex items-center gap-3" data-reveal>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 font-semibold text-sm flex items-center gap-3" data-reveal>
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" class="mt-8 bg-gray-50 border border-gray-100 rounded-[1.75rem] p-6 grid md:grid-cols-4 gap-4 items-end" data-reveal>
                <div class="md:col-span-2">
                    <label for="jenis_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Filter Jenis APAR</label>
                    <select name="jenis_apar_id" id="jenis_apar_id" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisApars as $jenisApar)
                            <option value="{{ $jenisApar->id }}" @selected($filters['jenis_apar_id'] == $jenisApar->id)>{{ $jenisApar->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="merek" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Filter Merek</label>
                    <select name="merek" id="merek" class="w-full px-5 py-4 bg-white border border-gray-100 rounded-2xl font-bold text-gray-900 focus:ring-2 focus:ring-red-600/20">
                        <option value="">Semua Merek</option>
                        @foreach($mereks as $merek)
                            <option value="{{ $merek }}" @selected($filters['merek'] === $merek)>{{ $merek }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-5 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition uppercase tracking-widest text-xs">
                        Filter
                    </button>
                    <a href="{{ route('produk.index') }}" class="flex-1 px-5 py-4 bg-white border border-gray-100 text-gray-700 font-black rounded-2xl hover:shadow-md transition text-center uppercase tracking-widest text-xs">
                        Reset
                    </a>
                </div>
            </form>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($produks as $produk)
                    @php
                        $stokSiapJual = (int) ($produk->stok_tersedia ?? 0);
                        $isHabis = $stokSiapJual <= 0;
                    @endphp
                    <div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-lg shadow-gray-200/40 hover:shadow-2xl hover:shadow-gray-300/40 transition duration-300 hover:-translate-y-1" data-reveal>
                        <a href="{{ route('produk.show', $produk) }}" class="block">
                            <div class="aspect-[4/5] relative overflow-hidden bg-gray-100">
                                @if($produk->gambar)
                                    <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                                <div class="absolute top-5 left-5">
                                    <span class="px-3 py-1 bg-white/90 backdrop-blur text-red-700 text-[9px] font-black uppercase tracking-widest rounded-xl shadow-sm">
                                        {{ $produk->jenisApar?->nama ?? 'APAR' }}
                                    </span>
                                </div>
                                @if($isHabis)
                                    <div class="absolute top-5 right-5">
                                        <span class="px-3 py-1 bg-red-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl shadow-sm">
                                            Habis
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="p-7">
                            <h2 class="text-base font-black text-gray-900 truncate">{{ $produk->nama }}</h2>
                            <p class="text-sm font-semibold text-gray-600 mt-3">{{ $produk->jenisApar?->nama ?? 'APAR' }} - {{ $produk->kapasitas ?? '-' }}</p>
                            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mt-3">{{ $produk->merek }}</p>
                            <p class="text-xs font-semibold text-gray-500 mt-3">{{ $produk->penggunaan ?? '-' }}</p>
                            <p class="text-[11px] font-bold {{ $isHabis ? 'text-red-600' : 'text-gray-500' }} mt-3">
                                {{ $isHabis ? 'Stok habis' : 'Stok siap jual: ' . $stokSiapJual . ' unit' }}
                            </p>
                            <p class="text-2xl font-black text-red-600 tracking-tight mt-4">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
                            <div class="mt-6 flex flex-col sm:flex-row gap-2">
                                @auth
                                    <form action="{{ route('keranjang.store') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" @disabled($isHabis) class="w-full py-4 {{ $isHabis ? 'bg-gray-300 cursor-not-allowed text-white' : 'bg-red-700 hover:bg-red-800 text-white shadow-lg shadow-red-700/20' }} font-black text-[10px] uppercase tracking-widest rounded-xl transition flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            {{ $isHabis ? 'Stok Habis' : '+ Keranjang' }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ $isHabis ? '#' : route('login') }}" class="flex-1 py-4 {{ $isHabis ? 'bg-gray-300 cursor-not-allowed pointer-events-none' : 'bg-red-700 hover:bg-red-800 shadow-lg shadow-red-700/20' }} text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition text-center flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ $isHabis ? 'Stok Habis' : '+ Keranjang' }}
                                    </a>
                                @endauth
                                <a href="{{ route('produk.show', $produk) }}" class="px-5 py-4 bg-gray-50 border border-gray-100 text-gray-500 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-gray-200 hover:text-gray-800 transition text-center" title="Detail">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-gray-50 border border-gray-100 rounded-2xl p-12 text-center text-gray-600 font-medium" data-reveal>
                        Belum ada produk di database.
                    </div>
                @endforelse
            </div>

            <div class="mt-12" data-reveal>
                {{ $produks->links() }}
            </div>
        </div>
    </section>
@endsection
