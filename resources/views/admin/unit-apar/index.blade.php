<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Unit APAR</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Daftar unit dibuat lebih ringkas supaya admin cepat mencari, menyaring, dan membuka detail tanpa scroll panjang.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Total Unit APAR</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $summary['total'] }}</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-600">Unit Aman</p>
                <p class="mt-2 text-2xl font-black text-emerald-700">{{ $summary['aktif'] }}</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-amber-50/90 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-amber-600">Unit Hampir Expired</p>
                <p class="mt-2 text-2xl font-black text-amber-700">{{ $summary['hampir'] }}</p>
            </div>

            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-red-500">Unit Expired</p>
                <p class="mt-2 text-2xl font-black text-red-700">{{ $summary['expired'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm" x-data="{ dateMode: '{{ $filters['tanggal_mode'] }}' }">
            <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Filter Unit APAR</h3>
                        <p class="text-sm font-medium text-slate-500">Susunan filter dipadatkan supaya nyaman dipakai di Chrome laptop 14".</p>
                    </div>

                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan
                        <span class="font-black text-slate-900">{{ $units->count() }}</span>
                        dari
                        <span class="font-black text-slate-900">{{ $filteredUnitCount }}</span>
                        unit
                        @if ($filteredUnitCount !== $visibleUnitCount)
                            <span class="text-slate-400">dari total {{ $visibleUnitCount }} unit</span>
                        @endif
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.unit-apar.index') }}" class="mt-4 space-y-3">
                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.9fr)_10.5rem_13rem]">
                        <div>
                            <label for="search" class="mb-1.5 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Cari</label>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm">
                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value="{{ $filters['search'] }}"
                                    placeholder="Nomor unit, pelanggan, produk"
                                    class="w-full border-none bg-transparent px-0 py-0 text-sm font-medium text-slate-700 placeholder:text-slate-300 focus:ring-0"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="status" class="mb-1.5 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Status</label>
                            <select
                                id="status"
                                name="status"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                            >
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="produk_id" class="mb-1.5 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Produk</label>
                            <select
                                id="produk_id"
                                name="produk_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                            >
                                <option value="">Semua Produk</option>
                                @foreach ($produks as $produk)
                                    <option value="{{ $produk->id }}" @selected((string) $filters['produk_id'] === (string) $produk->id)>{{ $produk->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[8.5rem_minmax(0,1.45fr)_auto]">
                        <div>
                            <label for="per_page" class="mb-1.5 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Per Hal.</label>
                            <select
                                id="per_page"
                                name="per_page"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                            >
                                @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Tanggal</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[9.5rem_minmax(0,1fr)_minmax(0,1fr)]">
                                <select
                                    name="tanggal_mode"
                                    x-model="dateMode"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                    <option value="all">Semua Tanggal</option>
                                    <option value="single">Per Tanggal</option>
                                    <option value="range">Rentang Tanggal</option>
                                </select>

                                <div x-show="dateMode === 'single'" x-cloak class="sm:col-span-2">
                                    <input
                                        id="tanggal"
                                        name="tanggal"
                                        type="date"
                                        value="{{ $filters['tanggal'] }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                    >
                                </div>

                                <div x-show="dateMode === 'range'" x-cloak>
                                    <input
                                        id="tanggal_mulai"
                                        name="tanggal_mulai"
                                        type="date"
                                        value="{{ $filters['tanggal_mulai'] }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                    >
                                </div>

                                <div x-show="dateMode === 'range'" x-cloak>
                                    <input
                                        id="tanggal_selesai"
                                        name="tanggal_selesai"
                                        type="date"
                                        value="{{ $filters['tanggal_selesai'] }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 self-end sm:flex-row">
                            <a
                                href="{{ route('admin.unit-apar.index') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                            >
                                Reset
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-[11px] font-black uppercase tracking-[0.18em] text-white transition hover:bg-slate-800"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>

                @if ($activeFilters !== [])
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($activeFilters as $filter)
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm">
                                <span class="font-black text-slate-700">{{ $filter['label'] }}:</span>
                                <span class="ml-1">{{ $filter['value'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="p-4 sm:p-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed text-left">
                        <colgroup>
                            <col class="w-[18%]">
                            <col class="w-[16%]">
                            <col class="w-[24%]">
                            <col class="w-[11%]">
                            <col class="w-[11%]">
                            <col class="w-[9%]">
                            <col class="w-[11%]">
                        </colgroup>

                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Nomor Unit</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Pelanggan</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Produk</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Tanggal Masuk</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Masa Berlaku Sampai</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Status</th>
                                <th class="px-3.5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-400">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($units as $unit)
                                <tr class="transition hover:bg-red-50/20">
                                    <td class="px-3.5 py-3.5 align-top">
                                        <p class="break-words text-sm font-black leading-5 text-slate-900">{{ $unit['no_seri'] }}</p>
                                        <p class="mt-1 text-xs font-semibold leading-4 text-slate-500">{{ $unit['ukuran'] }} / {{ $unit['bahan'] }}</p>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <p class="break-words text-sm font-bold leading-5 text-slate-800">{{ $unit['pelanggan_nama'] }}</p>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <p class="break-words text-sm font-bold leading-5 text-slate-800">{{ $unit['produk_nama'] }}</p>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <p class="whitespace-nowrap text-sm font-semibold text-slate-700">{{ $unit['tgl_masuk_label'] }}</p>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <p class="whitespace-nowrap text-sm font-black {{ $unit['expired_text_class'] }}">{{ $unit['tgl_expired_label'] }}</p>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <span class="inline-flex whitespace-nowrap rounded-xl px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.16em] {{ $unit['status_badge_class'] }}">
                                            {{ $unit['status_label'] }}
                                        </span>
                                    </td>

                                    <td class="px-3.5 py-3.5 align-top">
                                        <div class="flex flex-wrap gap-1.5">
                                            <a
                                                href="{{ route('admin.unit-apar.show', $unit['id']) }}"
                                                class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-700 transition hover:bg-emerald-100"
                                            >
                                                Lihat Detail
                                            </a>

                                            <form
                                                action="{{ route('admin.unit-apar.destroy', $unit['id']) }}"
                                                method="POST"
                                                class="inline"
                                                data-confirm="Yakin ingin menghapus unit APAR ini?"
                                                data-confirm-title="Konfirmasi Hapus"
                                                data-confirm-button="Ya, Hapus"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-red-700 transition hover:bg-red-100"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                        Belum ada unit APAR yang cocok dengan pencarian atau filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($units->hasPages())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        {{ $units->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
