<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.service.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Layanan Service</h2>
                <p class="text-sm text-gray-500 font-medium">Perbarui catatan layanan perawatan unit</p>
            </div>
        </div>
    </x-slot>

    @php
        $pelanggansEdit = $units->groupBy(fn($u) => $u->pelanggan->id ?? 0)->map(fn($units, $pelangganId) => [
            'pelangganId' => $pelangganId,
            'pelangganNama' => $units->first()->pelanggan->nama ?? '-',
            'units' => $units->map(fn($u) => [
                'id' => $u->id,
                'seri' => $u->no_seri,
                'merek' => $u->produk->merek ?? '-',
                'kapasitas' => $u->produk->kapasitas ?? '-',
                'jenis' => $u->produk->jenisApar->nama ?? '-',
                'expired' => $u->tgl_expired ? $u->tgl_expired->format('d M Y') : '-',
            ])->values(),
        ])->values();
    @endphp

    <div class="max-w-5xl" x-data="{
        selectedPaketId: '{{ old('service_paket_id', $service->service_paket_id) }}',
        pelangganId: '',
        unitId: '{{ old('unit_apar_id', $service->unit_apar_id) }}',
        pelanggans: @js($pelanggansEdit),
        pakets: @js($servicePakets->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'label' => $p->label,
            'harga' => $p->harga,
            'rincian' => $p->rincian_layanan,
            'peralatans' => $p->peralatans->map(fn ($pr) => ['id' => $pr->id, 'nama' => $pr->nama, 'jumlah' => $pr->pivot->jumlah_estimasi])->values(),
        ])->values()),
        get unitOptions() {
            let found = this.pelanggans.find((p) => Number(p.pelangganId) === Number(this.pelangganId));
            return found ? found.units : [];
        },
        selectedPaket() {
            return this.pakets.find((p) => String(p.id) === String(this.selectedPaketId))
        },
    }">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.service.update', $service) }}" method="POST" class="p-12">
                @csrf
                @method('PUT')

                @if($service->status_konfirmasi === 'pending')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        {{-- PELANGGAN --}}
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pelanggan</label>
                            <select x-model="pelangganId" @change="unitId = ''"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition">
                                <option value="">— Pilih pelanggan —</option>
                                <template x-for="p in pelanggans" :key="p.pelangganId">
                                    <option :value="p.pelangganId" x-text="p.pelangganNama"></option>
                                </template>
                            </select>
                        </div>

                        {{-- UNIT --}}
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Unit APAR</label>
                            <select name="unit_apar_id" x-model="unitId" required
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition">
                                <option value="">— Pilih unit APAR —</option>
                                <template x-for="u in unitOptions" :key="u.id">
                                    <option :value="u.id" x-text="u.seri"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('unit_apar_id')" class="mt-2" />
                        </div>

                        {{-- UNIT INFO --}}
                        <template x-if="unitId">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 space-y-2">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No. Seri</span>
                                    <span class="text-sm font-black text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.seri || '-')"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Merek</span>
                                    <span class="text-sm font-black text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.merek || '-')"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ukuran</span>
                                    <span class="text-sm font-bold text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.kapasitas || '-')"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</span>
                                    <span class="text-sm font-bold text-gray-900" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.jenis || '-')"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Expired</span>
                                    <span class="text-sm font-black text-red-700" x-text="(unitOptions.find(u => Number(u.id) === Number(unitId))?.expired || '-')"></span>
                                </div>
                            </div>
                        </template>

                        {{-- TANGGAL --}}
                        <div>
                            <label for="tgl_service" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Service</label>
                            <input type="date" name="tgl_service" id="tgl_service" value="{{ old('tgl_service', $service->tgl_service) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition">
                            <x-input-error :messages="$errors->get('tgl_service')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- PAKET --}}
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Paket Service</label>
                            <select name="service_paket_id" x-model="selectedPaketId" required
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 transition">
                                <option value="">— Pilih paket service —</option>
                                <template x-for="p in pakets" :key="p.id">
                                    <option :value="p.id" x-text="p.label + ' — Rp ' + Number(p.harga).toLocaleString('id-ID')"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('service_paket_id')" class="mt-2" />
                        </div>

                        {{-- PAKET INFO --}}
                        <template x-if="selectedPaket()">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50/50 overflow-hidden">
                                <div class="px-5 py-4 bg-slate-800/80 border-b border-slate-700">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-black text-white uppercase tracking-widest" x-text="selectedPaket()?.nama"></p>
                                        <p class="text-sm font-black text-red-400" x-text="'Rp ' + Number(selectedPaket()?.harga).toLocaleString('id-ID')"></p>
                                    </div>
                                </div>
                                <div class="p-5 space-y-4">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rincian Layanan</p>
                                        <p class="text-sm font-semibold text-gray-700" x-text="selectedPaket()?.rincian"></p>
                                    </div>
                                    <template x-if="selectedPaket()?.peralatans?.length > 0">
                                        <div>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Estimasi Peralatan</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="per in selectedPaket()?.peralatans" :key="per.id">
                                                    <span class="inline-flex flex-col px-2.5 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-700">
                                                        <span x-text="per.nama"></span>
                                                        <span class="text-gray-400 font-medium">×<span x-text="per.jumlah"></span></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- KETERANGAN --}}
                        <div>
                            <label for="keterangan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan / Catatan</label>
                            <textarea name="keterangan" id="keterangan" rows="5"
                                class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-500/30 focus:border-red-400 font-bold text-gray-900 placeholder:text-gray-300 transition resize-none">{{ old('keterangan', $service->keterangan) }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                        </div>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit APAR</span>
                            <span class="text-sm font-black text-gray-900">{{ $service->unitApar?->no_seri ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</span>
                            <span class="text-sm font-bold text-gray-900">{{ $service->unitApar?->pelanggan?->nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Paket</span>
                            <span class="text-sm font-bold text-gray-900">{{ $service->servicePaket?->nama ?? $service->jenis_service }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</span>
                            <span class="text-sm font-bold text-gray-900">{{ optional($service->tgl_service)->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Biaya</span>
                            <span class="text-sm font-bold text-gray-900">Rp {{ number_format($service->biaya, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan</span>
                            <p class="text-sm font-medium text-gray-700">{{ $service->keterangan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-12 flex justify-end gap-4">
                    <a href="{{ route('admin.service.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    @if($service->status_konfirmasi === 'pending')
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update Log
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
