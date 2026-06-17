<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tight text-slate-900">Monitoring APAR</h2>
                <p class="mt-2 text-sm font-medium text-slate-500">Pantau kelayakan dan masa berlaku unit APAR milik pelanggan.</p>
            </div>

            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-unit-modal'))"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-6 py-4 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/25 transition hover:bg-red-800"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrasi APAR
            </button>
        </div>
    </x-slot>

    @php
        $oldForm = [
            'pelanggan_id' => old('pelanggan_id', ''),
            'produk_id' => old('produk_id', ''),
            'tanggal_dasar_masa_berlaku' => old('tanggal_dasar_masa_berlaku', old('tgl_produksi', old('tgl_beli', ''))),
        ];
    @endphp

    <div
        class="space-y-8"
        x-data="unitAparIndex({
            units: @js($unitItems),
            summary: @js($summary),
            productOptions: @js($productOptions),
            unitBaseUrl: @js(url('/admin/unit-apar')),
            openModal: {{ $errors->any() ? 'true' : 'false' }},
            oldForm: @js($oldForm),
        })"
        @open-unit-modal.window="openModal = true"
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

        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="openModal = false"></div>

            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-slate-200 bg-white shadow-2xl"
            >
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white/95 px-6 py-5 backdrop-blur sm:px-8">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900">Registrasi APAR</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Daftarkan unit APAR milik pelanggan agar masa berlakunya dapat dipantau.</p>
                    </div>

                    <button
                        type="button"
                        @click="openModal = false"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 transition hover:text-red-700"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.unit-apar.store') }}" method="POST" class="p-6 sm:p-8">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-5 py-4">
                            <p class="text-sm font-black text-red-700">Registrasi belum berhasil. Periksa data berikut:</p>
                            <ul class="mt-3 space-y-1 text-sm font-semibold text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <div class="space-y-5">
                            <div>
                                <label for="pelanggan_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan <span class="text-red-500">*</span></label>
                                <select
                                    id="pelanggan_id"
                                    name="pelanggan_id"
                                    x-model="form.pelangganId"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-800 focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                    <option value="">Pilih pelanggan</option>
                                    @foreach ($pelanggans as $pelanggan)
                                        <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="produk_id" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Produk / Jenis APAR <span class="text-red-500">*</span></label>
                                <select
                                    id="produk_id"
                                    name="produk_id"
                                    x-model="form.produkId"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-800 focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                    <option value="">Pilih produk</option>
                                    @foreach ($produks as $produk)
                                        <option value="{{ $produk->id }}">{{ $produk->nama }} / {{ $produk->kapasitas ?? '-' }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('produk_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="no_seri" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor Unit</label>
                                <input
                                    id="no_seri"
                                    type="text"
                                    name="no_seri"
                                    value="{{ old('no_seri') }}"
                                    placeholder="Kosongkan jika ingin dibuat otomatis"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                <p class="mt-2 text-xs font-semibold text-slate-500">Sistem akan membuat nomor unit otomatis bila kolom ini dikosongkan.</p>
                                <x-input-error :messages="$errors->get('no_seri')" class="mt-2" />
                            </div>

                            <div>
                                <label for="tanggal_dasar_masa_berlaku" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Dasar Masa Berlaku <span class="text-red-500">*</span></label>
                                <input
                                    id="tanggal_dasar_masa_berlaku"
                                    type="date"
                                    name="tanggal_dasar_masa_berlaku"
                                    x-model="form.tanggalDasarMasaBerlaku"
                                    value="{{ old('tanggal_dasar_masa_berlaku', old('tgl_produksi', old('tgl_beli'))) }}"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-800 focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                <p class="mt-2 text-xs font-semibold text-slate-500">Tanggal ini digunakan sebagai dasar perhitungan masa berlaku APAR.</p>
                                <x-input-error :messages="$errors->get('tanggal_dasar_masa_berlaku')" class="mt-2" />
                            </div>

                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal Expired / Masa Berlaku</label>
                                <input
                                    type="text"
                                    :value="expiryPreviewLabel()"
                                    readonly
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-5 py-4 text-sm font-bold text-slate-700 focus:ring-0"
                                >
                                <p class="mt-2 text-xs font-semibold text-slate-500">Dihitung otomatis dari tanggal produksi/dasar expired. APAR 1 kg berlaku 6 bulan, ukuran 2 kg ke atas berlaku 1 tahun.</p>
                            </div>

                            <div>
                                <label for="kondisi_awal" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-400">Kondisi Awal Unit <span class="text-red-500">*</span></label>
                                <select
                                    id="kondisi_awal"
                                    name="kondisi_awal"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold text-slate-800 focus:border-red-300 focus:ring-2 focus:ring-red-600/10"
                                >
                                    <option value="layak" @selected(old('kondisi_awal', 'layak') === 'layak')>Layak</option>
                                    <option value="tidak_aktif" @selected(old('kondisi_awal') === 'tidak_aktif' || old('kondisi_awal') === 'tidak_layak')>Tidak Layak</option>
                                </select>
                                <x-input-error :messages="$errors->get('kondisi_awal')" class="mt-2" />
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Petunjuk Singkat</p>
                                    <ul class="mt-4 space-y-3 text-sm font-semibold leading-6 text-slate-600">
                                        <li>Form ini dipakai untuk mendaftarkan unit APAR pelanggan lama agar masa berlakunya bisa dipantau.</li>
                                        <li>Nomor unit boleh dikosongkan jika ingin dibuat otomatis oleh sistem.</li>
                                        <li>Expired dihitung otomatis: 1 kg = 6 bulan, 2 kg ke atas = 1 tahun.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            @click="openModal = false"
                            class="rounded-2xl px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 transition hover:text-slate-800"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="rounded-2xl bg-red-700 px-8 py-4 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-red-700/25 transition hover:bg-red-800"
                        >
                            Simpan Registrasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @once
        <script>
            function unitAparIndex(config) {
                return {
                    units: config.units ?? [],
                    summary: config.summary ?? { total: 0, aktif: 0, hampir: 0, expired: 0 },
                    productOptions: config.productOptions ?? {},
                    unitBaseUrl: config.unitBaseUrl ?? '',
                    openModal: Boolean(config.openModal),
                    search: '',
                    selectedPelanggan: 'all',
                    selectedStatus: 'semua',
                    form: {
                        pelangganId: String(config.oldForm?.pelanggan_id ?? ''),
                        produkId: String(config.oldForm?.produk_id ?? ''),
                        tanggalDasarMasaBerlaku: String(config.oldForm?.tanggal_dasar_masa_berlaku ?? ''),
                    },
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
                    selectedProduct() {
                        return this.productOptions[String(this.form.produkId)] ?? null;
                    },
                    expiryPreviewDate() {
                        const baseValue = this.form.tanggalDasarMasaBerlaku;

                        if (!baseValue) {
                            return null;
                        }

                        const product = this.selectedProduct();
                        if (!product) {
                            return null;
                        }

                        const baseDate = new Date(`${baseValue}T00:00:00`);
                        if (Number.isNaN(baseDate.getTime())) {
                            return null;
                        }

                        const result = new Date(baseDate.getTime());
                        const ukuranMatch = String(product.ukuran ?? '').replace(',', '.').match(/(\d+(?:\.\d+)?)/);
                        const ukuranAngka = ukuranMatch ? Number.parseFloat(ukuranMatch[1]) : null;

                        if (ukuranAngka === 1) {
                            result.setMonth(result.getMonth() + 6);
                        } else {
                            result.setFullYear(result.getFullYear() + 1);
                        }

                        return result;
                    },
                    expiryPreviewLabel() {
                        const previewDate = this.expiryPreviewDate();
                        if (!previewDate) {
                            return 'Pilih produk dan tanggal dasar masa berlaku untuk melihat perkiraan expired.';
                        }

                        return new Intl.DateTimeFormat('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                        }).format(previewDate);
                    },
                };
            }
        </script>
    @endonce
</x-app-layout>
