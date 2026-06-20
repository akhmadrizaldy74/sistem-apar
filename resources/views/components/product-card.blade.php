@props(['produk'])

@php
    $stokSiapJual = (int) ($produk->stok_tersedia ?? 0);
    $isHabis = $stokSiapJual <= 0;
    $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
    $jenisNama = trim((string) ($produk->jenisApar?->nama ?? 'APAR'));
    $jenisBadge = str_contains(strtolower($jenisNama), 'carbon') || str_contains(strtolower($jenisNama), 'co2')
        ? 'CO2'
        : (str_contains(strtolower($jenisNama), 'foam') || str_contains(strtolower($jenisNama), 'busa') ? 'FOAM' : 'DRY CHEMICAL POWDER');
    $stockBadge = $isHabis ? 'HABIS' : 'TERSEDIA';
    $stockDetail = $isHabis ? 'Habis' : $stokSiapJual . ' unit tersedia';
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_50px_-28px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-red-200 hover:shadow-[0_28px_65px_-30px_rgba(185,28,28,0.28)]">
    <a href="{{ route('produk.show', $produk) }}" class="block">
        <div class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 via-white to-red-50/30">
            <div class="relative z-20 flex items-start justify-between gap-2 px-4 pt-4 sm:px-5 sm:pt-5">
                <span class="inline-flex items-center justify-center rounded-full bg-red-50 px-3 py-1.5 text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-red-700 ring-1 ring-red-100 whitespace-nowrap">
                    {{ $jenisBadge }}
                </span>
                <span class="inline-flex shrink-0 items-center justify-center rounded-full px-3 py-1.5 text-[9px] sm:text-[10px] font-black uppercase tracking-wider ring-1 {{ $isHabis ? 'bg-red-600 text-white ring-red-600/25' : 'bg-emerald-50 text-emerald-800 ring-emerald-200' }}">
                    {{ $stockBadge }}
                </span>
            </div>
            <div class="px-6 pb-7 pt-4 sm:px-8 sm:pb-8 sm:pt-4">
                <div class="flex min-h-[12.4rem] items-end justify-center sm:min-h-[13.5rem]">
                @if($produk->resolved_image_url)
                        <img src="{{ $produk->resolved_image_url }}" alt="{{ $produk->nama }}" class="max-h-[11.8rem] w-full object-contain object-center transition-transform duration-700 group-hover:scale-105 sm:max-h-[12.7rem]">
                @else
                        <div class="flex min-h-[11.8rem] w-full items-center justify-center text-slate-300 sm:min-h-[12.7rem]">
                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                @endif
                </div>
            </div>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-6 sm:p-7">
        <p class="text-[0.94rem] font-extrabold uppercase tracking-[0.18em] text-emerald-700">{{ $produk->merek ?: '-' }}</p>
        <h2 class="mt-3 text-[1.18rem] font-black leading-snug text-slate-950 sm:text-[1.22rem]">{{ $produk->nama }}</h2>

        <div class="mt-4 space-y-2 text-[0.94rem] font-medium leading-7 text-slate-700">
            <p>
                <span class="font-bold text-slate-900">Jenis:</span>
                {{ ' ' }}{{ $jenisNama }}
            </p>
            <p>
                <span class="font-bold text-slate-900">Ukuran:</span>
                {{ ' ' }}{{ $produk->kapasitas ?: '-' }}
            </p>
            <p>
                <span class="font-bold text-slate-900">Fungsi:</span>
                {{ ' ' }}{{ $produk->penggunaan ?: '-' }}
            </p>
            <p class="{{ $isHabis ? 'font-bold text-red-700' : '' }}">
                <span class="font-bold {{ $isHabis ? 'text-red-700' : 'text-slate-900' }}">Stok:</span>
                {{ ' ' }}{{ $stockDetail }}
            </p>
        </div>

        <div class="mt-auto pt-6">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Harga</p>
            <p class="mt-2 text-[1.85rem] font-black leading-none tracking-tight text-red-700">{{ $formatRupiah($produk->harga) }}</p>

            <div class="mt-5 flex flex-col gap-2">
                @if($isHabis)
                    <button type="button" disabled class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-slate-400">
                        Stok Habis
                    </button>
                @else
                    @auth
                        <form action="{{ route('keranjang.store') }}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-red-800 hover:shadow-lg hover:shadow-red-700/20">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Keranjang
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-700 px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-white transition hover:bg-red-800 hover:shadow-lg hover:shadow-red-700/20">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Keranjang
                        </a>
                    @endauth
                @endif
                <a href="{{ route('produk.show', $produk) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3.5 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-950">
                    Detail
                </a>
            </div>
        </div>
    </div>
</article>
