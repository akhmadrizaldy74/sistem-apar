<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Daftar Pelanggan</h2>
                <p class="text-sm text-gray-500 font-medium">Pencatatan pelanggan lama (existing) dan pelanggan baru untuk operasional</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-pelanggan-modal'))" class="px-8 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Input Data Pelanggan
            </button>
        </div>
    </x-slot>

    @php
        $totalPelanggan = \App\Models\Pelanggan::count();
        $totalAktif = \App\Models\Pelanggan::whereHas('pesanan', fn ($q) => $q->whereIn('status', ['diproses','selesai','selesai final','ditugaskan ke teknisi','dikerjakan teknisi','selesai oleh teknisi','dikonfirmasi admin']))->count();
        $totalTransaksi = \App\Models\Pesanan::count();
    @endphp

    <div class="space-y-8" x-data="{ search: '', statusTab: 'all', openModal: {{ $errors->any() ? 'true' : 'false' }} }" @open-pelanggan-modal.window="openModal = true">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Pelanggan</p>
                        <p class="text-5xl font-black text-slate-900">{{ $totalPelanggan }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center shadow-lg shadow-red-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pelanggan Aktif</p>
                        <p class="text-5xl font-black text-emerald-700">{{ $totalAktif }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Transaksi</p>
                        <p class="text-5xl font-black text-blue-700">{{ $totalTransaksi }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 md:col-span-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Akses Monitoring</p>
                        <p class="text-sm font-bold text-slate-700 mt-1 leading-relaxed">Seluruh pelanggan menggunakan nomor WhatsApp untuk cek APAR tanpa login.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white/60 overflow-hidden">
            {{-- Filter bar --}}
            <form method="GET" class="p-6 flex flex-col sm:flex-row gap-3 bg-slate-50/60 backdrop-blur-sm border-b border-gray-100/70">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WhatsApp, atau perusahaan..."
                        class="w-full pl-11 pr-5 py-3.5 bg-white rounded-2xl border border-gray-200 text-sm font-medium focus:border-red-400 focus:ring-1 focus:ring-red-400 shadow-sm transition" />
                </div>
                @if(request('search'))
                    <a href="{{ route('admin.pelanggan.index') }}" class="px-6 py-3.5 text-sm font-bold text-red-600 hover:bg-red-50 rounded-2xl transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Reset
                    </a>
                @endif
            </form>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[860px] text-left">
                    <thead class="bg-slate-50/80 backdrop-blur-sm border-b border-gray-100/70">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">WhatsApp</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alamat</th>
                            
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right sticky-action-col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/70">
                        @foreach($pelanggans as $p)
                            <tr class="hover:bg-red-50/30 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-xs shadow-lg shadow-red-500/20">
                                            {{ strtoupper(substr($p->nama, 0, 2)) }}
                                        </div>
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-red-700 transition">{{ $p->nama }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D+/', '', (string) $p->no_wa)) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                        {{ $p->no_wa }}
                                    </a>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-medium text-slate-500 truncate max-w-xs">{{ $p->alamat }}</p>
                                </td>
                                <td class="px-8 py-6 text-right sticky-action-cell whitespace-nowrap">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('admin.pelanggan.edit', $p) }}" class="inline-flex h-11 w-11 shrink-0 items-center justify-center bg-white text-blue-600 hover:bg-blue-50 rounded-xl border border-blue-100 hover:border-blue-200 hover:shadow-lg transition-all shadow-sm" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </a>
                                        <form action="{{ route('admin.pelanggan.destroy', $p) }}" method="POST" class="inline shrink-0" data-confirm="Yakin ingin menghapus pelanggan ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-11 w-11 shrink-0 items-center justify-center bg-white text-red-600 hover:bg-red-50 rounded-xl border border-red-100 hover:border-red-200 hover:shadow-lg transition-all shadow-sm" title="Hapus">
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

            <div class="block divide-y divide-gray-100/80 md:hidden">
                @forelse($pelanggans as $p)
                    @php
                        $waDigits = preg_replace('/\D+/', '', (string) $p->no_wa);
                        $waUrl = 'https://wa.me/' . preg_replace('/^0/', '62', $waDigits);
                    @endphp
                    <article class="p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-sm font-black text-white shadow-lg shadow-red-500/20">
                                {{ strtoupper(substr($p->nama, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-base font-black text-slate-900">{{ $p->nama }}</h3>
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-2 text-sm font-bold text-blue-600">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    {{ $p->no_wa }}
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                            <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-700">{{ $p->alamat ?: '-' }}</p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.pelanggan.edit', $p) }}" class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl border border-blue-100 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-widest text-blue-700 shadow-sm transition hover:bg-blue-50">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit</span>
                            </a>
                            <form action="{{ route('admin.pelanggan.destroy', $p) }}" method="POST" data-confirm="Yakin ingin menghapus pelanggan ini?" data-confirm-title="Konfirmasi Hapus" data-confirm-button="Ya, Hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl border border-red-100 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-widest text-red-700 shadow-sm transition hover:bg-red-50">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center text-sm font-semibold text-slate-500">
                        Belum ada data pelanggan.
                    </div>
                @endforelse
            </div>

            @if($pelanggans->hasPages())
                <div class="px-8 py-5 border-t border-gray-100/70 bg-slate-50/40">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </div>

        <div x-show="openModal" x-cloak id="pelanggan-modal-overlay" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openModal = false"></div>
            <div
                x-show="openModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Input Data Pelanggan</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Gunakan form ini untuk mencatat pelanggan lama (existing) maupun pelanggan baru.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.pelanggan.store') }}" method="POST" class="p-8 sm:p-10" id="pelanggan-modal-form">
                    @csrf

                    <div class="rounded-[1.6rem] border border-gray-100 p-6 sm:p-8 bg-gray-50/40 mb-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-red-600 text-white text-sm font-black flex items-center justify-center">1</div>
                            <p class="text-lg font-black text-gray-900 uppercase tracking-wide">Informasi Pelanggan</p>
                        </div>
                        <div class="border-t border-gray-200 mb-6"></div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Contoh: Budi Santoso">
                                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                            </div>
                            
                            <div>
                                <label for="no_wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" name="no_wa" id="no_wa" value="{{ old('no_wa') }}" required
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="08xxxxxxxxxx">
                                <x-input-error :messages="$errors->get('no_wa')" class="mt-2" />
                            </div>
                            <div>
                                <label for="pelanggan-modal-provinsi" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Provinsi</label>
                                <input type="text" name="alamat_provinsi" id="pelanggan-modal-provinsi" value="{{ old('alamat_provinsi') }}"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Provinsi">
                                <x-input-error :messages="$errors->get('alamat_provinsi')" class="mt-2" />
                            </div>
                            <div>
                                <label for="pelanggan-modal-kota" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kota / Kabupaten</label>
                                <input type="text" name="alamat_kota" id="pelanggan-modal-kota" value="{{ old('alamat_kota') }}"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Kota / Kabupaten">
                                <x-input-error :messages="$errors->get('alamat_kota')" class="mt-2" />
                            </div>
                            <div>
                                <label for="pelanggan-modal-kecamatan" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kecamatan</label>
                                <input type="text" name="alamat_kecamatan" id="pelanggan-modal-kecamatan" value="{{ old('alamat_kecamatan') }}"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Kecamatan">
                                <x-input-error :messages="$errors->get('alamat_kecamatan')" class="mt-2" />
                            </div>
                            <div>
                                <label for="pelanggan-modal-kode-pos" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Kode Pos</label>
                                <input type="text" name="alamat_kode_pos" id="pelanggan-modal-kode-pos" value="{{ old('alamat_kode_pos') }}"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                    placeholder="Kode Pos">
                                <x-input-error :messages="$errors->get('alamat_kode_pos')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat via OpenStreetMap <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" name="alamat_maps" id="pelanggan-modal-alamat-maps" value="{{ old('alamat_maps') }}" required
                                        class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                        placeholder="Cari alamat (OpenStreetMap)...">
                                    <div id="pelanggan-modal-suggestions" class="hidden absolute z-30 top-full mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden">
                                    </div>
                                </div>
                                <p id="pelanggan-modal-helper" class="text-[10px] font-semibold mt-2 text-gray-500">Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.</p>
                                <x-input-error :messages="$errors->get('alamat_maps')" class="mt-2" />
                                <x-input-error :messages="collect($errors->get('alamat_lat'))->merge($errors->get('alamat_lng'))->unique()->values()->all()" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Konfirmasi Titik Lokasi</label>
                            <div id="pelanggan-modal-map" class="w-full rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm bg-gray-100" style="height:280px;"></div>
                            <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-1">
                                <div class="flex gap-5">
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Latitude</span>
                                        <span id="pelanggan-modal-lat-display" class="text-xs font-mono font-black text-gray-800">{{ old('alamat_lat', '-') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Longitude</span>
                                        <span id="pelanggan-modal-lng-display" class="text-xs font-mono font-black text-gray-800">{{ old('alamat_lng', '-') }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-2 rounded-xl w-fit">
                                    Geser pin atau klik peta untuk koreksi titik
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="alamat_detail" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Detail Alamat <span class="text-red-500">*</span></label>
                            <textarea name="alamat_detail" id="alamat_detail" rows="2" required
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition resize-none"
                                placeholder="Contoh: Blok A2 No.10, patokan dekat minimarket, lantai 2.">{{ old('alamat_detail') }}</textarea>
                            <x-input-error :messages="$errors->get('alamat_detail')" class="mt-2" />
                        </div>
                    </div>

                    <input type="hidden" name="sumber_data" value="{{ old('sumber_data', 'manual') }}">
                    <input type="hidden" name="kategori_pelanggan" value="{{ old('kategori_pelanggan', 'lama') }}">
                    <input type="hidden" name="alamat_lat" id="pelanggan-modal-lat" value="{{ old('alamat_lat') }}">
                    <input type="hidden" name="alamat_lng" id="pelanggan-modal-lng" value="{{ old('alamat_lng') }}">
                    <input type="hidden" name="alamat" id="pelanggan-modal-alamat-combined" value="{{ old('alamat') }}">

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                        <button type="button" @click="openModal = false" class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition">Batal</button>
                        <button type="submit" id="pelanggan-modal-submit" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Pelanggan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    (function() {
        const ADDRESS_SUGGEST_URL = '{{ route('order.address.suggest') }}';
        let map = null;
        let marker = null;
        let timer = null;
        let suggestionItems = [];

        function getEls() {
            return {
                form: document.getElementById('pelanggan-modal-form'),
                overlay: document.getElementById('pelanggan-modal-overlay'),
                alamatMaps: document.getElementById('pelanggan-modal-alamat-maps'),
                suggestionsEl: document.getElementById('pelanggan-modal-suggestions'),
                helperEl: document.getElementById('pelanggan-modal-helper'),
                mapEl: document.getElementById('pelanggan-modal-map'),
                latInput: document.getElementById('pelanggan-modal-lat'),
                lngInput: document.getElementById('pelanggan-modal-lng'),
                latDisplay: document.getElementById('pelanggan-modal-lat-display'),
                lngDisplay: document.getElementById('pelanggan-modal-lng-display'),
                provinsiInput: document.getElementById('pelanggan-modal-provinsi'),
                kotaInput: document.getElementById('pelanggan-modal-kota'),
                kecamatanInput: document.getElementById('pelanggan-modal-kecamatan'),
                kodePosInput: document.getElementById('pelanggan-modal-kode-pos'),
                combinedInput: document.getElementById('pelanggan-modal-alamat-combined'),
                detailInput: document.getElementById('alamat_detail'),
                submitButton: document.getElementById('pelanggan-modal-submit'),
            };
        }

        function hasCoreEls(els) {
            return !!(els.form && els.overlay && els.alamatMaps && els.suggestionsEl && els.helperEl && els.mapEl && els.latInput && els.lngInput && els.latDisplay && els.lngDisplay && els.provinsiInput && els.kotaInput && els.kecamatanInput && els.kodePosInput && els.combinedInput && els.submitButton);
        }

        function updateCombined() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            const maps = String(els.alamatMaps.value || '').trim();
            const detail = String(els.detailInput?.value || '').trim();
            els.combinedInput.value = [maps, detail].filter(Boolean).join(' | Detail: ');
        }

        function updateSubmitState() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            els.submitButton.disabled = !(String(els.alamatMaps.value || '').trim() && String(els.latInput.value || '').trim() && String(els.lngInput.value || '').trim());
        }

        function updateHelper(text, tone) {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            els.helperEl.textContent = text;
            els.helperEl.className = 'text-[10px] font-semibold mt-2 ';
            if (tone === 'success') els.helperEl.className += 'text-emerald-600';
            else if (tone === 'warning') els.helperEl.className += 'text-amber-600';
            else if (tone === 'error') els.helperEl.className += 'text-red-600';
            else if (tone === 'info') els.helperEl.className += 'text-blue-600';
            else els.helperEl.className += 'text-gray-500';
        }

        function updateCoord(lat, lng) {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            const latFixed = Number(lat).toFixed(8);
            const lngFixed = Number(lng).toFixed(8);
            els.latInput.value = latFixed;
            els.lngInput.value = lngFixed;
            els.latDisplay.textContent = latFixed;
            els.lngDisplay.textContent = lngFixed;
            updateCombined();
            updateSubmitState();
        }

        function clearCoord() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            els.latInput.value = '';
            els.lngInput.value = '';
            els.latDisplay.textContent = '-';
            els.lngDisplay.textContent = '-';
            updateSubmitState();
        }

        function clearStructuredAddress() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            els.provinsiInput.value = '';
            els.kotaInput.value = '';
            els.kecamatanInput.value = '';
            els.kodePosInput.value = '';
        }

        function hideSuggestions() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            els.suggestionsEl.classList.add('hidden');
            els.suggestionsEl.innerHTML = '';
            suggestionItems = [];
        }

        function placeMarker(lat, lng) {
        if (!map) return;
    const markerIcon = L.divIcon({
        className: 'custom-leaflet-marker',
        html: `
            <div style="position: relative; width: 34px; height: 34px;">
                <div style="position:absolute; inset:0; background:#ef4444; border-radius:9999px; border:4px solid #fff; box-shadow:0 10px 20px rgba(239,68,68,.28);"></div>
                <div style="position:absolute; left:11px; top:11px; width:6px; height:6px; border-radius:9999px; background:#fff;"></div>
                <div style="position:absolute; left:13px; bottom:-8px; width:0; height:0; border-left:4px solid transparent; border-right:4px solid transparent; border-top:10px solid #ef4444;"></div>
            </div>
        `,
        iconSize: [34, 42],
        iconAnchor: [17, 38],
    });
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], {icon: markerIcon, draggable: true}).addTo(map);
            marker.on('drag', function(e) {
                updateCoord(e.latlng.lat, e.latlng.lng);
            });
            marker.on('dragend', function(e) {
                updateCoord(e.latlng.lat, e.latlng.lng);
            });
        }
    }

        function refreshMap() {
            if (!map) return;
            setTimeout(() => map.invalidateSize(), 300);
        }

        function ensureMap() {
            const els = getEls();
            if (!hasCoreEls(els) || map) return;

            const oldLat = Number(els.latInput.value || 0);
            const oldLng = Number(els.lngInput.value || 0);
            const startLat = oldLat || -6.2088;
            const startLng = oldLng || 106.8456;
            const startZoom = oldLat && oldLng ? 17 : 13;

            map = L.map(els.mapEl, {
                center: [startLat, startLng],
                zoom: startZoom,
                scrollWheelZoom: false,
                zoomControl: true,
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

            map.on('click', (event) => {
                const lat = event.latlng.lat;
                const lng = event.latlng.lng;
                const currentEls = getEls();
                if (!hasCoreEls(currentEls)) return;

                if (!String(currentEls.alamatMaps.value || '').trim()) {
                    updateHelper('Pilih alamat dari saran OpenStreetMap terlebih dahulu, lalu koreksi titik dari peta bila perlu.', 'warning');
                    return;
                }

                placeMarker(lat, lng);
                updateCoord(lat, lng);
                updateHelper('Lokasi dipilih. Geser pin merah jika perlu koreksi.', 'success');
            });

            if (oldLat && oldLng) {
                placeMarker(oldLat, oldLng);
                updateCoord(oldLat, oldLng);
            } else {
                updateSubmitState();
            }
        }

        function renderSuggestions(items) {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            if (!items.length) {
                hideSuggestions();
                updateHelper('Alamat tidak ditemukan. Coba kata kunci lain.', 'warning');
                return;
            }

            suggestionItems = items;
            els.suggestionsEl.innerHTML = '';

            items.forEach((item, idx) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.idx = String(idx);
                button.className = 'w-full text-left px-5 py-3.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors border-b border-gray-50 last:border-0';
                button.textContent = item.display_name || '';
                els.suggestionsEl.appendChild(button);
            });

            els.suggestionsEl.classList.remove('hidden');
            updateHelper('Pilih salah satu saran alamat agar titik koordinat tersimpan.', 'info');
        }

        async function fetchSuggestions(query) {
            try {
                const res = await fetch(`${ADDRESS_SUGGEST_URL}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();
                renderSuggestions((res.ok && data.success) ? (data.data || []) : []);
            } catch {
                hideSuggestions();
                updateHelper('Gagal mengambil saran alamat. Coba lagi.', 'error');
            }
        }

        function selectSuggestion(index) {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            const item = suggestionItems[index];
            if (!item) return;

            els.alamatMaps.value = item.display_name || '';
            els.provinsiInput.value = item.provinsi || '';
            els.kotaInput.value = item.kota || '';
            els.kecamatanInput.value = item.kecamatan || '';
            els.kodePosInput.value = item.kode_pos || '';

            const lat = Number(item.lat || 0);
            const lng = Number(item.lng || item.lon || 0);
            if (lat && lng) {
                ensureMap();
                updateCoord(lat, lng);
                if (map) {
                    map.setView([lat, lng], 17);
                    placeMarker(lat, lng);
                    refreshMap();
                }
            }

            hideSuggestions();
            updateCombined();
            updateHelper('Lokasi dipilih. Geser pin merah jika perlu koreksi.', 'success');
        }

        function scheduleSuggest() {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            clearCoord();
            clearStructuredAddress();
            if (marker && map) {
                window.AppMapLibre.removeMarker(marker);
                marker = null;
            }

            if (timer) clearTimeout(timer);
            const query = String(els.alamatMaps.value || '').trim();
            updateCombined();

            if (query.length < 3) {
                hideSuggestions();
                updateHelper('Ketik minimal 3 huruf, lalu pilih saran alamat OpenStreetMap agar titik koordinat terkunci.', 'default');
                return;
            }

            timer = setTimeout(() => fetchSuggestions(query), 350);
        }

        document.addEventListener('input', (event) => {
            if (event.target && event.target.id === 'pelanggan-modal-alamat-maps') {
                scheduleSuggest();
                return;
            }

            if (event.target && event.target.id === 'alamat_detail') {
                updateCombined();
            }
        });

        document.addEventListener('focusin', (event) => {
            if (!event.target || event.target.id !== 'pelanggan-modal-alamat-maps') return;

            const els = getEls();
            if (!hasCoreEls(els)) return;

            const query = String(els.alamatMaps.value || '').trim();
            if (query.length >= 3) {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => fetchSuggestions(query), 200);
            }
        });

        document.addEventListener('focusout', (event) => {
            if (event.target && event.target.id === 'pelanggan-modal-alamat-maps') {
                setTimeout(hideSuggestions, 180);
            }
        });

        document.addEventListener('click', (event) => {
            const els = getEls();
            if (!hasCoreEls(els)) return;

            const button = event.target.closest('button[data-idx]');
            if (button && els.suggestionsEl.contains(button)) {
                selectSuggestion(Number(button.dataset.idx));
                return;
            }

            if (!els.suggestionsEl.contains(event.target) && event.target !== els.alamatMaps) {
                hideSuggestions();
            }
        });

        document.addEventListener('submit', (event) => {
            if (!event.target || event.target.id !== 'pelanggan-modal-form') return;

            const els = getEls();
            if (!hasCoreEls(els)) return;

            updateCombined();
            if (!String(els.alamatMaps.value || '').trim()) {
                event.preventDefault();
                updateHelper('Silakan pilih alamat dari saran OpenStreetMap terlebih dahulu.', 'error');
                return;
            }

            if (!String(els.latInput.value || '').trim() || !String(els.lngInput.value || '').trim()) {
                event.preventDefault();
                updateHelper('Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.', 'error');
            }
        });

        window.addEventListener('open-pelanggan-modal', () => {
            setTimeout(() => {
                ensureMap();
                refreshMap();
            }, 400);
        });

        const initialEls = getEls();
        const overlay = initialEls.overlay;
        if (overlay) {
            const observer = new MutationObserver(() => {
                const visible = window.getComputedStyle(overlay).display !== 'none';
                if (visible) {
                    ensureMap();
                    refreshMap();
                }
            });
            observer.observe(overlay, { attributes: true, attributeFilter: ['style', 'class'] });
        }

        const waitMapLibre = (attempt = 0) => {
            if (window.AppMapLibre && window.maplibregl && typeof window.maplibregl.Map === 'function') {
                ensureMap();
                refreshMap();
                return;
            }

            if (attempt < 50) {
                setTimeout(() => waitMapLibre(attempt + 1), 100);
            }
        };

        updateCombined();
        updateSubmitState();
        waitMapLibre();
    })();
</script>
@endpush

</x-app-layout>
