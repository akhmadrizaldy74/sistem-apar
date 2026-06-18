<x-app-layout>
    <x-slot name="header">
        <div class="space-y-2">
            <h2 class="text-[24px] font-black tracking-tight text-slate-900">Master Service &amp; Peralatan</h2>
            <p class="text-sm font-medium text-slate-500">Kelola jenis service final, harga standar, peralatan service, dan relasinya dalam satu halaman Master Data.</p>
        </div>
    </x-slot>

    @php
        $selectedTab = in_array(($activeTab ?? 'jenis-service'), ['jenis-service', 'peralatan-service'], true)
            ? $activeTab
            : 'jenis-service';
    @endphp

    <div x-data="{ tab: @js($selectedTab) }" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Jenis Service Aktif</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $servicePakets->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Peralatan Service</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $peralatans->count() }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Relasi Peralatan</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $servicePakets->sum(fn ($servicePaket) => $servicePaket->peralatans->count()) }}</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Master Data Final</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">Gunakan tab di bawah untuk mengelola jenis service dan peralatan service tanpa berpindah halaman.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="tab = 'jenis-service'"
                        :class="tab === 'jenis-service' ? 'bg-red-700 text-white shadow-lg shadow-red-700/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-2xl px-5 py-3 text-xs font-black uppercase tracking-widest transition"
                    >
                        Jenis Service
                    </button>
                    <button
                        type="button"
                        @click="tab = 'peralatan-service'"
                        :class="tab === 'peralatan-service' ? 'bg-red-700 text-white shadow-lg shadow-red-700/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-2xl px-5 py-3 text-xs font-black uppercase tracking-widest transition"
                    >
                        Peralatan Service
                    </button>
                </div>
            </div>

            <div x-show="tab === 'jenis-service'" x-cloak class="space-y-5 px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Jenis Service</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Harga standar dan peralatan di bawah akan dipakai saat transaksi service mencapai status selesai final.</p>
                    </div>
                    <a href="{{ route('admin.service-paket.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-red-700 px-5 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-red-800">
                        Tambah Jenis Service
                    </a>
                </div>

                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Nama Service</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Harga Standar</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Peralatan Digunakan</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($servicePakets as $servicePaket)
                                    <tr class="align-top transition hover:bg-slate-50/80">
                                        <td class="px-5 py-5">
                                            <p class="text-sm font-black text-slate-900">{{ $servicePaket->nama }}</p>
                                            <p class="mt-1 text-[13px] font-semibold text-slate-500">
                                                {{ $servicePaket->services_count }} transaksi service terhubung
                                            </p>
                                        </td>
                                        <td class="px-5 py-5">
                                            <p class="text-sm font-black text-slate-900">Rp {{ number_format((float) $servicePaket->harga, 0, ',', '.') }}</p>
                                            <p class="mt-1 text-[13px] font-semibold text-slate-500">Per unit pekerjaan</p>
                                        </td>
                                        <td class="px-5 py-5">
                                            @if($servicePaket->peralatans->isEmpty())
                                                <p class="text-[13px] font-semibold text-slate-400">Belum ada peralatan terhubung.</p>
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
                                        <td class="px-5 py-5">
                                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-emerald-700">Aktif</span>
                                        </td>
                                        <td class="px-5 py-5">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.service-paket.edit', $servicePaket) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-600 transition hover:bg-slate-100">
                                                    Edit
                                                </a>
                                                <form action="{{ route('admin.service-paket.destroy', $servicePaket) }}" method="POST" data-confirm="Hapus jenis service ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-xl border border-red-200 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-red-600 transition hover:bg-red-50">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-12 text-center text-sm font-semibold text-slate-400">Belum ada jenis service aktif.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'peralatan-service'" x-cloak class="space-y-5 px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Peralatan Service</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Stok di bawah dipakai saat finalisasi service dan tidak akan dikurangi dua kali.</p>
                    </div>
                    <a href="{{ route('admin.peralatan.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-red-700 px-5 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-red-800">
                        Tambah Peralatan
                    </a>
                </div>

                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Nama Peralatan</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Stok</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Satuan</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Harga Standar</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Status</th>
                                    <th class="px-5 py-4 text-[11px] font-black uppercase tracking-widest text-slate-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($peralatans as $peralatan)
                                    <tr class="transition hover:bg-slate-50/80">
                                        <td class="px-5 py-5">
                                            <p class="text-sm font-black text-slate-900">{{ $peralatan->nama }}</p>
                                        </td>
                                        <td class="px-5 py-5">
                                            <p class="text-sm font-black text-slate-900">{{ number_format((int) $peralatan->stok, 0, ',', '.') }}</p>
                                        </td>
                                        <td class="px-5 py-5">
                                            <p class="text-[13px] font-semibold text-slate-600">Unit</p>
                                        </td>
                                        <td class="px-5 py-5">
                                            <p class="text-sm font-black text-slate-900">Rp {{ number_format((float) $peralatan->harga_standar, 0, ',', '.') }}</p>
                                        </td>
                                        <td class="px-5 py-5">
                                            <span class="inline-flex rounded-full px-3 py-1.5 text-[11px] font-black uppercase tracking-widest {{ $peralatan->is_stok_rendah ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                                {{ $peralatan->is_stok_rendah ? 'Stok Rendah' : 'Aktif' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-5">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.peralatan.edit', $peralatan) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-600 transition hover:bg-slate-100">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-12 text-center text-sm font-semibold text-slate-400">Belum ada peralatan service aktif.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
