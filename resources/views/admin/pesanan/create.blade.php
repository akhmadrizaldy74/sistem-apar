<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.pesanan.index') }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Buat Pesanan Baru</h2>
                <p class="text-sm text-slate-500 font-semibold">Pilih tipe pesanan (Jual Produk, Refill, atau Jasa) dan lengkapi rincian transaksi.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl" x-data="{
        tipe: '{{ old('tipe_pesanan', 'jual_produk') }}',
        pelangganId: '{{ old('pelanggan_id', '') }}'
    }">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('admin.pesanan.store') }}" method="POST" class="p-8 sm:p-12 space-y-8">
                @csrf

                <!-- Section Tipe Pesanan -->
                <div class="bg-slate-50/80 p-6 rounded-3xl border border-slate-200/60 space-y-4">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest block">
                        Pilih Tipe Pesanan <span class="text-red-600">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 rounded-2xl border-2 transition cursor-pointer"
                            :class="tipe === 'jual_produk' ? 'border-blue-600 bg-blue-50/50 text-blue-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="tipe_pesanan" value="jual_produk" x-model="tipe" class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-black">Jual Produk</span>
                                <span class="block text-[11px] font-semibold text-slate-500">Pembelian APAR Baru</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 rounded-2xl border-2 transition cursor-pointer"
                            :class="tipe === 'refill' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="tipe_pesanan" value="refill" x-model="tipe" class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                            <div class="ml-3">
                                <span class="block text-sm font-black">Refill APAR</span>
                                <span class="block text-[11px] font-semibold text-slate-500">Isi Ulang APAR</span>
                            </div>
                        </label>

                        <label class="flex items-center p-4 rounded-2xl border-2 transition cursor-pointer"
                            :class="tipe === 'jasa' ? 'border-amber-600 bg-amber-50/50 text-amber-900' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'">
                            <input type="radio" name="tipe_pesanan" value="jasa" x-model="tipe" class="w-5 h-5 text-amber-600 focus:ring-amber-500">
                            <div class="ml-3">
                                <span class="block text-sm font-black">Jasa Service</span>
                                <span class="block text-[11px] font-semibold text-slate-500">Service & Perbaikan</span>
                            </div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('tipe_pesanan')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Pelanggan -->
                    <div>
                        <label for="pelanggan_id" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Pilih Pelanggan <span class="text-red-600">*</span>
                        </label>
                        <select name="pelanggan_id" id="pelanggan_id" x-model="pelangganId" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($pelanggans as $pelanggan)
                                <option value="{{ $pelanggan->id }}" {{ old('pelanggan_id') == $pelanggan->id ? 'selected' : '' }}>
                                    {{ $pelanggan->nama }} ({{ $pelanggan->no_wa ?: 'No WA -' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('pelanggan_id')" class="mt-2" />
                    </div>

                    <!-- Tanggal Pesanan -->
                    <div>
                        <label for="tanggal" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Tanggal Pesanan <span class="text-red-600">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-2" />
                    </div>
                </div>

                <!-- SECTION 1: JUAL PRODUK -->
                <div x-show="tipe === 'jual_produk'" x-transition class="space-y-6 pt-4 border-t border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                        Section Rincian Produk APAR
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="sm:col-span-2">
                            <label for="produk_id" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                                Produk APAR <span class="text-red-600">*</span>
                            </label>
                            <select name="produk_id" id="produk_id"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($produks as $produk)
                                    <option value="{{ $produk->id }}" {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                        {{ $produk->nama }} - Merek: {{ $produk->merek }} (Rp {{ number_format($produk->harga, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('produk_id')" class="mt-2" />
                        </div>
                        <div>
                            <label for="jumlah" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                                Jumlah Unit <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="jumlah" id="jumlah" min="1" value="{{ old('jumlah', 1) }}"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                            <x-input-error :messages="$errors->get('jumlah')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: REFILL -->
                <div x-show="tipe === 'refill'" x-transition class="space-y-6 pt-4 border-t border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                        Section Unit APAR Pelanggan
                    </h3>
                    <div>
                        <label for="unit_apar_id" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Unit APAR Milik Pelanggan <span class="text-red-600">*</span>
                        </label>
                        <select name="unit_apar_id" id="unit_apar_id"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                            <option value="">-- Pilih Unit APAR --</option>
                            @foreach($unitApars as $unit)
                                <option value="{{ $unit->id }}"
                                    x-show="!pelangganId || '{{ $unit->pelanggan_id }}' === pelangganId"
                                    {{ old('unit_apar_id') == $unit->id ? 'selected' : '' }}>
                                    No Seri: {{ $unit->no_seri }} | {{ $unit->merek }} {{ $unit->kapasitas }} (Pelanggan: {{ $unit->pelanggan?->nama ?: 'Tanpa Pemilik' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('unit_apar_id')" class="mt-2" />
                    </div>
                </div>

                <!-- SECTION 3: JASA -->
                <div x-show="tipe === 'jasa'" x-transition class="space-y-6 pt-4 border-t border-slate-100">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-600"></span>
                        Section Jasa APAR (Aktif)
                    </h3>
                    <div>
                        <label for="jasa_id" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Pilih Jasa Layanan <span class="text-red-600">*</span>
                        </label>
                        <select name="jasa_id" id="jasa_id"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                            <option value="">-- Pilih Layanan Jasa --</option>
                            @foreach($jasas as $jasa)
                                <option value="{{ $jasa->id }}" {{ old('jasa_id') == $jasa->id ? 'selected' : '' }}>
                                    {{ $jasa->nama_jasa }} - Rp {{ number_format($jasa->harga, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jasa_id')" class="mt-2" />
                    </div>
                </div>

                <!-- Catatan Admin -->
                <div>
                    <label for="catatan_admin" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Catatan Transaksi (Opsional)
                    </label>
                    <textarea name="catatan_admin" id="catatan_admin" rows="3"
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-semibold text-slate-900 placeholder:text-slate-300 transition outline-none"
                        placeholder="Catatan tambahan pesanan admin...">{{ old('catatan_admin') }}</textarea>
                    <x-input-error :messages="$errors->get('catatan_admin')" class="mt-2" />
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.pesanan.index') }}" class="px-8 py-4 bg-slate-600 text-white font-black rounded-2xl hover:bg-slate-700 transition uppercase tracking-widest text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Simpan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
