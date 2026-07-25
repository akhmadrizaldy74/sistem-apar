<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.jasa.index') }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Edit Jasa Layanan</h2>
                <p class="text-sm text-slate-500 font-semibold">Perbarui informasi jasa layanan {{ $jasa->nama_jasa }}.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('admin.jasa.update', $jasa->id) }}" method="POST" class="p-8 sm:p-12 space-y-8">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Nama Jasa -->
                    <div>
                        <label for="nama_jasa" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Nama Jasa Layanan <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="nama_jasa" id="nama_jasa" value="{{ old('nama_jasa', $jasa->nama_jasa) }}" required
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Contoh: Servis Ringan APAR, Penggantian Selang, dll.">
                        <x-input-error :messages="$errors->get('nama_jasa')" class="mt-2" />
                    </div>

                    <!-- Harga -->
                    <div>
                        <label for="harga" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Harga Jasa (Rp) <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                            <input type="number" step="500" name="harga" id="harga" value="{{ old('harga', $jasa->harga) }}" required min="0"
                                class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 placeholder:text-slate-300 transition outline-none"
                                placeholder="50000">
                        </div>
                        <x-input-error :messages="$errors->get('harga')" class="mt-2" />
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label for="deskripsi" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                            Deskripsi Layanan (Opsional)
                        </label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-semibold text-slate-900 placeholder:text-slate-300 transition outline-none"
                            placeholder="Jelaskan cakupan perbaikan atau penggantian sparepart yang dikerjakan...">{{ old('deskripsi', $jasa->deskripsi) }}</textarea>
                        <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-3">
                            Status Jasa <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="status" value="aktif" {{ old('status', $jasa->status) === 'aktif' ? 'checked' : '' }}
                                    class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-500">
                                <span class="text-sm font-bold text-slate-900">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-600 mr-1"></span> Aktif
                                </span>
                            </label>
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="status" value="nonaktif" {{ old('status', $jasa->status) === 'nonaktif' ? 'checked' : '' }}
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
                    <a href="{{ route('admin.jasa.index') }}" class="px-8 py-4 bg-slate-600 text-white font-black rounded-2xl hover:bg-slate-700 transition uppercase tracking-widest text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Update Jasa
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
