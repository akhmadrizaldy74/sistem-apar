@extends('layouts.public')

@section('title', 'Pembayaran Pesanan - PD. Anugrah Utama')

@push('styles')
<style>
    #public-nav { display: none !important; }
    main { padding-top: 0 !important; }
</style>
@endpush

@section('content')
@php
    $pricingSummary = $pesanan->pricingSummary();
    $totalBayar = (float) $pricingSummary['totalPembayaran'];
    $ongkir = (float) $pricingSummary['ongkir'];
    $subtotalProduk = (float) $pricingSummary['subtotalProduk'];
    $totalUnit = (int) $pricingSummary['totalUnit'];
    $diskonPersen = (int) $pricingSummary['diskonPersen'];
    $nominalDiskon = (float) $pricingSummary['nominalDiskon'];
    $totalSetelahPromo = (float) ($pricingSummary['totalSetelahPromo'] ?? max(0, $subtotalProduk - $nominalDiskon));
    $hargaPengajuan = (float) ($pricingSummary['hargaPengajuan'] ?? 0);
    $hargaFinal = (float) ($pricingSummary['hargaFinal'] ?? 0);
    $hasApprovedSpecialPrice = !empty($pricingSummary['specialPriceActive']);
    $metodePengiriman = $pesanan->metode_pengiriman ?: 'pickup';
    $selectedBankCode = ($pesanan->bank && isset($banks[$pesanan->bank])) ? $pesanan->bank : array_key_first($banks);
    $selectedBank = $banks[$selectedBankCode];
    $deadline = \Illuminate\Support\Carbon::parse($pesanan->created_at)->addMinutes(60);
    $orderDate = $pesanan->tanggal ?: $pesanan->created_at;
    $orderCode = 'TNTI' . optional($orderDate)->format('dmY') . 'AJ' . str_pad((string) $pesanan->id, 3, '0', STR_PAD_LEFT);
    $now = now();
    $isExpired = $deadline->lt($now);
    $isServiceOrder = $pesanan->tipe === 'service';
    $shippingLabel = $isServiceOrder
        ? ($pesanan->service_metode_penanganan === 'antar sendiri' ? 'Antar Sendiri' : 'Dijemput')
        : ($metodePengiriman === 'diantar_internal' ? 'Diantar' : 'Ambil Sendiri');
    $pageTitle = $isServiceOrder ? 'Selesaikan Pembayaran Layanan Anda' : 'Selesaikan Pembayaran Anda';
    $pageDescription = $isServiceOrder
        ? 'Lanjutkan pembayaran refill atau service APAR, lalu sistem akan menampilkan status pengambilan atau pengerjaannya.'
        : 'Transfer sesuai nominal yang tertera, lalu upload bukti pembayaran untuk verifikasi otomatis oleh admin.';
    $badgeLabel = $isServiceOrder ? 'Pembayaran Layanan' : 'Pembayaran Pesanan';
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-red-50/20">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200/60 sticky top-0 z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="text-red-700 font-black text-sm uppercase tracking-wide">PD. ANUGRAH UTAMA</span>
                </a>
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>{{ $isServiceOrder ? 'Layanan' : 'Pembayaran' }}</span>
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-slate-700 font-black">Konfirmasi Transfer</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Title Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-50 border border-red-200 text-[11px] font-black uppercase tracking-widest text-red-600 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                {{ $badgeLabel }}
            </div>
            <h1 class="mt-4 text-3xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $pageTitle }}</h1>
            <p class="mt-3 text-base font-medium text-slate-500 max-w-2xl mx-auto leading-relaxed">
                {{ $pageDescription }}
            </p>
        </div>

        <form id="payment-form" method="POST" action="{{ route('order.payment.store', ['pesanan' => $pesanan->id]) }}" class="grid grid-cols-1 lg:grid-cols-5 gap-8 items-start">
            @csrf
            <input type="hidden" name="metode_pembayaran" value="transfer">
            <input type="hidden" name="bank" value="{{ $selectedBankCode }}">

            <!-- LEFT: Payment Card -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Main Payment Card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/60 shadow-xl shadow-slate-200/50">
                    <!-- Card Header -->
                    <div class="px-6 md:px-8 py-6 border-b border-slate-100/70 bg-white/40 backdrop-blur-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Transaksi Pembayaran</p>
                                <p class="mt-1 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">{{ $pesanan->transactionDisplayName() }}</p>
                                <p class="mt-2 text-xs font-semibold text-slate-400">{{ $pesanan->displayTransactionDateTime() }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="px-5 py-3 rounded-2xl {{ $isExpired ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }} text-center shadow-sm relative overflow-hidden">
                                    <p class="text-[10px] font-black uppercase tracking-widest {{ $isExpired ? 'text-red-500' : 'text-amber-600' }}">Sisa Waktu Pembayaran</p>
                                    <div class="flex items-center justify-center gap-1.5 mt-1">
                                        <svg class="w-4 h-4 {{ $isExpired ? 'text-red-500' : 'text-amber-600' }} animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p id="countdown-timer" class="text-xl font-black {{ $isExpired ? 'text-red-600' : 'text-amber-700' }} tracking-widest" data-deadline="{{ $deadline->timestamp }}">00:00:00</p>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1">Batas: {{ $deadline->format('H:i, d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 md:p-8 space-y-6">
                        <!-- Bank & Amount Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Bank Card -->
                            <div class="rounded-[1.75rem] bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-7 relative overflow-hidden shadow-2xl shadow-slate-900/30">
                                <!-- Decorative circles -->
                                <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/5 rounded-full"></div>
                                <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-red-600/10 rounded-full"></div>

                                <div class="relative z-10">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-white/40">Bank Tujuan Transfer</p>
                                            <h2 class="text-[26px] font-black mt-2 tracking-tight leading-tight">{{ $selectedBank['nama_bank'] }}</h2>
                                            <p class="text-sm font-semibold text-white/60 mt-2">a.n. <span class="text-white/80 font-bold">{{ $selectedBank['pemilik'] }}</span></p>
                                        </div>
                                        <span class="shrink-0 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/70 backdrop-blur-sm">Transfer</span>
                                    </div>

                                    <div class="mt-6 rounded-2xl bg-white/10 border border-white/10 px-4 sm:px-5 py-4 sm:py-5">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-white/40">Nomor Rekening Tujuan</p>
                                        <div class="mt-2 sm:mt-3 flex flex-wrap items-center justify-between gap-3">
                                            <p id="bank-rek" class="text-2xl md:text-[26px] leading-none font-black tracking-wide break-all">{{ $selectedBank['no_rekening'] }}</p>
                                            <button type="button" id="copy-rek" class="shrink-0 inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-white text-slate-900 text-xs sm:text-sm font-black hover:bg-slate-100 transition shadow-lg">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                                Salin
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Card -->
                            <div class="rounded-[1.75rem] border-2 border-red-200 bg-gradient-to-br from-red-50 via-white to-white p-7 relative overflow-hidden shadow-lg shadow-red-100/30">
                                <div class="absolute -top-12 -right-12 w-40 h-40 bg-red-100/30 rounded-full"></div>

                                <div class="relative z-10">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-red-400">Total Yang Harus Dibayar</p>
                                    <p id="nominal-bayar" class="text-3xl md:text-[36px] leading-tight font-black text-slate-900 mt-3 tracking-tight break-words">Rp {{ number_format($totalBayar, 0, ',', '.') }}</p>
                                    <p class="text-sm font-semibold text-slate-500 mt-1">{{ $hasApprovedSpecialPrice ? 'Harga Final disetujui admin' : ($diskonPersen > 0 ? 'Promo Diskon ' . $diskonPersen . '%' : 'Harga Normal') }}</p>

                                    <button type="button" id="copy-nominal" class="mt-5 w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-5 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-sm font-black transition shadow-lg shadow-red-600/25">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        Salin Nominal Transfer
                                    </button>

                                    <div class="mt-4 sm:mt-5 grid grid-cols-2 gap-2 sm:gap-3">
                                        <div class="rounded-2xl bg-white border border-red-100 p-3 sm:p-4 shadow-sm">
                                            <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider sm:tracking-widest text-slate-400">Tgl Pesanan</p>
                                            <p class="text-xs sm:text-sm font-black text-slate-900 mt-1">{{ optional($orderDate)->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-white border border-red-100 p-3 sm:p-4 shadow-sm">
                                            <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider sm:tracking-widest text-slate-400">Pengiriman</p>
                                            <p class="text-xs sm:text-sm font-black text-slate-900 mt-1">{{ $shippingLabel }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step-by-step Guide -->
                        <div class="bg-white/60 backdrop-blur-sm rounded-2xl border border-slate-100 p-6">
                            <p class="text-sm font-black text-slate-700 uppercase tracking-widest mb-5">Langkah Pembayaran</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="flex gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-lg shadow-red-500/25">1</div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Transfer Nominal</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">Gunakan nominal <strong>persis sama</strong> agar verifikasi otomatis lebih cepat.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-lg shadow-red-500/25">2</div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Simpan Bukti Transfer</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">Screenshot mobile banking, foto struk ATM, atau file PDF struk transfer.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-lg shrink-0 shadow-lg shadow-red-500/25">3</div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900">Upload Bukti Bayar</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1 leading-relaxed">Upload bukti, lalu klik Kirim. Admin akan menerima notifikasi konfirmasi Anda.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Area -->
                        <div class="rounded-[1.5rem] border-2 border-slate-200 bg-white/80 backdrop-blur-sm p-6 shadow-sm">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <div>
                                    <p class="text-base font-black text-slate-900">Upload Bukti Pembayaran</p>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Format: JPG, PNG, atau PDF. Maksimal 5MB.</p>
                                </div>
                            </div>

                            <label for="proof-input" id="upload-area" class="flex flex-col items-center justify-center gap-3 px-5 py-10 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/80 hover:border-red-400 hover:bg-red-50/30 transition-all cursor-pointer text-center group">
                                <div id="upload-icon" class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:border-red-200 group-hover:text-red-400 transition-all shadow-sm">
                                    <svg id="upload-icon-svg" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <svg id="upload-check-svg" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-700">Klik untuk pilih file bukti transfer</p>
                                    <p id="proof-filename" class="text-xs font-semibold text-slate-400 mt-1">Format: JPG, PNG, atau PDF — Maksimal 5MB</p>
                                </div>
                            </label>
                            <input type="file" name="bukti_pembayaran" id="proof-input" accept=".jpg,.jpeg,.png,.pdf" class="hidden">

                            <!-- Submit Button Moved Here for Better UX -->
                            <div class="mt-6 space-y-3 pt-6 border-t border-slate-100">
                                <button type="button" id="submit-proof-btn" class="w-full inline-flex items-center justify-center px-5 py-4 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-black transition shadow-xl shadow-red-600/30 disabled:opacity-60 disabled:cursor-not-allowed text-sm tracking-wider">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Kirim Bukti Pembayaran
                                </button>
                                <div class="flex items-center justify-center gap-1.5 text-[11px] font-semibold text-slate-400">
                                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Admin akan menerima bukti dan memproses pesanan Anda.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/60 shadow-xl shadow-slate-200/50 p-7 sticky top-28">
                    <!-- Summary Header -->
                    <div class="flex items-center gap-3 mb-6 pb-5 border-b border-slate-100/70">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-900/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-base font-black text-slate-900">Ringkasan Pesanan</p>
                            <p class="text-xs font-semibold text-slate-500 mt-0.5">Periksa kembali sebelum transfer</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    @if($isServiceOrder)
                        <div class="space-y-3 mb-6">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Detail Layanan</p>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 backdrop-blur-sm px-4 py-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656 0L12 17.2l-1.772-1.772a4 4 0 10-5.656 5.656l1.772 1.772L12 20.628l5.656-5.656a4 4 0 000-5.656z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-black text-slate-900 leading-tight">{{ $pesanan->trackingTypeLabel() }}</p>
                                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $pesanan->trackingItemLabel() }}</p>
                                        <div class="mt-3 grid grid-cols-2 gap-3 text-[11px] font-semibold text-slate-500">
                                            <p>Ukuran: {{ $pesanan->service_ukuran_apar ?: '-' }}</p>
                                            <p>Jumlah: {{ (int) ($pesanan->service_jumlah_unit ?? 0) }} unit</p>
                                            <p>Metode: {{ $pesanan->trackingMethodLabel() }}</p>
                                            <p>Kebutuhan: {{ $pesanan->service_total_kg ? rtrim(rtrim(number_format((float) $pesanan->service_total_kg, 2, ',', '.'), '0'), ',').' Kg' : '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($pesanan->details->count())
                        <div class="space-y-3 mb-6">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Item Pesanan</p>
                            @foreach($pesanan->details as $detail)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 backdrop-blur-sm px-4 py-4 flex items-start justify-between gap-3 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 leading-tight">{{ $detail->produk?->nama ?? 'Produk APAR' }}</p>
                                            <p class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                                {{ $detail->merek ?? '-' }} &bull; {{ $detail->kapasitas ?? '-' }} &bull; Qty {{ $detail->jumlah }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-black text-slate-900 whitespace-nowrap shrink-0">Rp {{ number_format((float) $detail->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Price Breakdown -->
                    <div class="rounded-2xl bg-slate-50/80 backdrop-blur-sm border border-slate-100 p-5 space-y-3 shadow-sm mb-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-500">{{ $isServiceOrder ? 'Estimasi Layanan' : 'Subtotal Produk' }}</span>
                            <span class="font-black text-slate-800">Rp {{ number_format($subtotalProduk, 0, ',', '.') }}</span>
                        </div>
                        @unless($isServiceOrder)
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Total Unit</span>
                                <span class="font-black text-slate-800">{{ $totalUnit }} unit</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Diskon Promo</span>
                                <span class="font-black {{ $diskonPersen > 0 ? 'text-emerald-700' : 'text-slate-800' }}">{{ $diskonPersen }}%</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Nominal Diskon</span>
                                <span class="font-black {{ $nominalDiskon > 0 ? 'text-emerald-700' : 'text-slate-800' }}">{{ $nominalDiskon > 0 ? '- ' : '' }}Rp {{ number_format($nominalDiskon, 0, ',', '.') }}</span>
                            </div>
                            @if($hasApprovedSpecialPrice)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-500">Total Setelah Promo</span>
                                    <span class="font-black text-slate-800">Rp {{ number_format($totalSetelahPromo, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-500">Harga Pengajuan</span>
                                    <span class="font-black text-slate-800">Rp {{ number_format($hargaPengajuan, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm">
                                    <span class="font-semibold text-emerald-800">Harga Final</span>
                                    <span class="font-black text-emerald-800">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        @endunless
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-500">{{ $metodePengiriman === 'diantar_internal' ? 'Ongkir Diantar' : 'Ongkir Ambil Sendiri' }}</span>
                            <span class="font-black text-slate-800">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-500">Bank Tujuan</span>
                            <span class="font-black text-slate-800">{{ $selectedBank['nama_bank'] }}</span>
                        </div>
                        <div class="border-t-2 border-slate-200 pt-3 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-black uppercase tracking-widest text-slate-600">Total Bayar</span>
                            <span class="text-xl sm:text-2xl font-black text-red-700">Rp {{ number_format($totalBayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Success Overlay -->
<div id="success-overlay" class="hidden fixed inset-0 z-[95] p-4 bg-slate-900/60 backdrop-blur-md flex items-center justify-center">
    <div class="w-full max-w-md rounded-[2rem] border border-emerald-100 bg-white shadow-2xl shadow-emerald-900/10 p-8 text-center">
        <!-- Animated Check -->
        <div class="mx-auto w-24 h-24 relative">
            <svg class="w-24 h-24" viewBox="0 0 96 96" fill="none">
                <circle cx="48" cy="48" r="44" fill="#d1fae5" stroke="#10b981" stroke-width="3"/>
                <path d="M30 50l14 14 22-28" stroke="#10b981" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="70" stroke-dashoffset="70" id="check-path"/>
            </svg>
        </div>

        <h4 class="mt-6 text-2xl font-black text-slate-900">Bukti Pembayaran Terkirim!</h4>
        <p class="mt-3 text-sm text-slate-500 font-medium leading-relaxed">
            Bukti pembayaran berhasil disimpan. Admin akan segera memproses pesanan Anda dan menghubungi via WhatsApp.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-black transition shadow-xl shadow-red-600/30 text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Kembali ke Beranda
            </a>
            @if(isset($data['wa_url']))
                <a href="{{ $data['wa_url'] }}" target="_blank" class="inline-flex items-center justify-center px-6 py-3.5 rounded-2xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-black transition shadow-xl shadow-[#25D366]/25 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Hubungi via WhatsApp
                </a>
            @endif
        </div>
    </div>
</div>

<style>
    #check-path {
        animation: draw-check 500ms ease-out 300ms forwards;
    }
    @keyframes draw-check {
        to { stroke-dashoffset: 0; }
    }
</style>

<script>
(function () {
    const form = document.getElementById('payment-form');
    const proofInput = document.getElementById('proof-input');
    const proofFilename = document.getElementById('proof-filename');
    const submitProofBtn = document.getElementById('submit-proof-btn');
    const successOverlay = document.getElementById('success-overlay');
    const uploadIcon = document.getElementById('upload-icon');
    const uploadIconSvg = document.getElementById('upload-icon-svg');
    const uploadCheckSvg = document.getElementById('upload-check-svg');
    const uploadArea = document.getElementById('upload-area');

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : '';
    }

    function getFreshCsrfToken() {
        return getCookie('XSRF-TOKEN')
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }

    // Countdown Timer Logic
    const countdownEl = document.getElementById('countdown-timer');
    if (countdownEl) {
        const deadlineTimestamp = parseInt(countdownEl.getAttribute('data-deadline'), 10) * 1000;
        
        function updateTimer() {
            const now = new Date().getTime();
            const distance = deadlineTimestamp - now;
            
            if (distance < 0) {
                countdownEl.textContent = "00:00:00";
                countdownEl.classList.remove('text-amber-700');
                countdownEl.classList.add('text-red-600');
                return;
            }
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdownEl.textContent = 
                String(hours).padStart(2, '0') + ":" + 
                String(minutes).padStart(2, '0') + ":" + 
                String(seconds).padStart(2, '0');
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    }

    function copyText(text) {
        navigator.clipboard.writeText(text || '').catch(() => {});
    }

    document.getElementById('copy-rek').addEventListener('click', () => copyText(document.getElementById('bank-rek').textContent.trim()));
    document.getElementById('copy-nominal').addEventListener('click', () => {
        const text = document.getElementById('nominal-bayar').textContent.trim().replace(/\s+/g, '');
        copyText(text);
    });

    proofInput.addEventListener('change', () => {
        if (proofInput.files && proofInput.files.length > 0) {
            const file = proofInput.files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            proofFilename.textContent = `${file.name} (${sizeMB}MB)`;
            proofFilename.classList.remove('text-slate-400');
            proofFilename.classList.add('text-emerald-600', 'font-black');
            uploadIcon.classList.remove('border-slate-200', 'text-slate-400', 'bg-white');
            uploadIcon.classList.add('border-emerald-300', 'text-emerald-600', 'bg-emerald-50');
            uploadIconSvg.classList.add('hidden');
            uploadCheckSvg.classList.remove('hidden');
            uploadArea.classList.remove('hover:border-red-400', 'hover:bg-red-50/30');
            uploadArea.classList.add('border-emerald-300', 'bg-emerald-50/20');
        } else {
            proofFilename.textContent = 'Format: JPG, PNG, atau PDF — Maksimal 5MB';
            proofFilename.classList.add('text-slate-400');
            proofFilename.classList.remove('text-emerald-600', 'font-black');
            uploadIcon.classList.add('border-slate-200', 'text-slate-400', 'bg-white');
            uploadIcon.classList.remove('border-emerald-300', 'text-emerald-600', 'bg-emerald-50');
            uploadIconSvg.classList.remove('hidden');
            uploadCheckSvg.classList.add('hidden');
            uploadArea.classList.add('hover:border-red-400', 'hover:bg-red-50/30');
            uploadArea.classList.remove('border-emerald-300', 'bg-emerald-50/20');
        }
    });

    submitProofBtn.addEventListener('click', async () => {
        if (!proofInput.files || !proofInput.files.length) {
            showAppAlert('Upload bukti transfer terlebih dahulu.', 'warning', 'Peringatan');
            return;
        }

        submitProofBtn.disabled = true;
        submitProofBtn.innerHTML = `<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Mengirim Bukti...`;

        try {
            const formData = new FormData(form);
            formData.set('bukti_pembayaran', proofInput.files[0]);
            formData.set('_token', getFreshCsrfToken());

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getFreshCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                credentials: 'same-origin',
            });

            if (response.status === 419) {
                throw new Error('Sesi keamanan kadaluarsa. Silakan refresh halaman lalu kirim ulang bukti pembayaran.');
            }

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal memproses konfirmasi pembayaran.');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
            successOverlay.classList.remove('hidden');
        } catch (error) {
            showAppAlert(error.message || 'Terjadi kesalahan saat mengirim bukti.', 'error', 'Gagal');
            submitProofBtn.disabled = false;
            submitProofBtn.innerHTML = `<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>Kirim Bukti Pembayaran`;
        }
    });
})();
</script>
@endsection
