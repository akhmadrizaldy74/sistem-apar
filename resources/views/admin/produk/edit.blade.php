<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.produk.index') }}" class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Produk</h2>
                <p class="text-sm text-gray-500 font-medium">Perbarui informasi peralatan: {{ $produk->nama }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.produk.update', $produk) }}" method="POST" enctype="multipart/form-data" class="p-12">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div>
                            <label for="nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Produk</label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $produk->nama) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Contoh: APAR Powder 3kg">
                            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                        </div>

                        <div>
                            <label for="jenis_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis APAR</label>
                            <select name="jenis_apar_id" id="jenis_apar_id" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih Jenis</option>
                                @foreach($jenisApars as $j)
                                    <option value="{{ $j->id }}" {{ old('jenis_apar_id', $produk->jenis_apar_id) == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jenis_apar_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="merek" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Merek</label>
                            <select name="merek" id="merek" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="">Pilih Merek</option>
                                @foreach(['SAFETY', 'ABC', 'GUARD'] as $merek)
                                    <option value="{{ $merek }}" {{ old('merek', $produk->merek) == $merek ? 'selected' : '' }}>{{ $merek }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('merek')" class="mt-2" />
                        </div>

                        <div>
                            <label for="harga" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga (IDR)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-gray-400">Rp</span>
                                <input type="number" name="harga" id="harga" value="{{ old('harga', $produk->harga) }}" required
                                    class="w-full pl-14 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="0">
                            </div>
                            <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                        </div>

                        <div>
                            <label for="kapasitas" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kapasitas</label>
                            <input type="text" name="kapasitas" id="kapasitas" value="{{ old('kapasitas', $produk->kapasitas) }}" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Contoh: 3 kg / 6 Liter">
                            <x-input-error :messages="$errors->get('kapasitas')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Gambar Produk</label>
                            <div class="relative group">
                                <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden">
                                <label for="gambar" class="cursor-pointer flex flex-col items-center justify-center w-full h-44 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 group-hover:border-red-600/30 transition-all overflow-hidden relative">
                                    <div class="flex flex-col items-center text-gray-400 {{ $produk->gambar ? 'hidden' : '' }} group-hover:text-red-600 transition" id="upload-placeholder">
                                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span class="text-xs font-black uppercase tracking-widest">Ganti Foto</span>
                                    </div>
                                    <img id="preview" src="{{ $produk->gambar ? asset('storage/' . $produk->gambar) : '' }}" class="absolute inset-0 w-full h-full object-cover {{ $produk->gambar ? '' : 'hidden' }}">
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('gambar')" class="mt-2" />
                        </div>

                        <div>
                            <label for="penggunaan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Penggunaan</label>
                            <textarea name="penggunaan" id="penggunaan" rows="5" required
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Contoh: Perkantoran, rumah, kendaraan, gudang">{{ old('penggunaan', $produk->penggunaan) }}</textarea>
                            <x-input-error :messages="$errors->get('penggunaan')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-end gap-4">
                    <a href="{{ route('admin.produk.index') }}" class="px-8 py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-900 transition">Batal</a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('gambar').onchange = function (evt) {
            const [file] = this.files
            if (file) {
                document.getElementById('preview').src = URL.createObjectURL(file)
                document.getElementById('preview').classList.remove('hidden')
                document.getElementById('upload-placeholder').classList.add('hidden')
            }
        }
    </script>
</x-app-layout>
