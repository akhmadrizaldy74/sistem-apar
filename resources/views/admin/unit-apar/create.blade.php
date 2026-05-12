<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.unit-apar.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Registrasi APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Daftarkan unit peralatan pemadam ke sistem monitoring</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.unit-apar.store') }}" method="POST" class="p-12">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div>
                            <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pelanggan / Pemilik</label>
                            <select name="pelanggan_id" id="pelanggan_id" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih Pelanggan</option>
                                @foreach($pelanggans as $p)
                                    <option value="{{ $p->id }}" {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="produk_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Model Produk</label>
                            <select name="produk_id" id="produk_id" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih Produk</option>
                                @foreach($produks as $pr)
                                    <option value="{{ $pr->id }}" {{ old('produk_id') == $pr->id ? 'selected' : '' }}>{{ $pr->nama }} • {{ $pr->kapasitas ?? '-' }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('produk_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="no_seri" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor Seri Unit</label>
                            <input type="text" name="no_seri" id="no_seri" value="{{ old('no_seri') }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Contoh: SN-2024-001">
                            <x-input-error :messages="$errors->get('no_seri')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label for="tgl_produksi" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Produksi</label>
                            <input type="date" name="tgl_produksi" id="tgl_produksi" value="{{ old('tgl_produksi') }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                            <p class="mt-2 text-[10px] text-gray-400 font-black uppercase tracking-wider italic">
                                * Masa berlaku dihitung sejak tanggal produksi APAR.
                            </p>
                            <x-input-error :messages="$errors->get('tgl_produksi')" class="mt-2" />
                        </div>

                        <div>
                            <label for="tgl_beli" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Beli (Opsional)</label>
                            <input type="date" name="tgl_beli" id="tgl_beli" value="{{ old('tgl_beli', date('Y-m-d')) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                            <p class="mt-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider italic">* Hanya digunakan sebagai catatan informasi transaksi.</p>
                            <x-input-error :messages="$errors->get('tgl_beli')" class="mt-2" />
                        </div>
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Info Otomatis</p>
                            <p class="text-sm font-bold text-gray-700 mt-3">Kapasitas dan jenis APAR akan mengikuti produk yang dipilih.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-end gap-4">
                    <a href="{{ route('admin.unit-apar.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Registrasi Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
