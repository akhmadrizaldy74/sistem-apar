<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.refill.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Refill Layanan</h2>
                <p class="text-sm text-gray-500 font-medium">Perbarui layanan refill untuk unit {{ $refill->unitApar->no_seri }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl" x-data="{
        jenisId: '{{ old('jenis_refill_id', $refill->jenis_refill_id) }}',
        unitId: '{{ old('unit_apar_id', $refill->unit_apar_id) }}',
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
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.refill.update', $refill) }}" method="POST" class="p-12">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label for="unit_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Unit APAR</label>
                        <select name="unit_apar_id" id="unit_apar_id" x-model="unitId" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                            <option value="">Pilih unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_apar_id', $refill->unit_apar_id) == $unit->id)>
                                    {{ $unit->no_seri }} - {{ $unit->pelanggan->nama }} - {{ $unit->produk->nama }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('unit_apar_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="jenis_refill_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Refill</label>
                        <select name="jenis_refill_id" id="jenis_refill_id" x-model="jenisId" @change="applyJenis()" required class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                            <option value="">Pilih jenis refill</option>
                            @foreach($jenisRefills as $jenisRefill)
                                <option value="{{ $jenisRefill->id }}" @selected(old('jenis_refill_id', $refill->jenis_refill_id) == $jenisRefill->id)>
                                    {{ $jenisRefill->nama }} - Rp {{ number_format($refillPackages[$jenisRefill->nama] ?? 0, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">Jenis refill memiliki harga standar.</p>
                        <x-input-error :messages="$errors->get('jenis_refill_id')" class="mt-2" />
                    </div>

                    <div>
                        <label for="tgl_refill" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Refill</label>
                        <input type="date" name="tgl_refill" id="tgl_refill" value="{{ old('tgl_refill', $refill->tgl_refill->format('Y-m-d')) }}" required
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                        <x-input-error :messages="$errors->get('tgl_refill')" class="mt-2" />
                    </div>

                    <div>
                        <label for="biaya" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Biaya</label>
                        <input type="number" name="biaya" id="biaya" value="{{ old('biaya', $refill->biaya) }}" required min="0"
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
                    </div>
                </div>

                <div class="mt-12 flex justify-end gap-4">
                    <a href="{{ route('admin.refill.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
