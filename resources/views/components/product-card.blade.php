@props(['produk'])

@php
    $stokSiapJual = (int) ($produk->stok_tersedia ?? 0);
    $isHabis = $stokSiapJual <= 0;
@endphp

<div class="group bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-lg shadow-gray-200/40 hover:shadow-2xl hover:shadow-gray-300/40 transition duration-300 hover:-translate-y-1">
    <a href="{{ route('produk.show', $produk) }}" class="block">
        <div class="aspect-[4/5] relative overflow-hidden bg-gray-50">
            @if($produk->gambar)
                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-700">
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
        <p class="text-xs font-bold {{ $isHabis ? 'text-red-600' : 'text-gray-500' }} mt-3">
            {{ $isHabis ? 'Stok habis' : 'Stok siap jual: ' . $stokSiapJual . ' unit' }}
        </p>
        <p class="text-2xl font-black text-red-600 tracking-tight mt-4">Rp {{ number_format($produk->harga, 0, ',', '.') }}</p>
        <div class="mt-6 flex gap-2">
            @if($isHabis)
                <button type="button" disabled class="flex-1 py-3 px-4 bg-gray-100 border border-gray-200 cursor-not-allowed text-gray-400 font-bold text-xs uppercase tracking-wider rounded-xl text-center flex items-center justify-center gap-1">
                    Stok Habis
                </button>
            @else
                @auth
                    <form action="{{ route('keranjang.store') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="w-full py-3 px-4 bg-red-700 hover:bg-red-800 hover:shadow-md text-white font-black text-xs uppercase tracking-wider rounded-xl transition text-center flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Keranjang
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex-1 py-3 px-4 bg-red-700 hover:bg-red-800 hover:shadow-md text-white font-black text-xs uppercase tracking-wider rounded-xl transition text-center flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Keranjang
                    </a>
                @endauth
            @endif
            <a href="{{ route('produk.show', $produk) }}" class="py-3 px-4 bg-white border border-slate-200 text-slate-700 font-black text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition text-center flex items-center justify-center" title="Detail">
                Detail
            </a>
        </div>
    </div>
</div>
