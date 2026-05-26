@extends('layouts.public')

@section('title', $pesanan->invoiceTitle() . ' - PD. Anugrah Utama')

@section('content')
<div class="min-h-screen bg-slate-50/60 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <!-- Action Buttons Top -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold rounded-xl shadow-sm transition text-xs uppercase tracking-wider">
                <i class="fa-solid fa-arrow-left text-slate-400"></i>
                Kembali
            </a>
            
            <a href="{{ route('invoice.pdf', $pesanan) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md shadow-red-600/10 transition text-xs uppercase tracking-wider">
                <i class="fa-solid fa-file-pdf"></i>
                Cetak / Unduh PDF
            </a>
        </div>

        <!-- Invoice Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden relative">
            <!-- Brand top accent line -->
            <div class="h-2 bg-red-600 w-full"></div>

            <!-- Invoice Header -->
            <div class="p-8 sm:p-12 border-b border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo-anugrah.png') }}" alt="Logo PD. Anugrah Utama" class="h-14 w-14 rounded-2xl object-cover ring-2 ring-slate-100">
                            <div>
                                <h1 class="text-xl font-black text-slate-900 tracking-tight">PD. ANUGRAH UTAMA</h1>
                                <p class="text-xs font-black text-red-600 uppercase tracking-widest mt-0.5">Sistem Proteksi Kebakaran & APAR</p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-500 max-w-sm">
                            Kawasan Ruko Sentra Niaga, Jl. Utama Raya Blok B No. 12,<br>
                            Telp/WhatsApp: 0821-2471-6109
                        </p>
                    </div>

                    <div class="text-left md:text-right md:self-stretch flex flex-col justify-between items-start md:items-end">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Dokumen Transaksi</span>
                            <h2 class="mt-1 text-3xl font-black text-slate-900">{{ $pesanan->invoiceTitle() }}</h2>
                            <p class="mt-2 text-sm font-semibold text-slate-500">Tanggal Transaksi: {{ $pesanan->displayTransactionDateTime() }}</p>
                        </div>
                        
                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            @if($isLunas)
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-black bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    LUNAS / PAID
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-black bg-amber-50 border border-amber-200 text-amber-700 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    INVOICE SEMENTARA
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Info -->
            <div class="p-8 sm:p-12 bg-slate-50/50 border-b border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Client Details -->
                    <div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Ditagihkan Kepada</h3>
                        <p class="text-base font-black text-slate-900">{{ $pesanan->pelanggan->nama }}</p>
                        @if($pesanan->pelanggan->perusahaan)
                            <p class="text-xs font-bold text-slate-600 mt-0.5">{{ $pesanan->pelanggan->perusahaan }}</p>
                        @endif
                        <p class="text-xs font-semibold text-slate-500 mt-2">
                            <i class="fa-solid fa-phone text-slate-400 mr-1.5"></i>
                            {{ $pesanan->pelanggan->no_wa }}
                        </p>
                        @if($pesanan->alamat_pengiriman || $pesanan->pelanggan->alamat)
                            <p class="text-xs font-semibold leading-relaxed text-slate-500 mt-2 max-w-sm">
                                <i class="fa-solid fa-location-dot text-slate-400 mr-2"></i>
                                {{ $pesanan->alamat_pengiriman ?: $pesanan->pelanggan->alamat }}
                            </p>
                        @endif
                    </div>

                    <!-- Meta details -->
                    <div class="md:text-right">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Rincian Transaksi</h3>
                        
                        <dl class="space-y-2 text-xs">
                            <div class="flex md:justify-end gap-4">
                                <dt class="font-bold text-slate-400">Metode Pemesanan:</dt>
                                <dd class="font-black text-slate-800 uppercase">{{ $pesanan->trackingMethodLabel() }}</dd>
                            </div>
                            <div class="flex md:justify-end gap-4">
                                <dt class="font-bold text-slate-400">Metode Pembayaran:</dt>
                                <dd class="font-black text-slate-800 uppercase">{{ $pesanan->getPaymentMethodLabel() }}</dd>
                            </div>
                            <div class="flex md:justify-end gap-4">
                                <dt class="font-bold text-slate-400">Status Pembayaran:</dt>
                                <dd class="font-black {{ $isLunas ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $isLunas ? 'Lunas / Paid' : 'Belum Lunas' }}
                                </dd>
                            </div>
                            <div class="flex md:justify-end gap-4">
                                <dt class="font-bold text-slate-400">Status Transaksi:</dt>
                                <dd class="font-black text-slate-800 uppercase">{{ $pesanan->publicStatusLabel() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="p-8 sm:p-12">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Rincian Item & Biaya</h3>

                @if($pesanan->isProductOrder())
                    <!-- PRODUK APAR TABLE -->
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Nama Produk</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-center">Spesifikasi</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-center">Jumlah</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-right">Harga Satuan</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($pesanan->details as $detail)
                                    <tr class="hover:bg-slate-50/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-black text-slate-900">{{ $detail->produk?->nama ?? 'Produk APAR' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-block px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-slate-100 rounded text-slate-700">
                                                {{ $detail->merek ?: '-' }} - {{ $detail->kapasitas ?: '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-slate-800">
                                            {{ $detail->jumlah }} unit
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-800">
                                            Rp {{ number_format($detail->harga, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-slate-900">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @elseif($pesanan->isRefillOrder())
                    <!-- REFILL APAR TABLE -->
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Jenis Layanan</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-center">Spesifikasi APAR</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-center">Jumlah Unit</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-right">Biaya Refill</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-black text-slate-900">Refill / Pengisian Ulang APAR</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Jenis Refill: {{ $pesanan->serviceJenisRefill?->nama ?? $pesanan->service_jenis_apar ?? 'Dry Chemical Powder' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-slate-100 rounded text-slate-700">
                                            {{ $pesanan->service_jenis_apar ?: '-' }} ({{ $pesanan->service_ukuran_apar ?: '-' }})
                                        </span>
                                        @if($pesanan->service?->unitApar?->no_seri)
                                            <p class="text-[10px] font-bold text-slate-500 mt-1.5">No. Seri: <span class="font-black text-slate-900">{{ $pesanan->service->unitApar->no_seri }}</span></p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-800">
                                        {{ (int) ($pesanan->service_jumlah_unit ?: 1) }} unit
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900">
                                        Rp {{ number_format($pesanan->payableTotal(), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                @elseif($pesanan->isServiceOrder())
                    @php
                        $serviceLines = $pesanan->servicePricingBreakdown();
                        $servicePeralatan = $pesanan->servicePeralatanItems();
                    @endphp
                    <!-- SERVICE APAR TABLE -->
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Paket Service</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Rincian Unit</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Peralatan Paket</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-right">Biaya Service</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-black text-slate-900">{{ $pesanan->servicePaket?->nama ?? 'Paket Service APAR' }}</p>
                                        @if($pesanan->servicePaket?->label)
                                            <p class="text-xs text-slate-400 mt-1">{{ $pesanan->servicePaket->label }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-2">
                                            @foreach($serviceLines as $line)
                                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
                                                    <p class="font-semibold text-slate-800">{{ $line['label'] }}</p>
                                                    <p class="text-xs font-bold text-slate-500 mt-1">{{ (int) ($line['qty'] ?? 1) }} unit • Rp {{ number_format((float) ($line['total'] ?? 0), 0, ',', '.') }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-2">
                                            @forelse($servicePeralatan as $item)
                                                <p class="text-sm font-semibold text-slate-700">{{ $item['nama'] ?? '-' }} <span class="text-slate-400">x{{ (int) ($item['jumlah'] ?? 0) }}</span></p>
                                            @empty
                                                <p class="text-sm font-semibold text-slate-500">Tidak ada peralatan terhubung.</p>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900">
                                        Rp {{ number_format($pesanan->payableTotal(), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Total section card -->
                <div class="mt-8 flex flex-col md:flex-row justify-between items-start md:items-center p-6 bg-slate-50 rounded-2xl border border-slate-100 gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pembayaran</p>
                        <p class="text-3xl font-black text-slate-900 mt-1">Rp {{ number_format($pesanan->payableTotal(), 0, ',', '.') }}</p>
                    </div>

                    @if($isLunas)
                        <div class="flex items-center gap-2.5 px-5 py-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                            <div>
                                <p class="text-xs font-black text-emerald-800 uppercase tracking-wider">Status Pembayaran</p>
                                <p class="text-[11px] font-semibold text-emerald-600">Terbayar Lunas</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2.5 px-5 py-3 bg-amber-50 border border-amber-100 rounded-xl">
                            <i class="fa-solid fa-clock text-amber-600 text-lg animate-pulse"></i>
                            <div>
                                <p class="text-xs font-black text-amber-800 uppercase tracking-wider">Status Pembayaran</p>
                                <p class="text-[11px] font-semibold text-amber-600">Menunggu Pembayaran</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Service Reports if Completed -->
                @if(in_array($pesanan->tipe, ['refill', 'service'], true) && $pesanan->isCompleted())
                    <div class="mt-10 p-6 bg-white border border-slate-200 rounded-2xl">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Laporan Pengerjaan Teknisi</h4>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 font-bold shrink-0">
                                    <i class="fa-solid fa-user-gear"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $pesanan->teknisi?->name ?? 'Teknisi Handal' }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">Petugas Teknisi APAR</p>
                                </div>
                            </div>
                            
                            @if($pesanan->teknisi_catatan)
                                <blockquote class="border-l-4 border-slate-200 pl-4 py-1.5 text-sm text-slate-600 italic">
                                    "{{ $pesanan->teknisi_catatan }}"
                                </blockquote>
                            @else
                                <p class="text-sm text-slate-500 font-semibold italic">Layanan refill/service telah selesai dikerjakan dengan standar keselamatan PD. Anugrah Utama.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer Invoice decorative stamp -->
            <div class="p-8 sm:p-12 bg-slate-50/30 border-t border-slate-50 text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Terima kasih atas kepercayaan Anda</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-1.5">Invoice ini dicetak secara otomatis dan sah sebagai bukti transaksi.</p>
                <p class="mt-2 text-[10px] font-medium text-slate-300">Nomor referensi internal: {{ $pesanan->invoiceDisplayNumber() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
