<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.suppliers.index') }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Tambah Supplier</h2>
                <p class="text-sm text-slate-500 font-semibold">Isi data supplier baru untuk pengadaan barang & Purchase Order (PO).</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('admin.suppliers.store') }}" method="POST" class="p-8 sm:p-12 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Nama Supplier -->
                    <div class="md:col-span-2">
                        <label for="nama_supplier" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Nama Supplier <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="nama_supplier" id="nama_supplier" value="{{ old('nama_supplier') }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Contoh: PT. Jaya Abadi Sentosa">
                        <x-input-error :messages="$errors->get('nama_supplier')" class="mt-2" />
                    </div>

                    <!-- Kontak Person -->
                    <div>
                        <label for="kontak_person" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Kontak Person
                        </label>
                        <input type="text" name="kontak_person" id="kontak_person" value="{{ old('kontak_person') }}"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Contoh: Budi Santoso">
                        <x-input-error :messages="$errors->get('kontak_person')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Email
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="supplier@email.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- No Telepon -->
                    <div>
                        <label for="no_telepon" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            No. Telepon
                        </label>
                        <input type="text" name="no_telepon" id="no_telepon" value="{{ old('no_telepon') }}"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Contoh: 021-5551234">
                        <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
                    </div>

                    <!-- No WhatsApp -->
                    <div>
                        <label for="no_wa" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            No. WhatsApp <span class="text-red-600">*</span>
                            <span class="text-[10px] text-slate-400 font-normal lowercase">(untuk kirim PO via WA)</span>
                        </label>
                        <input type="text" name="no_wa" id="no_wa" value="{{ old('no_wa') }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Contoh: 081234567890">
                        <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <label for="alamat" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Alamat Lengkap
                        </label>
                        <textarea name="alamat" id="alamat" rows="3"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-semibold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Masukkan alamat gudang / kantor supplier">{{ old('alamat') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-3">
                            Status Supplier <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="status" value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'checked' : '' }}
                                    class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-500">
                                <span class="text-sm font-bold text-slate-900">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600 mr-1"></span> Aktif
                                </span>
                            </label>
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="status" value="nonaktif" {{ old('status') === 'nonaktif' ? 'checked' : '' }}
                                    class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-500">
                                <span class="text-sm font-bold text-slate-900">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-slate-500 mr-1"></span> Nonaktif
                                </span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.suppliers.index') }}" class="px-8 py-4 bg-slate-600 text-white font-black rounded-2xl hover:bg-slate-700 transition uppercase tracking-widest text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
