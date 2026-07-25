<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data Layanan</p>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Jasa &amp; Service</h2>
                <p class="text-base font-semibold leading-7 text-slate-500">Kelola daftar jenis service dan layanan jasa APAR yang ditawarkan perusahaan.</p>
            </div>
            <a href="{{ route('admin.service-paket.create') }}" class="px-7 py-3.5 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-lg shadow-red-700/20 flex items-center gap-2 uppercase tracking-widest text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                Tambah Jasa / Service
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Total Jasa &amp; Service</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $servicePakets->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Layanan Dengan Sparepart</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $servicePakets->filter(fn ($s) => $s->peralatans->isNotEmpty())->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Layanan Non-Sparepart</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $servicePakets->filter(fn ($s) => $s->peralatans->isEmpty())->count() }}</p>
            </div>
        </div>

        <!-- Search & Filter Card -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('admin.jasa.index') }}" class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center flex-1">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama jasa / rincian..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 transition outline-none" />
                    </div>

                    @if(request('search'))
                        <a href="{{ route('admin.jasa.index') }}" class="px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 rounded-2xl transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Reset
                        </a>
                    @endif
                </div>

                <button type="submit" class="px-6 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition text-xs tracking-wider uppercase">
                    Cari
                </button>
            </form>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Daftar Layanan Jasa &amp; Jenis Service</h3>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Harga standar dan peralatan di bawah akan dipakai saat transaksi service memiliki pembayaran yang valid.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Nama Service / Jasa</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Harga Acuan Terbaru</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Peralatan Digunakan</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($servicePakets as $servicePaket)
                            <tr class="align-top transition hover:bg-slate-50/80">
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ $servicePaket->nama }}</p>
                                    @if(!empty($servicePaket->rincian_layanan))
                                        <p class="mt-1 text-[13px] font-semibold text-slate-500">
                                            {{ $servicePaket->rincian_layanan }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-[13px] font-semibold text-slate-500">
                                            {{ $servicePaket->services_count }} transaksi service terhubung
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">Rp {{ number_format((float) $servicePaket->harga, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-[13px] font-semibold text-slate-500">Per unit pekerjaan</p>
                                </td>
                                <td class="px-6 py-5">
                                    @if($servicePaket->peralatans->isEmpty())
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-[13px] font-semibold text-slate-500">
                                            Tanpa Peralatan (Tarif Jasa)
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($servicePaket->peralatans as $peralatan)
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-[13px] font-semibold text-slate-700">
                                                    {{ $peralatan->nama }} x{{ (int) ($peralatan->pivot->jumlah_estimasi ?? 0) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-emerald-700">Aktif</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.service-paket.edit', $servicePaket) }}" class="rounded-xl border border-slate-200 px-3.5 py-2 text-[11px] font-black uppercase tracking-widest text-slate-600 transition hover:bg-slate-100">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.service-paket.destroy', $servicePaket) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus {{ $servicePaket->nama }}?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl border border-red-200 px-3.5 py-2 text-[11px] font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">Belum ada data layanan jasa atau service.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
