<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data Layanan</p>
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Peralatan</h2>
                <p class="text-base font-semibold leading-7 text-slate-500">Kelola daftar peralatan yang digunakan dalam layanan APAR.</p>
            </div>
            <a href="{{ route('admin.peralatan.create') }}" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Peralatan
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Total Peralatan</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $peralatans->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Stok Rendah</p>
                <p class="mt-3 text-3xl font-black text-amber-600">{{ $peralatans->filter(fn ($p) => $p->is_stok_rendah)->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Stok Kosong</p>
                <p class="mt-3 text-3xl font-black text-red-600">{{ $peralatans->filter(fn ($p) => (int) $p->stok === 0)->count() }}</p>
            </div>
        </div>

        <!-- Table Container Peralatan -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Daftar Peralatan Service</h3>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Stok di bawah dipakai saat transaksi service diproses dan dapat ditambahkan via menu Pembelian.</p>
                </div>
                <a href="{{ route('admin.peralatan.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-red-700 px-5 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-red-800 shrink-0">
                    Tambah Peralatan
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Nama Peralatan</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Stok</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Satuan</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Harga Standar</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400 text-center">Status</th>
                            <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400 text-right pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($peralatans as $peralatan)
                            <tr class="transition hover:bg-slate-50/50">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ $peralatan->nama }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ number_format((int) $peralatan->stok, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-[13px] font-semibold text-slate-600">Unit</p>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-slate-900">Rp {{ number_format((float) $peralatan->harga_standar, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if($peralatan->stok == 0)
                                        <span class="inline-flex rounded-full px-4 py-1.5 text-[11px] font-black uppercase tracking-widest bg-red-50 text-red-700 border border-red-200">
                                            Kosong
                                        </span>
                                    @elseif($peralatan->is_stok_rendah)
                                        <span class="inline-flex rounded-full px-4 py-1.5 text-[11px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200">
                                            Stok Rendah
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-4 py-1.5 text-[11px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right pr-8">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.peralatan.edit', $peralatan) }}" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 hover:text-slate-900 transition" title="Edit Peralatan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <form action="{{ route('admin.peralatan.destroy', $peralatan) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus peralatan {{ $peralatan->nama }}?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition" title="Hapus Peralatan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-slate-400">Belum ada peralatan service aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
