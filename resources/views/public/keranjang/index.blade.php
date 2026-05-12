@extends('layouts.public')

@section('title', 'Keranjang Belanja - PD. Anugrah Utama')

@section('styles')
<style>
    .cart-page{background:linear-gradient(180deg,#fff 0%,#f8fafc 100%)}
    .cart-card{background:#fff;border:1px solid #e2e8f0;border-radius:1.5rem;box-shadow:0 10px 30px rgba(15,23,42,.06)}
    .cart-item{background:#fff;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1rem;transition:border-color .2s ease, box-shadow .2s ease}
    .cart-item:hover{border-color:#fecaca;box-shadow:0 8px 24px rgba(15,23,42,.06)}
    .cart-thumb{width:5.5rem;height:5.5rem;border-radius:1rem;overflow:hidden;background:#f8fafc;border:1px solid #e2e8f0;flex-shrink:0}
    .cart-thumb img{width:100%;height:100%;object-fit:cover}
    .qty-btn{width:2.25rem;height:2.25rem;border-radius:.85rem;border:1px solid #e2e8f0;background:#f8fafc;color:#334155;display:flex;align-items:center;justify-content:center;transition:all .2s ease}
    .qty-btn:hover{background:#fff1f2;border-color:#fca5a5;color:#dc2626}
    .qty-btn:disabled{opacity:.45;cursor:not-allowed}
    .qty-box{min-width:2.75rem;height:2.25rem;padding:0 .75rem;border-radius:.85rem;border:1px solid #e2e8f0;background:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:800;color:#0f172a}
    .btn-primary-cart{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.95rem 1rem;border-radius:1rem;background:#dc2626;color:#fff;font-size:.82rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;transition:all .2s ease}
    .btn-primary-cart:hover{background:#b91c1c}
    .btn-secondary-cart{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.9rem 1rem;border-radius:1rem;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-size:.8rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;transition:all .2s ease}
    .btn-secondary-cart:hover{background:#f8fafc}
    .mini-note{border-radius:1rem;padding:.85rem 1rem;font-size:.78rem;font-weight:700;line-height:1.5}
    .mini-note.ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#047857}
    .mini-note.wait{background:#fffbeb;border:1px solid #fde68a;color:#b45309}
</style>
@endsection

@section('content')
    @php
        $totalUnit = $keranjangs->sum('qty');
        $negotiationEligible = $totalUnit >= 10;
        $remainingToNego = max(0, 10 - $totalUnit);
    @endphp

    <section class="cart-page min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Lanjut Belanja
            </a>

            <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950">Keranjang Belanja</h1>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $keranjangs->count() }} produk, {{ $totalUnit }} unit</p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Total Belanja</p>
                    <p class="mt-1 text-3xl font-black tracking-tight text-red-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($keranjangs->isEmpty())
                <div class="cart-card mt-8 px-8 py-12 text-center">
                    <h2 class="text-2xl font-black text-slate-900">Keranjang Anda masih kosong</h2>
                    <p class="mt-3 text-sm font-medium text-slate-500">Pilih produk dulu dari katalog, nanti otomatis muncul di sini.</p>
                    <a href="{{ route('produk.index') }}" class="btn-primary-cart mt-6 inline-flex w-auto px-6">
                        Lihat Katalog
                    </a>
                </div>
            @else
                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-2">
                        @foreach($keranjangs as $item)
                            <div class="cart-item">
                                <div class="flex flex-col gap-4 sm:flex-row">
                                    <div class="cart-thumb">
                                        @if($item->produk->gambar)
                                            <img src="{{ asset('storage/' . $item->produk->gambar) }}" alt="{{ $item->produk->nama }}">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-xs font-black uppercase tracking-[0.16em] text-red-600">{{ $item->produk->jenisApar?->nama ?? 'APAR' }}</p>
                                                <h3 class="mt-1 text-lg font-black text-slate-950">{{ $item->produk->nama }}</h3>
                                                <p class="mt-1 text-sm font-medium text-slate-500">{{ $item->produk->merek ?? '-' }} @if($item->produk->kapasitas) • {{ $item->produk->kapasitas }} @endif</p>
                                                <p class="mt-2 text-sm font-semibold text-slate-500">Harga/unit: Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                                            </div>
                                            <p class="text-xl font-black text-red-600">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                        </div>

                                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-center gap-2">
                                                <form action="{{ route('keranjang.update', $item) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="qty" value="{{ max(1, $item->qty - 1) }}">
                                                    <button type="submit" class="qty-btn" {{ $item->qty <= 1 ? 'disabled' : '' }}>
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                    </button>
                                                </form>

                                                <span class="qty-box">{{ $item->qty }}</span>

                                                <form action="{{ route('keranjang.update', $item) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="qty" value="{{ $item->qty + 1 }}">
                                                    <button type="submit" class="qty-btn">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    </button>
                                                </form>
                                            </div>

                                            <form action="{{ route('keranjang.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-bold text-red-600 hover:text-red-700 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="lg:col-span-1">
                        <div class="cart-card p-6 lg:sticky lg:top-24">
                            <h2 class="text-lg font-black text-slate-950">Ringkasan</h2>

                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-500">Jumlah produk</span>
                                    <span class="font-black text-slate-900">{{ $keranjangs->count() }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-500">Total unit</span>
                                    <span class="font-black text-slate-900">{{ $totalUnit }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                                    <span class="text-sm font-bold text-slate-500">Total</span>
                                    <span class="text-2xl font-black text-red-600">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="mt-5 {{ $negotiationEligible ? 'mini-note ok' : 'mini-note wait' }}">
                                @if($negotiationEligible)
                                    Harga usulan tersedia karena total pembelian sudah minimal 10 unit.
                                @else
                                    Harga usulan aktif jika total pembelian minimal 10 unit. Tambah {{ $remainingToNego }} unit lagi.
                                @endif
                            </div>

                            <p class="mt-4 text-xs font-medium leading-relaxed text-slate-500">
                                Saat lanjut, barang di keranjang ini otomatis masuk ke halaman pemesanan.
                            </p>

                            <a href="{{ route('order.create') }}" class="btn-primary-cart mt-5">
                                Lanjut ke Pemesanan
                            </a>

                            <a href="{{ route('produk.index') }}" class="btn-secondary-cart mt-3">
                                Tambah Produk
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
