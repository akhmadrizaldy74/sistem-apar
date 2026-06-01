@extends('layouts.public')

@section('title', 'Keranjang Belanja - PD. Anugrah Utama')

@section('styles')
<style>
    .cart-page { background: linear-gradient(180deg, #fff 0%, #f8fafc 100%); }
    .cart-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1.5rem; box-shadow: 0 10px 30px rgba(15,23,42,.03); overflow: hidden; }
    .cart-item { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 1.5rem; padding: 1.25rem; transition: all .2s ease; }
    .cart-item:hover { border-color: #fca5a5; box-shadow: 0 12px 24px rgba(220,38,38,.03); }
    .cart-thumb { width: 5rem; height: 5rem; border-radius: 1.25rem; overflow: hidden; background: #f8fafc; border: 1px solid #f1f5f9; flex-shrink: 0; }
    .cart-thumb img { width: 100%; height: 100%; object-fit: contain; padding: 0.25rem; }
    .qty-btn { width: 2.25rem; height: 2.25rem; border-radius: .85rem; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #334155; display: flex; align-items: center; justify-content: center; transition: all .2s ease; cursor: pointer; }
    .qty-btn:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }
    .qty-btn:disabled { opacity: .4; cursor: not-allowed; }
    .qty-box { min-width: 2.5rem; height: 2.25rem; border-radius: .85rem; border: 1.5px solid #e2e8f0; background: #fff; display: flex; align-items: center; justify-content: center; font-size: .875rem; font-weight: 800; color: #0f172a; transition: opacity 0.15s ease; }
    
    .btn-primary-cart { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .95rem 1rem; border-radius: 1.25rem; background: #dc2626; color: #fff; font-size: .82rem; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; transition: all .2s ease; box-shadow: 0 4px 12px rgba(220,38,38,0.15); border: none; }
    .btn-primary-cart:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(220,38,38,0.25); }
    
    .btn-secondary-cart { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .9rem 1rem; border-radius: 1.25rem; border: 1.5px solid #cbd5e1; background: #fff; color: #334155; font-size: .8rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; transition: all .2s ease; }
    .btn-secondary-cart:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
    
    .mini-note { border-radius: 1.25rem; padding: 1rem; font-size: .78rem; font-weight: 700; line-height: 1.5; }
    .mini-note.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
    .mini-note.wait { background: #fffbeb; border: 1px solid #fde68a; color: #d97706; }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fadeIn 0.3s ease forwards; }
</style>
@endsection

@section('content')


    <section class="cart-page min-h-screen py-10 sm:py-14">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-red-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke Katalog
            </a>

            {{-- Title Group --}}
            <div class="mt-6 pb-6 border-b border-slate-100 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900">Keranjang Belanja</h1>
                    <p class="mt-1 text-sm font-medium text-slate-500">Kumpulkan item APAR pilihan Anda untuk diproses sekaligus.</p>
                </div>
            </div>

            @if($keranjangs->isEmpty())
                <div class="cart-card mt-8 px-8 py-16 text-center border border-slate-200">
                    <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900">Keranjang masih kosong.</h2>
                    <p class="mt-3 text-sm font-medium text-slate-500 max-w-md mx-auto">Silakan pilih produk dari katalog untuk menambahkannya ke keranjang belanja Anda.</p>
                    <a href="{{ route('produk.index') }}" class="btn-primary-cart mt-8 inline-flex w-auto px-8 py-3.5">
                        Lihat Katalog Produk
                    </a>
                </div>
            @else
                <div class="mt-8 grid gap-8 lg:grid-cols-3 items-start">
                    {{-- Left Column: Cart Items --}}
                    <div class="space-y-4 lg:col-span-2">
                        @foreach($keranjangs as $item)
                            <div class="cart-item flex flex-col sm:flex-row gap-5 items-center justify-between" id="cart-item-{{ $item->id }}" data-id="{{ $item->id }}" data-price="{{ $item->harga }}" data-stock="{{ $item->produk->stok_tersedia }}">
                                {{-- Left Group: Thumbnail and details --}}
                                <div class="flex items-center gap-4 w-full sm:flex-1 min-w-0">
                                    <div class="cart-thumb">
                                        @if($item->produk->resolved_image_url)
                                            <img src="{{ $item->produk->resolved_image_url }}" alt="{{ $item->produk->nama }}">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-slate-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-red-50 text-[10px] font-black text-red-600 uppercase tracking-widest">{{ $item->produk->jenisApar?->nama ?? 'APAR' }}</span>
                                        <h3 class="text-base font-black text-slate-900 truncate mt-1">{{ $item->produk->nama }}</h3>
                                        <p class="text-xs font-semibold text-slate-500 mt-0.5">
                                            Merek: {{ $item->produk->merek ?? 'FIREFIX' }} @if($item->produk->kapasitas) • {{ $item->produk->kapasitas }} @endif
                                        </p>
                                        <p class="text-xs font-bold text-slate-400 mt-1">Harga Satuan: Rp {{ number_format($item->harga, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                {{-- Right Group: Controls & Subtotal --}}
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 pt-4 sm:pt-0 border-slate-100 flex-shrink-0">
                                    {{-- Quantity controls --}}
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="qty-btn btn-decrease" data-id="{{ $item->id }}" {{ $item->qty <= 1 ? 'disabled' : '' }}>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="qty-box" id="qty-val-{{ $item->id }}">{{ $item->qty }}</span>
                                        <button type="button" class="qty-btn btn-increase" data-id="{{ $item->id }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>

                                    {{-- Subtotal and Delete button --}}
                                    <div class="text-right flex items-center gap-4">
                                        <div class="min-w-[6.5rem]">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Subtotal</p>
                                            <p class="text-base font-black text-red-600 mt-1 item-subtotal" id="subtotal-val-{{ $item->id }}">Rp {{ number_format($item->harga * $item->qty, 0, ',', '.') }}</p>
                                        </div>
                                        <form action="{{ route('keranjang.destroy', $item->id) }}" method="POST" data-confirm="Hapus item ini dari keranjang?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="qty-btn text-red-600 hover:bg-red-50 flex items-center justify-center" title="Hapus Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Right Column: Summary Panel --}}
                    <div class="lg:col-span-1">
                        <div class="cart-card p-6 border border-slate-200 lg:sticky lg:top-24">
                            <h2 class="text-lg font-black text-slate-900 pb-4 border-b border-slate-100">Ringkasan Pesanan</h2>

                            <div class="mt-4 space-y-3.5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-500">Jumlah produk</span>
                                    <span class="font-black text-slate-800" id="summary-total-products">{{ $keranjangs->count() }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-500">Total unit</span>
                                    <span class="font-black text-slate-800" id="summary-total-unit">{{ $totalUnit }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm border-t border-slate-100 pt-3">
                                    <span class="font-semibold text-slate-500">Subtotal</span>
                                    <span class="font-black text-slate-800" id="summary-subtotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-500">Diskon Aktif (<span id="summary-diskon-persen">{{ $diskonPersen }}</span>%)</span>
                                    <span class="font-black text-emerald-600" id="summary-diskon-nominal">- Rp {{ number_format($nominalDiskon, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-100 pt-4 mt-2">
                                    <span class="text-sm font-black text-slate-500">Total Belanja</span>
                                    <span class="text-2xl font-black text-red-600" id="summary-total-akhir">Rp {{ number_format($totalAkhir, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="mt-5 {{ $diskonPersen > 0 ? 'mini-note ok' : 'mini-note wait' }}" id="promo-warning-box">
                                @if($diskonPersen > 0)
                                    Selamat, Anda mendapatkan diskon {{ $diskonPersen }}%.
                                @else
                                    Tambah {{ 5 - $totalUnit }} unit lagi untuk mendapatkan diskon 5%.
                                @endif
                            </div>

                            <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-400">
                                Barang di keranjang ini akan otomatis ditarik ke dalam formulir pemesanan online.
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

    {{-- Script for Optimistic UI and AJAX quantity sync --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = '{{ csrf_token() }}';
            const updateUrlPattern = "{{ route('keranjang.update', ':id') }}";
            function showError(message) {
                showAppAlert(message, 'error', 'Gagal');
            }

            function hideError() {
                return true;
            }

            async function updateQty(itemId, newQty, btnElement) {
                hideError();
                
                const itemElement = document.getElementById(`cart-item-${itemId}`);
                if (!itemElement) return;

                const unitPrice = parseFloat(itemElement.getAttribute('data-price'));
                const maxStock = parseInt(itemElement.getAttribute('data-stock'));
                
                const qtyBox = document.getElementById(`qty-val-${itemId}`);
                const subtotalBox = document.getElementById(`subtotal-val-${itemId}`);
                const decBtn = itemElement.querySelector('.btn-decrease');
                const incBtn = itemElement.querySelector('.btn-increase');
                
                const oldQty = parseInt(qtyBox.textContent);
                
                if (newQty < 1) return;
                if (newQty > maxStock) {
                    showError(`Stok tidak mencukupi. Tersedia: ${maxStock} unit.`);
                    return;
                }

                // 1. OPTIMISTIC UI UPDATE
                qtyBox.textContent = newQty;
                const newSubtotal = unitPrice * newQty;
                subtotalBox.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newSubtotal);
                decBtn.disabled = (newQty <= 1);
                
                // Recalculate and update the grand totals
                const allItems = Array.from(document.querySelectorAll('.cart-item'));
                let totalUnit = 0;
                let subtotal = 0;
                
                allItems.forEach(el => {
                    const elId = el.getAttribute('data-id');
                    const elPrice = parseFloat(el.getAttribute('data-price'));
                    const elQty = (elId === String(itemId)) ? newQty : parseInt(document.getElementById(`qty-val-${elId}`).textContent);
                    totalUnit += elQty;
                    subtotal += (elPrice * elQty);
                });
                
                let diskonPersen = 0;
                if (totalUnit >= 50) diskonPersen = 25;
                else if (totalUnit >= 35) diskonPersen = 20;
                else if (totalUnit >= 20) diskonPersen = 15;
                else if (totalUnit >= 10) diskonPersen = 10;
                else if (totalUnit >= 5) diskonPersen = 5;

                const nominalDiskon = subtotal * (diskonPersen / 100);
                const totalAkhir = subtotal - nominalDiskon;

                document.getElementById('summary-total-unit').textContent = totalUnit;
                document.getElementById('summary-subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
                document.getElementById('summary-diskon-persen').textContent = diskonPersen;
                document.getElementById('summary-diskon-nominal').textContent = '- Rp ' + new Intl.NumberFormat('id-ID').format(nominalDiskon);
                document.getElementById('summary-total-akhir').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalAkhir);
                
                // Update promo note
                const promoWarning = document.getElementById('promo-warning-box');
                if (promoWarning) {
                    if (diskonPersen > 0) {
                        promoWarning.className = 'mini-note ok mt-5';
                        promoWarning.textContent = `Selamat, Anda mendapatkan diskon ${diskonPersen}%.`;
                    } else {
                        promoWarning.className = 'mini-note wait mt-5';
                        const remaining = 5 - totalUnit;
                        promoWarning.textContent = `Tambah ${remaining} unit lagi untuk mendapatkan diskon 5%.`;
                    }
                }

                // 2. BACKEND SYNC IN BACKGROUND
                const url = updateUrlPattern.replace(':id', itemId);
                qtyBox.classList.add('opacity-50');
                
                try {
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ qty: newQty })
                    });
                    
                    const data = await response.json();
                    qtyBox.classList.remove('opacity-50');
                    
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Gagal mengubah qty.');
                    }
                    
                    // Keep DOM perfectly synced with absolute data returned from backend
                    qtyBox.textContent = data.item_qty;
                    subtotalBox.textContent = data.item_subtotal_formatted;
                    document.getElementById('summary-total-unit').textContent = data.cart_count;
                    document.getElementById('summary-subtotal').textContent = data.subtotal_formatted;
                    document.getElementById('summary-diskon-persen').textContent = data.diskon_persen;
                    document.getElementById('summary-diskon-nominal').textContent = data.nominal_diskon_formatted;
                    document.getElementById('summary-total-akhir').textContent = data.total_akhir_formatted;
                    
                    // Update badge counts in navbar dynamically (desktop and mobile)
                    const desktopBadge = document.querySelector('header a[href*="keranjang"] span');
                    if (desktopBadge) {
                        if (data.cart_count > 0) {
                            desktopBadge.textContent = data.cart_count > 99 ? '99+' : data.cart_count;
                            desktopBadge.classList.remove('hidden');
                        } else {
                            desktopBadge.classList.add('hidden');
                        }
                    }
                    
                    const mobileBadge = document.querySelector('.md\\:hidden a[href*="keranjang"] span');
                    if (mobileBadge) {
                        if (data.cart_count > 0) {
                            mobileBadge.textContent = data.cart_count > 99 ? '99+' : data.cart_count;
                            mobileBadge.classList.remove('hidden');
                        } else {
                            mobileBadge.classList.add('hidden');
                        }
                    }
                    
                } catch (err) {
                    // REVERT OPTIMISTIC UI back to old values if request fails
                    qtyBox.classList.remove('opacity-50');
                    qtyBox.textContent = oldQty;
                    const oldSubtotal = unitPrice * oldQty;
                    subtotalBox.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(oldSubtotal);
                    decBtn.disabled = (oldQty <= 1);
                    
                    // Re-calculate totals back
                    let revertUnit = 0;
                    let revertSubtotal = 0;
                    allItems.forEach(el => {
                        const elId = el.getAttribute('data-id');
                        const elPrice = parseFloat(el.getAttribute('data-price'));
                        const elQty = parseInt(document.getElementById(`qty-val-${elId}`).textContent);
                        revertUnit += elQty;
                        revertSubtotal += (elPrice * elQty);
                    });
                    
                    let revertDiskon = 0;
                    if (revertUnit >= 50) revertDiskon = 25;
                    else if (revertUnit >= 35) revertDiskon = 20;
                    else if (revertUnit >= 20) revertDiskon = 15;
                    else if (revertUnit >= 10) revertDiskon = 10;
                    else if (revertUnit >= 5) revertDiskon = 5;

                    const revertNominal = revertSubtotal * (revertDiskon / 100);
                    const revertTotal = revertSubtotal - revertNominal;

                    document.getElementById('summary-total-unit').textContent = revertUnit;
                    document.getElementById('summary-subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(revertSubtotal);
                    document.getElementById('summary-diskon-persen').textContent = revertDiskon;
                    document.getElementById('summary-diskon-nominal').textContent = '- Rp ' + new Intl.NumberFormat('id-ID').format(revertNominal);
                    document.getElementById('summary-total-akhir').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(revertTotal);
                    
                    showError(err.message || 'Gagal mengubah qty. Silakan coba lagi.');
                }
            }

            // Click Handlers
            document.querySelectorAll('.btn-decrease').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.getAttribute('data-id');
                    const qtyBox = document.getElementById(`qty-val-${itemId}`);
                    const currentQty = parseInt(qtyBox.textContent);
                    updateQty(itemId, currentQty - 1, this);
                });
            });

            document.querySelectorAll('.btn-increase').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.getAttribute('data-id');
                    const qtyBox = document.getElementById(`qty-val-${itemId}`);
                    const currentQty = parseInt(qtyBox.textContent);
                    updateQty(itemId, currentQty + 1, this);
                });
            });
        });
    </script>
@endsection
