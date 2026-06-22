<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Daftar Produk</h2>
                <p class="text-base font-semibold leading-7 text-gray-700">Kelola data produk APAR yang tampil di katalog pelanggan.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-produk-modal'))" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Produk
            </button>
        </div>
    </x-slot>

    <div class="space-y-8" x-data="{ openModal: {{ $errors->any() ? 'true' : 'false' }} }" @open-produk-modal.window="openModal = true">
        <!-- Filter Form -->
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                class="px-5 py-3 bg-white rounded-2xl border border-gray-200 text-sm font-semibold text-gray-800 focus:border-red-400 focus:ring-1 focus:ring-red-400 w-72" />
            <select name="jenis_apar_id" onchange="this.form.submit()" class="px-4 py-3 bg-white rounded-2xl border border-gray-200 text-sm font-semibold text-gray-800 focus:border-red-400">
                <option value="">Semua Jenis</option>
                @foreach($jenisApars as $ja)
                    <option value="{{ $ja->id }}" {{ request('jenis_apar_id') == $ja->id ? 'selected' : '' }}>{{ $ja->nama }}</option>
                @endforeach
            </select>
            @if(request('search') || request('jenis_apar_id'))
                <a href="{{ route('admin.produk.index') }}" class="px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-2xl transition">Reset</a>
            @endif
        </form>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Produk</p>
                <p class="text-4xl font-black text-gray-900">{{ \App\Models\Produk::count() }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kategori Aktif</p>
                <p class="text-4xl font-black text-gray-900">{{ \App\Models\JenisApar::count() }}</p>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Gambar</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Merek</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kapasitas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($produks as $p)
                            <tr class="hover:bg-gray-50/30 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="w-16 h-16 bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 shadow-inner group-hover:scale-110 transition-transform duration-500">
                                        @if($p->resolved_image_url)
                                            <img src="{{ $p->resolved_image_url }}" alt="{{ $p->nama }}" class="w-full h-full object-contain p-1">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-base font-black leading-6 text-gray-900 group-hover:text-red-700 transition">{{ $p->nama }}</p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-gray-600">SKU: FSA-{{ strtoupper(Str::slug($p->nama)) }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-1.5 bg-emerald-50 text-emerald-700 text-[11px] font-black uppercase tracking-widest rounded-full">
                                        {{ $p->merek }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-1.5 bg-gray-100 text-gray-700 text-[11px] font-black uppercase tracking-widest rounded-full group-hover:bg-red-50 group-hover:text-red-700 transition-colors">
                                        {{ $p->jenisApar->nama }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-800">{{ $p->kapasitas ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $hargaAcuanBeli = (float) ($productPurchaseReferencePrices->get($p->id, (float) ($p->harga ?? 0)));
                                    @endphp
                                    <p class="text-base font-black text-gray-900">Rp {{ number_format($p->harga, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs font-bold leading-5 text-gray-700">Acuan beli terakhir: Rp {{ number_format($hargaAcuanBeli, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.produk.edit', $p) }}" class="p-3 bg-white text-gray-400 hover:text-blue-600 rounded-xl border border-gray-100 hover:border-blue-100 hover:shadow-lg transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                        <form action="{{ route('admin.produk.destroy', $p) }}" method="POST" class="inline" data-confirm="Yakin ingin menghapus produk ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-3 bg-white text-gray-400 hover:text-red-600 rounded-xl border border-gray-100 hover:border-red-100 hover:shadow-lg transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-8 bg-gray-50/30 border-t border-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total: {{ $produks->count() }} dari {{ $produks->total() }} produk</p>
                @if($produks->hasPages())
                    {{ $produks->links() }}
                @endif
            </div>
        </div>

        <div x-show="openModal" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-3 sm:items-center sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="app-modal-shell relative my-3 max-w-5xl sm:my-6"
            >
                <div class="app-modal-header flex items-start justify-between px-5 py-4 bg-white/95 backdrop-blur border-b border-gray-100 sm:items-center sm:px-6 lg:px-8 lg:py-6">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Tambah Produk</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Input data produk langsung dari halaman ini.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <div class="app-modal-body grid grid-cols-1 gap-8 p-5 sm:grid-cols-2 sm:p-6 lg:p-8">
                        <div class="space-y-6">
                            <div>
                                <label for="nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Produk</label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: APAR Dry Chemical Powder 3 kg">
                                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                            </div>

                            <div>
                                <label for="jenis_apar_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Jenis APAR</label>
                                <select name="jenis_apar_id" id="jenis_apar_id" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                    <option value="">Pilih jenis APAR</option>
                                    @foreach($jenisApars as $jenisApar)
                                        <option value="{{ $jenisApar->id }}" @selected(old('jenis_apar_id') == $jenisApar->id)>{{ $jenisApar->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('jenis_apar_id')" class="mt-2" />
                            </div>

                            <div>
                                <label for="merek" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Merek</label>
                                <select name="merek" id="merek" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                    <option value="">Pilih merek</option>
                                    @foreach(['FIREFIX', 'GuardALL', 'TONATA'] as $merek)
                                        <option value="{{ $merek }}" @selected(old('merek') == $merek)>{{ $merek }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('merek')" class="mt-2" />
                            </div>

                            <div>
                                <label for="kapasitas" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kapasitas</label>
                                <input type="text" name="kapasitas" id="kapasitas" value="{{ old('kapasitas') }}" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: 3 kg / 6 kg">
                                <x-input-error :messages="$errors->get('kapasitas')" class="mt-2" />
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="harga" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Harga</label>
                                <input type="number" name="harga" id="harga" value="{{ old('harga') }}" required min="0"
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: 350000">
                                <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                            </div>

                            <div>
                                <label for="gambar" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Gambar Produk</label>
                                <input type="file" name="gambar" id="gambar"
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <x-input-error :messages="$errors->get('gambar')" class="mt-2" />
                            </div>

                            <div>
                                <label for="penggunaan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Penggunaan</label>
                                <textarea name="penggunaan" id="penggunaan" rows="5" required
                                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: Perkantoran, rumah, kendaraan, gudang">{{ old('penggunaan') }}</textarea>
                                <x-input-error :messages="$errors->get('penggunaan')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="app-modal-footer flex flex-col-reverse gap-3 border-t border-gray-100 px-5 py-4 sm:flex-row sm:justify-end sm:px-6 lg:px-8">
                        <button type="button" @click="openModal = false" class="w-full px-8 py-4 text-xs font-black uppercase tracking-widest text-gray-400 transition hover:text-gray-900 sm:w-auto">Batal</button>
                        <button type="submit" class="w-full rounded-2xl bg-red-700 px-10 py-4 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-red-700/30 transition hover:bg-red-800 sm:w-auto">
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
