<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Unit APAR</h2>
                <p class="mt-2 text-sm font-medium text-slate-500">Kelola dan pantau unit APAR pelanggan yang terhubung dengan transaksi pembelian, refill, dan service.</p>
            </div>
        </div>
    </x-slot>

    <div
        class="space-y-8"
        x-data="unitAparIndex({
            units: @js($unitItems),
            summary: @js($summary),
            unitBaseUrl: @js(url('/admin/unit-apar')),
        })"
    >
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Total Unit Terdaftar</p>
                <p class="mt-4 text-4xl font-black text-slate-900" x-text="summary.total"></p>
            </div>

            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-emerald-600">Status Aktif</p>
                <p class="mt-4 text-4xl font-black text-emerald-700" x-text="summary.aktif"></p>
            </div>

            <div class="rounded-3xl border border-amber-100 bg-amber-50/70 p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-amber-600">Hampir Habis</p>
                <p class="mt-4 text-4xl font-black text-amber-700" x-text="summary.hampir"></p>
                <p class="mt-2 text-xs font-semibold text-amber-600">Maks. 30 hari menuju expired</p>
            </div>

            <div class="rounded-3xl border border-red-200 bg-red-50 p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-widest text-red-500">Masa Berlaku Habis</p>
                <p class="mt-4 text-4xl font-black text-red-700" x-text="summary.expired"></p>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 p-6 sm:p-8">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,0.9fr)]">
                    <div>
                        <label for="unit-apar-search" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Cari Unit</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                id="unit-apar-search"
                                type="text"
                                x-model="search"
                                placeholder="Cari nama pelanggan atau nomor unit"
                                class="w-full border-none bg-transparent text-sm font-medium text-slate-700 placeholder:text-slate-300 focus:ring-0"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="unit-apar-pelanggan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Filter Pelanggan</label>
                        <select
                            id="unit-apar-pelanggan"
                            x-model="selectedPelanggan"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                        >
                            <option value="all">Semua Pelanggan</option>
                            @foreach ($pelanggans as $pelanggan)
                                <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="unit-apar-status" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Filter Status</label>
                        <select
                            id="unit-apar-status"
                            x-model="selectedStatus"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-bold text-slate-700 shadow-sm focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                        >
                            <option value="semua">Semua</option>
                            <option value="hampir">Hampir Expired</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>

                <p class="mt-4 text-sm font-semibold text-slate-500">
                    Menampilkan
                    <span class="font-black text-slate-800" x-text="filteredUnits().length"></span>
                    unit APAR.
                </p>
                <p class="mt-2 text-sm font-medium text-slate-500">
                    Data pelanggan pada filter mengikuti menu Pelanggan, sehingga hanya akun pelanggan yang benar-benar terdaftar yang ditampilkan.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Unit Info / Nomor Unit</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Produk</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Tanggal Dasar / Expired</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 sm:px-8">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <template x-if="filteredUnits().length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm font-semibold text-slate-500 sm:px-8">
                                    Belum ada unit APAR yang cocok dengan pencarian atau filter ini.
                                </td>
                            </tr>
                        </template>

                        <template x-for="unit in filteredUnits()" :key="unit.id">
                            <tr class="transition hover:bg-red-50/30">
                                <td class="px-6 py-5 align-top sm:px-8">
                                    <p class="text-sm font-black text-slate-900" x-text="unit.no_seri"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500" x-text="unit.ukuran + ' / ' + unit.bahan"></p>
                                </td>

                                <td class="px-6 py-5 align-top sm:px-8">
                                    <p class="text-sm font-bold text-slate-800" x-text="unit.pelanggan_nama"></p>
                                </td>

                                <td class="px-6 py-5 align-top sm:px-8">
                                    <p class="text-sm font-semibold text-slate-700" x-text="unit.produk_nama"></p>
                                </td>

                                <td class="px-6 py-5 align-top sm:px-8">
                                    <p class="text-sm font-bold text-slate-800" x-text="unit.tgl_dasar_label"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500" x-text="'Expired: ' + unit.tgl_expired_label"></p>
                                </td>

                                <td class="px-6 py-5 align-top sm:px-8">
                                    <span
                                        class="inline-flex rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest"
                                        :class="statusBadgeClass(unit.status)"
                                        x-text="unit.status_label"
                                    ></span>
                                </td>

                                <td class="px-6 py-5 align-top sm:px-8">
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            :href="detailUrl(unit.id)"
                                            class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            Lihat Detail
                                        </a>

                                        <form
                                            :action="destroyUrl(unit.id)"
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
                                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-[11px] font-black uppercase tracking-widest text-red-700 transition hover:bg-red-100"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @once
        <script>
            function unitAparIndex(config) {
                return {
                    units: config.units ?? [],
                    summary: config.summary ?? { total: 0, aktif: 0, hampir: 0, expired: 0 },
                    unitBaseUrl: config.unitBaseUrl ?? '',
                    search: '',
                    selectedPelanggan: 'all',
                    selectedStatus: 'semua',
                    normalize(value) {
                        return String(value ?? '').toLowerCase().trim();
                    },
                    filteredUnits() {
                        return this.units.filter((unit) => {
                            const keyword = this.normalize(this.search);
                            const matchesSearch = keyword === '' || this.normalize(unit.search_text).includes(keyword);
                            const matchesPelanggan = this.selectedPelanggan === 'all' || String(unit.pelanggan_id) === String(this.selectedPelanggan);
                            const matchesStatus = this.selectedStatus === 'semua' || unit.status === this.selectedStatus;

                            return matchesSearch && matchesPelanggan && matchesStatus;
                        });
                    },
                    statusBadgeClass(status) {
                        if (status === 'expired') {
                            return 'border border-red-200 bg-red-50 text-red-700';
                        }

                        if (status === 'hampir') {
                            return 'border border-amber-200 bg-amber-50 text-amber-700';
                        }

                        return 'border border-emerald-200 bg-emerald-50 text-emerald-700';
                    },
                    detailUrl(id) {
                        return `${this.unitBaseUrl}/${id}`;
                    },
                    destroyUrl(id) {
                        return `${this.unitBaseUrl}/${id}`;
                    },
                };
            }
        </script>
    @endonce
</x-app-layout>
