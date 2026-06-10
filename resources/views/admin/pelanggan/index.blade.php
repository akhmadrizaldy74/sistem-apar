<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-gray-900">Pelanggan</h2>
                <p class="text-sm font-medium text-gray-500">Daftar pelanggan yang terdaftar dan riwayat pembelian pelanggan.</p>
            </div>
            <a href="{{ route('admin.pelanggan.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-red-700 to-red-800 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/30 transition hover:from-red-800 hover:to-red-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pelanggan
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pelanggan</p>
                        <p class="text-5xl font-black text-slate-900">{{ number_format($summary['totalPelanggan']) }}</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white shadow-lg shadow-red-500/30">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan Aktif</p>
                        <p class="text-5xl font-black text-emerald-700">{{ number_format($summary['pelangganAktif']) }}</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-500">Pelanggan aktif dihitung dari pelanggan yang sudah memiliki riwayat pembelian produk.</p>
            </div>

            <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi Pelanggan</p>
                        <p class="text-5xl font-black text-blue-700">{{ number_format($summary['totalTransaksiPelanggan']) }}</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-lg shadow-blue-500/30">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-500">Total transaksi hanya menghitung riwayat pembelian produk pelanggan.</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-[2.5rem] border border-white/60 bg-white/80 shadow-xl shadow-slate-200/50 backdrop-blur-md">
            <form method="GET" class="flex flex-col gap-3 border-b border-gray-100/70 bg-slate-50/60 p-6 sm:flex-row">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, WhatsApp, atau alamat pelanggan..." class="w-full rounded-2xl border border-gray-200 bg-white py-3.5 pl-11 pr-5 text-sm font-medium shadow-sm transition focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white transition hover:bg-black">
                        Cari
                    </button>
                    @if($search !== '')
                        <a href="{{ route('admin.pelanggan.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-red-100 bg-white px-6 py-3.5 text-xs font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left">
                    <thead class="border-b border-gray-100/70 bg-slate-50/80">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Email</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">WhatsApp / HP</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/70">
                        @forelse($pelanggans as $pelanggan)
                            @php
                                $alamatRingkas = trim((string) ($pelanggan->alamat ?: $pelanggan->alamat_maps ?: $pelanggan->alamat_detail ?: '-'));
                                $waDigits = preg_replace('/\D+/', '', (string) $pelanggan->no_wa);
                                $waUrl = $waDigits !== '' ? 'https://wa.me/' . preg_replace('/^0/', '62', $waDigits) : null;
                            @endphp
                            <tr class="transition-colors hover:bg-red-50/30">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-sm font-black uppercase text-white shadow-lg shadow-red-500/20">
                                            {{ strtoupper(substr($pelanggan->nama, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900">{{ $pelanggan->nama }}</p>
                                            <p class="mt-1 text-xs font-medium text-slate-400">{{ $pelanggan->created_at?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($pelanggan->user?->email)
                                        <p class="text-sm font-bold text-slate-700">{{ $pelanggan->user->email }}</p>
                                    @else
                                        <p class="text-sm font-semibold text-slate-400">Belum ada email</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @if($pelanggan->no_wa)
                                        @if($waUrl)
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 transition hover:text-blue-800 hover:underline">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                                </svg>
                                                {{ $pelanggan->no_wa }}
                                            </a>
                                        @else
                                            <p class="text-sm font-bold text-slate-700">{{ $pelanggan->no_wa }}</p>
                                        @endif
                                    @else
                                        <p class="text-sm font-semibold text-slate-400">Belum ada nomor</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <p class="max-w-sm text-sm font-semibold leading-relaxed text-slate-600">{{ $alamatRingkas }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex rounded-xl border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-black uppercase tracking-widest text-blue-700">
                                        {{ number_format($pelanggan->product_orders_count) }} Transaksi
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 text-[10px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100">
                                            Detail
                                        </a>
                                        <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-blue-100 bg-white px-4 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-50">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <div class="mx-auto max-w-md">
                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="mt-5 text-lg font-black text-slate-900">Belum ada data pelanggan</h3>
                                        <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">Data pelanggan yang sesuai pencarian belum ditemukan. Coba ubah kata kunci atau tambahkan pelanggan baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-gray-100/80 lg:hidden">
                @forelse($pelanggans as $pelanggan)
                    @php
                        $alamatRingkas = trim((string) ($pelanggan->alamat ?: $pelanggan->alamat_maps ?: $pelanggan->alamat_detail ?: '-'));
                        $waDigits = preg_replace('/\D+/', '', (string) $pelanggan->no_wa);
                        $waUrl = $waDigits !== '' ? 'https://wa.me/' . preg_replace('/^0/', '62', $waDigits) : null;
                    @endphp
                    <article class="space-y-4 p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-sm font-black uppercase text-white shadow-lg shadow-red-500/20">
                                {{ strtoupper(substr($pelanggan->nama, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-base font-black text-slate-900">{{ $pelanggan->nama }}</h3>
                                <p class="mt-1 text-sm {{ $pelanggan->user?->email ? 'font-bold text-slate-600' : 'font-semibold text-slate-400' }}">
                                    {{ $pelanggan->user?->email ?: 'Belum ada email' }}
                                </p>
                                @if($pelanggan->no_wa)
                                    @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-2 text-sm font-bold text-blue-600">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                            </svg>
                                            {{ $pelanggan->no_wa }}
                                        </a>
                                    @else
                                        <p class="mt-1 text-sm font-bold text-slate-600">{{ $pelanggan->no_wa }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                            <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-700">{{ $alamatRingkas }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-xl border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-blue-700">
                                {{ number_format($pelanggan->product_orders_count) }} Transaksi
                            </span>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.pelanggan.show', $pelanggan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 text-[10px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100">
                                    Detail
                                </a>
                                <a href="{{ route('admin.pelanggan.edit', $pelanggan) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-blue-100 bg-white px-4 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-50">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">Belum ada data pelanggan</h3>
                        <p class="mt-2 text-sm font-medium leading-relaxed text-slate-500">Data pelanggan yang sesuai pencarian belum ditemukan. Coba ubah kata kunci atau tambahkan pelanggan baru.</p>
                    </div>
                @endforelse
            </div>

            @if($pelanggans->hasPages())
                <div class="border-t border-gray-100/70 px-6 py-4">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
