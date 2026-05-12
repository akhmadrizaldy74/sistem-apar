<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Refill Layanan APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Submodul layanan untuk pencatatan isi ulang APAR dan pemakaian media refill.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-refill-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Input Refill Layanan
            </button>
        </div>
    </x-slot>

    <div class="space-y-8" x-data="{ search: '', openModal: {{ $errors->any() ? 'true' : 'false' }} }" @open-refill-modal.window="openModal = true">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Refill</p>
                <p class="text-4xl font-black text-gray-900">{{ $refills->count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pendapatan Refill</p>
                <p class="text-4xl font-black text-red-700">Rp {{ number_format($refills->sum('biaya'), 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jenis Digunakan</p>
                <p class="text-4xl font-black text-blue-600">{{ $refills->pluck('jenis_refill_id')->filter()->unique()->count() }}</p>
            </div>
        </div>

        {{-- Stok Bahan Refill --}}
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($jenisRefills as $jr)
            <div class="rounded-2xl border p-6 {{ $jr->is_stok_rendah ? 'border-red-200 bg-red-50' : 'border-gray-100 bg-white' }} shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok {{ $jr->nama }}</p>
                        <p class="text-4xl font-black {{ $jr->is_stok_rendah ? 'text-red-700' : 'text-gray-900' }} mt-2">{{ $jr->stok }}</p>
                        <p class="text-xs font-semibold text-gray-500 mt-1">Min. {{ $jr->stok_minimum }} unit</p>
                    </div>
                    @if($jr->is_stok_rendah)
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-[10px] font-black rounded-xl uppercase tracking-widest animate-pulse">
                        Stok Rendah!
                    </span>
                    @else
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-xl uppercase tracking-widest">
                        Aman
                    </span>
                    @endif
                </div>
                @if($jr->harga)
                <p class="text-xs font-bold text-gray-500 mt-3 pt-3 border-t border-gray-100">
                    Harga Standar: <span class="text-gray-900">Rp {{ number_format($jr->harga, 0, ',', '.') }}</span>
                </p>
                @endif
            </div>
            @endforeach
        </div>

        <div class="rounded-[2rem] border border-emerald-100 bg-emerald-50 px-6 py-5">
            <p class="text-sm font-black text-emerald-800">Menu ini khusus untuk refill APAR.</p>
            <p class="text-sm font-semibold text-emerald-700/80 mt-2">Gunakan jenis refill Powder, CO2, atau Foam dengan harga standar agar terpisah dari service teknis seperti ganti selang atau baut.</p>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 flex flex-col md:flex-row gap-4 bg-gray-50/30 border-b border-gray-50">
                <div class="flex-grow flex items-center px-6 py-4 bg-white rounded-2xl border border-gray-100 gap-4 shadow-sm">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" x-model="search" placeholder="Cari nomor seri, pelanggan, atau jenis refill..." class="w-full border-none focus:ring-0 text-sm font-medium placeholder:text-gray-300">
                </div>
                <button onclick="window.print()" class="px-6 py-4 bg-white text-gray-600 font-bold rounded-2xl border border-gray-100 flex items-center gap-2 hover:bg-gray-50 transition shadow-sm no-print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak Laporan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit / Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Refill</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kapasitas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Biaya</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($refills as $refill)
                            <tr class="hover:bg-gray-50/30 transition-colors group"
                                x-show="search === '' || '{{ strtolower($refill->unitApar->no_seri . ' ' . $refill->unitApar->pelanggan->nama . ' ' . $refill->jenisRefill->nama) }}'.includes(search.toLowerCase())">
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">{{ $refill->tgl_refill->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-red-700 transition">{{ $refill->unitApar->no_seri }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $refill->unitApar->pelanggan->nama }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">
                                        {{ $refill->jenisRefill->nama }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">
                                        {{ $refill->unitApar->kapasitas ?? '1' }} {{ $refill->jenisRefill->satuan ?? 'Unit' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-gray-900">Rp {{ number_format($refill->biaya, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.refill.edit', $refill) }}" class="p-3 bg-white text-gray-400 hover:text-blue-600 rounded-xl border border-gray-100 hover:border-blue-100 hover:shadow-lg transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                        <form action="{{ route('admin.refill.destroy', $refill) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-3 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all" onclick="return confirm('Yakin ingin menghapus data refill ini?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada transaksi refill.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="openModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-5xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Input Refill</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Tambah data refill langsung dari halaman riwayat.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.refill.store') }}" method="POST" class="p-8 sm:p-10"
                    x-data="{
                        jenisId: '{{ old('jenis_refill_id') }}',
                        unitId: '{{ old('unit_apar_id') }}',
                        units: @js($units->map(fn ($unit) => [
                            'id' => $unit->id,
                            'seri' => $unit->no_seri,
                            'pelanggan' => $unit->pelanggan->nama,
                            'produk' => $unit->produk->nama ?? 'Unit APAR',
                            'expired' => optional($unit->tgl_expired)->format('d M Y'),
                        ])->values()),
                        refillMap: @js($jenisRefills->mapWithKeys(fn ($jenis) => [$jenis->id => ['nama' => $jenis->nama, 'harga' => $refillPackages[$jenis->nama] ?? 0]])),
                        selectedUnit() {
                            return this.units.find((unit) => Number(unit.id) === Number(this.unitId))
                        },
                        applyJenis() {
                            const refill = this.refillMap[this.jenisId]
                            if (refill) {
                                document.getElementById('biaya').value = refill.harga ?? ''
                            }
                        },
                    }">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label for="unit_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Unit APAR</label>
                            <select name="unit_apar_id" id="unit_apar_id" x-model="unitId" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                <option value="">Pilih unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_apar_id') == $unit->id)>{{ $unit->no_seri }} - {{ $unit->pelanggan->nama }} - {{ $unit->produk->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('unit_apar_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="jenis_refill_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Refill</label>
                            <select name="jenis_refill_id" id="jenis_refill_id" x-model="jenisId" @change="applyJenis()" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                                <option value="">Pilih jenis refill</option>
                                @foreach($jenisRefills as $jenisRefill)
                                    <option value="{{ $jenisRefill->id }}" @selected(old('jenis_refill_id') == $jenisRefill->id)>{{ $jenisRefill->nama }} - Rp {{ number_format($refillPackages[$jenisRefill->nama] ?? 0, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">Biaya refill akan mengikuti standar jenis isi ulang.</p>
                            <x-input-error :messages="$errors->get('jenis_refill_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tgl_refill" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Refill</label>
                            <input type="date" name="tgl_refill" id="tgl_refill" value="{{ old('tgl_refill', now()->format('Y-m-d')) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                            <x-input-error :messages="$errors->get('tgl_refill')" class="mt-2" />
                        </div>

                        <div>
                            <label for="biaya" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Biaya</label>
                            <input type="number" name="biaya" id="biaya" value="{{ old('biaya') }}" required min="0"
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900"
                                placeholder="Contoh: 250000">
                            <x-input-error :messages="$errors->get('biaya')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 rounded-[2rem] border border-gray-100 bg-gray-50 p-6">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Unit Refill</p>
                            <template x-if="selectedUnit()">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No. Seri</p>
                                        <p class="text-sm font-black text-gray-900 mt-2" x-text="selectedUnit()?.seri"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</p>
                                        <p class="text-sm font-black text-gray-900 mt-2" x-text="selectedUnit()?.pelanggan"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</p>
                                        <p class="text-sm font-black text-gray-900 mt-2" x-text="selectedUnit()?.produk"></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Expired</p>
                                        <p class="text-sm font-black text-red-700 mt-2" x-text="selectedUnit()?.expired || '-'"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!selectedUnit()">
                                <p class="text-sm font-semibold text-gray-500 mt-4">Pilih unit APAR untuk melihat data unit yang akan di-refill.</p>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</button>
                        <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
