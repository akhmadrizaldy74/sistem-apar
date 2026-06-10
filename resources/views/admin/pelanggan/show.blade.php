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
                    <p class="text-sm font-medium text-gray-500">Informasi pelanggan dan riwayat pembelian produk.</p>
                </div>
            </div>
            <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-100 bg-white px-6 py-3.5 text-xs font-black uppercase tracking-widest text-blue-700 shadow-sm transition hover:bg-blue-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Edit Data Dasar
            </a>
        </div>
    </x-slot>

    @php
        $alamatLengkap = trim((string) ($pelanggan->alamat ?: $pelanggan->alamat_maps ?: $pelanggan->alamat_detail ?: '-'));
        $waDigits = preg_replace('/\D+/', '', (string) $pelanggan->no_wa);
        $waUrl = $waDigits !== '' ? 'https://wa.me/' . preg_replace('/^0/', '62', $waDigits) : null;
        $totalTransaksi = $riwayatPembelian->count();
        $totalBelanja = $riwayatPembelian->sum(fn ($pesanan) => (float) ($pesanan->total_harga ?: $pesanan->total ?: 0));
    @endphp

    <div class="space-y-8">
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <section class="overflow-hidden rounded-[2.5rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-8 py-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-center gap-5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-red-500 to-red-700 text-2xl font-black uppercase text-white shadow-lg shadow-red-500/30">
                                {{ strtoupper(substr($pelanggan->nama, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-3xl font-black tracking-tight text-white">{{ $pelanggan->nama }}</h3>
                                <p class="mt-1 text-sm font-medium text-white/60">Data pelanggan terdaftar pada {{ $pelanggan->created_at?->format('d M Y') ?: '-' }}</p>
                            </div>
                        </div>
                        <span class="inline-flex w-fit rounded-2xl border border-red-400/30 bg-red-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-red-200">
                            Pelanggan
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-8 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Email</p>
                        <p class="mt-2 text-sm font-bold text-slate-700">{{ $pelanggan->user?->email ?: 'Belum ada email' }}</p>
                    </div>
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP</p>
                        @if($pelanggan->no_wa)
                            @if($waUrl)
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-2 text-sm font-bold text-blue-600 transition hover:text-blue-800 hover:underline">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                    {{ $pelanggan->no_wa }}
                                </a>
                            @else
                                <p class="mt-2 text-sm font-bold text-slate-700">{{ $pelanggan->no_wa }}</p>
                            @endif
                        @else
                            <p class="mt-2 text-sm font-semibold text-slate-400">Belum ada nomor</p>
                        @endif
                    </div>
                    <div class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6 md:col-span-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat Lengkap</p>
                        <p class="mt-2 text-sm font-semibold leading-relaxed text-slate-700">{{ $alamatLengkap }}</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-1">
                <div class="rounded-[2rem] border border-white/60 bg-white/80 p-7 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi</p>
                    <p class="mt-3 text-4xl font-black text-slate-900">{{ number_format($totalTransaksi) }}</p>
                    <p class="mt-2 text-xs font-semibold text-slate-500">Jumlah riwayat pembelian produk pelanggan.</p>
                </div>
                <div class="rounded-[2rem] border border-white/60 bg-white/80 p-7 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Nilai Pembelian</p>
                    <p class="mt-3 text-3xl font-black text-emerald-700">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-semibold text-slate-500">Akumulasi total pembayaran dari riwayat pembelian produk.</p>
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-[2.5rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/50 backdrop-blur-md">
            <div class="flex items-center justify-between border-b border-gray-100/70 px-8 py-6">
                <div>
                    <h3 class="text-xl font-black text-slate-900">Riwayat Pembelian</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Hanya menampilkan pesanan produk pelanggan.</p>
                </div>
                <span class="inline-flex rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-blue-700">
                    {{ number_format($totalTransaksi) }} Transaksi
                </span>
            </div>

            @if($riwayatPembelian->isNotEmpty())
                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-full text-left">
                        <thead class="border-b border-gray-100/70 bg-slate-50/80">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Pesanan</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Produk</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pembayaran</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status Pesanan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/70">
                            @foreach($riwayatPembelian as $pesanan)
                                @php
                                    $totalJumlah = (int) $pesanan->details->sum('jumlah');
                                    $statusLabel = $pesanan->publicStatusLabel();
                                    $statusClasses = $pesanan->publicStatusClasses();
                                @endphp
                                <tr class="transition-colors hover:bg-red-50/30">
                                    <td class="px-8 py-6 text-sm font-semibold text-slate-600">
                                        {{ $pesanan->displayTransactionDateTime('d M Y') }}
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="space-y-2">
                                            @forelse($pesanan->details as $detail)
                                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                                    <p class="text-sm font-black text-slate-900">
                                                        {{ $detail->produk?->nama ?: trim(collect([$detail->merek, $detail->kapasitas])->filter()->implode(' ')) ?: 'Produk APAR' }}
                                                    </p>
                                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                                        {{ (int) $detail->jumlah }} unit
                                                    </p>
                                                </div>
                                            @empty
                                                <p class="text-sm font-semibold text-slate-500">Produk tidak tersedia.</p>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-sm font-black text-slate-900">
                                        {{ number_format($totalJumlah) }}
                                    </td>
                                    <td class="px-8 py-6 text-sm font-black text-emerald-700">
                                        Rp {{ number_format((float) ($pesanan->total_harga ?: $pesanan->total ?: 0), 0, ',', '.') }}
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $statusClasses }}">
                                            {{ $statusLabel }}
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
                            $statusLabel = $pesanan->publicStatusLabel();
                            $statusClasses = $pesanan->publicStatusClasses();
                        @endphp
                        <article class="space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Pesanan</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ $pesanan->displayTransactionDateTime('d M Y') }}</p>
                                </div>
                                <span class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                @forelse($pesanan->details as $detail)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                        <p class="text-sm font-black text-slate-900">
                                            {{ $detail->produk?->nama ?: trim(collect([$detail->merek, $detail->kapasitas])->filter()->implode(' ')) ?: 'Produk APAR' }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ (int) $detail->jumlah }} unit</p>
                                    </div>
                                @empty
                                    <p class="text-sm font-semibold text-slate-500">Produk tidak tersedia.</p>
                                @endforelse
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">{{ number_format($totalJumlah) }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pembayaran</p>
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
