<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.jenis-refill.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Jenis Refil</h2>
                <p class="text-sm text-gray-500 font-medium">Perbarui data jenis refil: {{ $jenisRefill->nama }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.jenis-refill.update', $jenisRefill) }}" method="POST" class="p-12 space-y-8">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label for="nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Jenis Refil</label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $jenisRefill->nama) }}" required
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                            placeholder="Contoh: Dry Chemical Powder">
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>

                    <div>
                        <label for="satuan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Satuan</label>
                        <select name="satuan" id="satuan" required
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                            <option value="kg" {{ old('satuan', $jenisRefill->satuan) == 'kg' ? 'selected' : '' }}>Kg (Kilogram)</option>
                            <option value="kg" {{ old('satuan', $jenisRefill->satuan) == 'kg' ? 'selected' : '' }}>kg</option>
                        </select>
                        <x-input-error :messages="$errors->get('satuan')" class="mt-2" />
                    </div>

                    <div>
                        <label for="harga" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga Standar per Satuan</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input type="number" name="harga" id="harga" value="{{ old('harga', $jenisRefill->harga) }}" required
                                class="w-full pl-14 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                        </div>
                        <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <p class="text-sm font-bold text-amber-800">Informasi Master Data</p>
                            <p class="text-xs text-amber-700 mt-1">Halaman ini tidak mengubah stok. Harga standar yang diperbarui di sini akan dipakai untuk transaksi pembelian refil berikutnya.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <a href="{{ route('admin.jenis-refill.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update Jenis Refil
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
