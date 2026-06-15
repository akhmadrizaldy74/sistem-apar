<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <a href="{{ route('admin.pelanggan.index') }}" class="mt-1 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-500 shadow-sm transition hover:border-red-200 hover:text-red-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-3xl font-black tracking-tight text-gray-900">Detail Pelanggan</h2>
                    <p class="text-sm font-medium text-gray-500">Ringkasan alamat dan riwayat pembelian produk pelanggan.</p>
                </div>
            </div>
            <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}" class="inline-flex items-center justify-center rounded-2xl bg-red-700 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800">
                Edit
            </a>
        </div>
    </x-slot>

    @php
        $alamatLengkap = trim((string) ($pelanggan->alamat ?: $pelanggan->alamat_maps ?: $pelanggan->alamat_detail ?: '-'));
        $totalTransaksi = $riwayatPembelian->count();
        $totalBelanja = $riwayatPembelian->sum(fn ($pesanan) => (float) ($pesanan->total_harga ?: $pesanan->total ?: 0));
    @endphp

    <div class="space-y-8">
        <section class="rounded-[2rem] border border-white/60 bg-white/80 p-6 shadow-xl shadow-slate-200/40 backdrop-blur-md sm:p-8">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $pelanggan->nama }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Email</p>
                    <p class="mt-2 text-sm font-bold text-slate-700">{{ $pelanggan->user?->email ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP</p>
                    <p class="mt-2 text-sm font-bold text-slate-700">{{ $pelanggan->no_wa ?: '-' }}</p>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">{{ $alamatLengkap }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ number_format($totalTransaksi) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Nilai Pembelian</p>
                    <p class="mt-2 text-lg font-black text-emerald-700">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/40 backdrop-blur-md">
            <div class="border-b border-gray-100/70 px-6 py-5 sm:px-8">
                <h3 class="text-xl font-black text-slate-900">Riwayat Pembelian Produk</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Pesanan produk yang tidak dibatalkan milik pelanggan ini.</p>
            </div>

            @if($riwayatPembelian->isNotEmpty())
                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-gray-100/70 bg-slate-50/80">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Produk</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Total</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/70">
                            @foreach($riwayatPembelian as $pesanan)
                                @php
                                    $totalJumlah = (int) $pesanan->details->sum('jumlah');
                                @endphp
                                <tr>
                                    <td class="px-8 py-6 text-sm font-semibold text-slate-600">{{ $pesanan->displayTransactionDateTime('d M Y') }}</td>
                                    <td class="px-8 py-6">
                                        <div class="space-y-2">
                                            @foreach($pesanan->details as $detail)
                                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                                    <p class="text-sm font-black text-slate-900">{{ $detail->produk?->nama ?: 'Produk APAR' }}</p>
                                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ (int) $detail->jumlah }} unit</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-sm font-black text-slate-900">{{ number_format($totalJumlah) }}</td>
                                    <td class="px-8 py-6 text-sm font-black text-emerald-700">Rp {{ number_format((float) ($pesanan->total_harga ?: $pesanan->total ?: 0), 0, ',', '.') }}</td>
                                    <td class="px-8 py-6">
                                        <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $pesanan->publicStatusClasses() }}">
                                            {{ $pesanan->publicStatusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100/80 lg:hidden">
                    @foreach($riwayatPembelian as $pesanan)
                        @php
                            $totalJumlah = (int) $pesanan->details->sum('jumlah');
                        @endphp
                        <article class="space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ $pesanan->displayTransactionDateTime('d M Y') }}</p>
                                </div>
                                <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $pesanan->publicStatusClasses() }}">
                                    {{ $pesanan->publicStatusLabel() }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                @foreach($pesanan->details as $detail)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                        <p class="text-sm font-black text-slate-900">{{ $detail->produk?->nama ?: 'Produk APAR' }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ (int) $detail->jumlah }} unit</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ number_format($totalJumlah) }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total</p>
                                    <p class="mt-1 text-sm font-black text-emerald-700">Rp {{ number_format((float) ($pesanan->total_harga ?: $pesanan->total ?: 0), 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="px-8 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-black text-slate-900">Belum ada riwayat pembelian.</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">Pesanan produk pelanggan akan muncul di halaman ini setelah transaksi dibuat.</p>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
