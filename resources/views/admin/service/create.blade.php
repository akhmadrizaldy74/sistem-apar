<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.service.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Tambah Data Service APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Isi data service APAR, lalu klik Simpan.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl" x-data="{
        paket: '{{ old('jenis_service') }}',
        hargaMap: {
            'Inspeksi Rutin': 50000,
            'Ganti Segel': 75000,
            'Ganti Selang': 150000,
            'Ganti Baut / Nozzle': 35000,
            'Cleaning Tabung': 100000
        },
        ketMap: {
            'Inspeksi Rutin': 'Inspeksi rutin unit APAR dan pengecekan tekanan.',
            'Ganti Segel': 'Penggantian segel pengaman APAR.',
            'Ganti Selang': 'Penggantian selang unit APAR.',
            'Ganti Baut / Nozzle': 'Penggantian baut / nozzle / aksesoris kecil.',
            'Cleaning Tabung': 'Pembersihan tabung dan pengecekan fisik menyeluruh.'
        },
        applyPaket() {
            if (!this.paket) return;
            document.getElementById('biaya').value = this.hargaMap[this.paket] ?? '';
            document.getElementById('keterangan').value = this.ketMap[this.paket] ?? '';
        }
    }">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.service.store') }}" method="POST" class="p-12">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div>
                            <label for="unit_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Unit APAR</label>
                            <select name="unit_apar_id" id="unit_apar_id" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih Unit</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_apar_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->no_seri }} - {{ $u->pelanggan?->nama ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('unit_apar_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tgl_service" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Service</label>
                            <input type="date" name="tgl_service" id="tgl_service" value="{{ old('tgl_service', date('Y-m-d')) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                            <x-input-error :messages="$errors->get('tgl_service')" class="mt-2" />
                        </div>

                        <div>
                            <label for="jenis_service" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis Service</label>
                            <select name="jenis_service" id="jenis_service" x-model="paket" @change="applyPaket" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih jenis service</option>
                                <option value="Inspeksi Rutin">Inspeksi Rutin - Rp 50.000</option>
                                <option value="Ganti Segel">Ganti Segel - Rp 75.000</option>
                                <option value="Ganti Selang">Ganti Selang - Rp 150.000</option>
                                <option value="Ganti Baut / Nozzle">Ganti Baut / Nozzle - Rp 35.000</option>
                                <option value="Cleaning Tabung">Cleaning Tabung - Rp 100.000</option>
                            </select>
                            <p class="mt-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider">Pilih jenis service untuk mengisi biaya dan keterangan otomatis.</p>
                            <x-input-error :messages="$errors->get('jenis_service')" class="mt-2" />
                        </div>

                        <div>
                            <label for="biaya" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Biaya (IDR)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-gray-400">Rp</span>
                                <input type="number" name="biaya" id="biaya" value="{{ old('biaya') }}" required
                                    class="w-full pl-14 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="0">
                            </div>
                            <x-input-error :messages="$errors->get('biaya')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="keterangan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Keterangan Service</label>
                            <textarea name="keterangan" id="keterangan" rows="8" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Detail pekerjaan: Ganti segel, pengisian gas, pembersihan tabung...">{{ old('keterangan') }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-end gap-4">
                    <a href="{{ route('admin.service.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
