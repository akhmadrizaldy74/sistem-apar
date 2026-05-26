@extends('layouts.public')

@section('title', 'Status & Riwayat APAR - PD. Anugrah Utama')

@section('content')
    @php
        $riwayatPesanan = $pelanggan
            ? $pelanggan->pesanan->sortByDesc(fn ($pesanan) => $pesanan->created_at ?? $pesanan->tanggal)->values()
            : collect();
        $unitAktif = $pelanggan ? $pelanggan->units->filter(fn ($unit) => $unit->tgl_expired && !$unit->tgl_expired->isPast())->count() : 0;
        $statusBerjalan = $riwayatPesanan->filter(fn ($pesanan) => !$pesanan->isCompleted() && $pesanan->status !== \App\Models\Pesanan::STATUS_DITOLAK)->count();
        $pengirimanAktif = $riwayatPesanan->filter(fn ($pesanan) => $pesanan->publicStatusLabel() === 'Sedang Pengiriman')->count();
    @endphp

    <section class="bg-[linear-gradient(180deg,#fffdf8_0%,#f8fafc_48%,#ffffff_100%)]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="max-w-3xl" data-reveal>
                <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.32em] mb-4">Tracking Pelanggan</p>
                <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900">Status & Riwayat APAR</h1>
                <p class="text-slate-600 font-medium leading-relaxed mt-5">
                    Cek riwayat pembelian APAR baru, refill, service, pembayaran, sampai status pengambilan atau pengiriman dalam satu halaman yang simpel.
                </p>
            </div>

            <div class="mt-10" data-reveal>
                <form action="{{ route('cek-apar.check') }}" method="POST" class="bg-white/90 backdrop-blur rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/60 p-3 flex flex-col lg:flex-row gap-3">
                    @csrf
                    <div class="flex-1 flex items-center gap-4 px-5 py-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        </div>
                        <div class="w-full">
                            <label for="no_wa" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Nomor WhatsApp Terdaftar</label>
                            <input
                                id="no_wa"
                                name="no_wa"
                                value="{{ old('no_wa') }}"
                                placeholder="Contoh: 0812xxxxxxxx"
                                class="w-full border-none focus:ring-0 text-lg font-black text-slate-900 placeholder:text-slate-300 p-0 mt-1"
                                inputmode="numeric"
                                autocomplete="tel"
                                required
                            >
                            @error('no_wa')
                                <p class="text-xs font-semibold text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="px-10 py-5 rounded-[1.4rem] bg-red-700 text-white font-black uppercase tracking-widest text-sm hover:bg-red-800 transition shadow-xl shadow-red-700/20">
                        Lihat Status
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="mt-8 rounded-[1.6rem] border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm font-semibold text-emerald-800" data-reveal>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mt-8 rounded-[1.6rem] border border-red-200 bg-red-50 px-6 py-5 text-sm font-semibold text-red-700" data-reveal>
                    {{ session('error') }}
                </div>
            @endif

            @if($pelanggan)
                <div class="mt-10 grid grid-cols-1 xl:grid-cols-4 gap-6" data-reveal>
                    <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900">{{ $pelanggan->nama }}</h2>
                        <p class="mt-2 text-sm font-semibold text-slate-500">{{ $pelanggan->no_wa }}</p>
                        @if($pelanggan->alamat)
                            <p class="mt-4 text-sm font-medium text-slate-600 leading-relaxed">{{ $pelanggan->alamat }}</p>
                        @endif
                    </div>
                    <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Transaksi</p>
                        <p class="mt-3 text-4xl font-black text-slate-900">{{ $riwayatPesanan->count() }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Pembelian baru, service, dan refill.</p>
                    </div>
                    <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Berjalan</p>
                        <p class="mt-3 text-4xl font-black text-blue-700">{{ $statusBerjalan }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Transaksi yang masih diproses.</p>
                    </div>
                    <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit APAR Aktif</p>
                        <p class="mt-3 text-4xl font-black text-emerald-700">{{ $unitAktif }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-500">{{ $pengirimanAktif }} transaksi sedang pengiriman.</p>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 xl:grid-cols-[1.45fr_0.95fr] gap-8" data-reveal>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Riwayat Transaksi</p>
                                <h3 class="mt-2 text-2xl font-black text-slate-900">Pembelian, Refill, dan Service</h3>
                            </div>
                        </div>

                        @forelse($riwayatPesanan as $pesanan)
                            <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest">
                                                {{ $pesanan->trackingTypeLabel() }}
                                            </span>
                                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $pesanan->publicStatusClasses() }}">
                                                {{ $pesanan->publicStatusLabel() }}
                                            </span>
                                        </div>
                                        <h4 class="mt-4 text-xl font-black text-slate-900">{{ $pesanan->trackingItemLabel() }}</h4>
                                        <p class="mt-2 text-sm font-semibold text-slate-500">
                                            {{ $pesanan->transactionDisplayName() }} • {{ $pesanan->displayTransactionDateTime() }}
                                        </p>
                                    </div>
                                    <div class="text-left lg:text-right">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total / Estimasi</p>
                                        <p class="mt-2 text-2xl font-black text-red-700">Rp {{ number_format((float) ($pesanan->total_harga ?: $pesanan->total), 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pembayaran</p>
                                        <p class="mt-2 text-sm font-black text-slate-900">{{ $pesanan->isPaymentConfirmed() ? 'Sudah Bayar' : 'Belum Bayar' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Metode</p>
                                        <p class="mt-2 text-sm font-black text-slate-900">{{ $pesanan->trackingMethodLabel() }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Jumlah Unit</p>
                                        <p class="mt-2 text-sm font-black text-slate-900">
                                            {{ $pesanan->tipe === 'service' ? ((int) ($pesanan->service_jumlah_unit ?? 0)).' unit' : $pesanan->details->sum('jumlah').' unit' }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail</p>
                                        <p class="mt-2 text-sm font-black text-slate-900">
                                            @if($pesanan->tipe === 'service')
                                                {{ $pesanan->service_ukuran_apar ?: '-' }}
                                            @else
                                                {{ $pesanan->details->count() }} item
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if($pesanan->tipe === 'service')
                                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400">Catatan Layanan</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-600 leading-relaxed">{{ $pesanan->service_keluhan ?: ($pesanan->keterangan ?: '-') }}</p>
                                        @if($pesanan->service_total_kg)
                                            <p class="mt-3 text-sm font-bold text-slate-700">Kebutuhan refill: {{ rtrim(rtrim(number_format((float) $pesanan->service_total_kg, 2, ',', '.'), '0'), ',') }} Kg</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-10 text-center text-slate-500 font-semibold">
                                Belum ada transaksi yang tercatat untuk nomor ini.
                            </div>
                        @endforelse
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Unit APAR</p>
                            <h3 class="mt-2 text-2xl font-black text-slate-900">Monitoring Unit</h3>
                            <p class="mt-3 text-sm font-medium text-slate-600 leading-relaxed">
                                Bagian ini khusus untuk melihat unit APAR yang sudah aktif dan masa berlaku proteksinya.
                            </p>
                        </div>

                        @forelse($pelanggan->units->sortByDesc('tgl_beli') as $unit)
                            <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit APAR</p>
                                        <h4 class="mt-2 text-lg font-black text-slate-900">{{ $unit->produk?->nama ?? 'APAR' }}</h4>
                                        <p class="mt-2 text-sm font-semibold text-slate-500">
                                            {{ $unit->produk?->jenisApar?->nama ?? 'APAR' }} • {{ $unit->produk?->kapasitas ?? ($unit->ukuran ?? '-') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $unit->tgl_expired && $unit->tgl_expired->isPast() ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                        {{ $unit->tgl_expired && $unit->tgl_expired->isPast() ? 'Expired' : 'Aktif' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-5">
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No. Seri</p>
                                        <p class="mt-2 text-sm font-black text-slate-900">{{ $unit->no_seri }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expired</p>
                                        <p class="mt-2 text-sm font-black {{ $unit->tgl_expired && $unit->tgl_expired->isPast() ? 'text-red-700' : 'text-slate-900' }}">
                                            {{ optional($unit->tgl_expired)->format('d M Y') ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-[1.8rem] border border-slate-100 shadow-lg shadow-slate-200/50 p-8 text-sm font-semibold text-slate-500 leading-relaxed">
                                Unit APAR belum muncul di akun ini. Jika Anda baru membeli APAR, data unit akan tampil setelah proses administrasi selesai.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
