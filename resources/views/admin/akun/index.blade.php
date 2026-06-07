<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen Akun</h2>
                <p class="text-sm text-gray-500 font-medium">Kelola akun admin, teknisi, dan pelanggan yang menggunakan sistem.</p>
            </div>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-tambah-akun'))" class="px-8 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Tambah Akun
            </button>
        </div>
    </x-slot>

    <div class="space-y-8"
         x-data="{
            openTambah: {{ $errors->any() && !old('_edit_user_id') ? 'true' : 'false' }},
            openEdit: {{ $errors->any() && old('_edit_user_id') ? 'true' : 'false' }},
            editUser: {
                id: '{{ old('_edit_user_id', '') }}',
                name: '{{ old('name', '') }}',
                email: '{{ old('email', '') }}',
                no_telpon: '{{ old('no_telpon', '') }}',
                role: '{{ old('role', '') }}'
            },
            tambahRole: '{{ old('role', 'admin') }}',
            editRole: '{{ old('role', '') }}'
         }"
         @open-tambah-akun.window="openTambah = true"
         @open-edit-akun.window="
            editUser = $event.detail;
            editRole = $event.detail.role;
            openEdit = true;
         ">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Total Admin --}}
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Admin</p>
                        <p class="text-5xl font-black text-slate-900">{{ $totalAdmin }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center shadow-lg shadow-red-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                </div>
            </div>
            {{-- Total Teknisi --}}
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Teknisi</p>
                        <p class="text-5xl font-black text-blue-700">{{ $totalTeknisi }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
            </div>
            {{-- Total Pelanggan --}}
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/60 p-8 shadow-xl shadow-slate-200/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Total Pelanggan</p>
                        <p class="text-5xl font-black text-emerald-700">{{ $totalPelanggan }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white/80 backdrop-blur-md rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white/60 overflow-hidden">
            {{-- Filter bar --}}
            <form method="GET" class="p-6 flex flex-col sm:flex-row gap-3 bg-slate-50/60 backdrop-blur-sm border-b border-gray-100/70">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, WhatsApp, atau role..."
                        class="w-full pl-11 pr-5 py-3.5 bg-white rounded-2xl border border-gray-200 text-sm font-medium focus:border-red-400 focus:ring-1 focus:ring-red-400 shadow-sm transition" />
                </div>
                <select name="role" onchange="this.form.submit()" class="px-5 py-3.5 bg-white rounded-2xl border border-gray-200 text-sm font-bold text-slate-700 focus:border-red-400 focus:ring-1 focus:ring-red-400 shadow-sm transition min-w-[160px]">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="teknisi" {{ request('role') === 'teknisi' ? 'selected' : '' }}>Teknisi</option>
                    <option value="pelanggan" {{ request('role') === 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                </select>
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.akun.index') }}" class="px-6 py-3.5 text-sm font-bold text-red-600 hover:bg-red-50 rounded-2xl transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Reset
                    </a>
                @endif
            </form>

            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="bg-slate-50/80 backdrop-blur-sm border-b border-gray-100/70">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">WhatsApp / HP</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Role</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Terdaftar</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/70">
                        @forelse($users as $u)
                            @php
                                $displayName = $u->name;
                                if ($u->role === 'pelanggan' && $u->pelanggan) {
                                    $displayName = $u->pelanggan->nama ?: $u->name;
                                }
                                $displayEmail = $u->email;
                                $displayPhone = $u->pelanggan?->no_wa ?: $u->no_telpon;
                                $roleBadge = match($u->role) {
                                    'admin' => 'bg-red-50 text-red-700 border-red-100',
                                    'teknisi' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'pelanggan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    default => 'bg-gray-50 text-gray-700 border-gray-100',
                                };
                                $roleLabel = ucfirst($u->role);
                                $initials = strtoupper(substr($displayName, 0, 2));
                                $avatarGradient = match($u->role) {
                                    'admin' => 'from-red-500 to-red-700',
                                    'teknisi' => 'from-blue-500 to-blue-700',
                                    'pelanggan' => 'from-emerald-500 to-emerald-700',
                                    default => 'from-slate-500 to-slate-700',
                                };
                                $avatarShadow = match($u->role) {
                                    'admin' => 'shadow-red-500/20',
                                    'teknisi' => 'shadow-blue-500/20',
                                    'pelanggan' => 'shadow-emerald-500/20',
                                    default => 'shadow-slate-500/20',
                                };
                            @endphp
                            <tr class="hover:bg-red-50/30 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $avatarGradient }} text-white flex items-center justify-center font-black text-xs shadow-lg {{ $avatarShadow }}">
                                            {{ $initials }}
                                        </div>
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-red-700 transition">{{ $displayName }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($displayEmail)
                                        <p class="text-xs font-bold text-slate-600">{{ $displayEmail }}</p>
                                    @else
                                        <p class="text-xs font-semibold text-slate-400">Belum ada email</p>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-bold text-slate-600">{{ $displayPhone ?: '-' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border {{ $roleBadge }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-xs font-medium text-slate-500">{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</p>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <button type="button"
                                            @click="$dispatch('open-edit-akun', {
                                                id: '{{ $u->id }}',
                                                name: '{{ addslashes($u->name) }}',
                                                email: '{{ addslashes($u->email ?? '') }}',
                                                no_telpon: '{{ addslashes($u->no_telpon ?? ($u->pelanggan?->no_wa ?? '')) }}',
                                                role: '{{ $u->role }}'
                                            })"
                                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center bg-white text-blue-600 hover:bg-blue-50 rounded-xl border border-blue-100 hover:border-blue-200 hover:shadow-lg transition-all shadow-sm" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        <form action="{{ route('admin.akun.destroy', $u) }}" method="POST" class="inline shrink-0"
                                            data-confirm="{{ (int)$u->id === (int)auth()->id() ? 'Anda tidak dapat menghapus akun yang sedang login.' : 'Yakin ingin menghapus akun ini?' }}"
                                            data-confirm-title="Konfirmasi Hapus"
                                            data-confirm-button="Ya, Hapus"
                                            @if((int)$u->id === (int)auth()->id()) onsubmit="event.preventDefault(); window.aparAlert({icon:'error',title:'Gagal',text:'Anda tidak dapat menghapus akun yang sedang login.'});" @endif>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-11 w-11 shrink-0 items-center justify-center bg-white text-red-600 hover:bg-red-50 rounded-xl border border-red-100 hover:border-red-200 hover:shadow-lg transition-all shadow-sm" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-semibold text-slate-500">
                                    Belum ada data akun.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="block divide-y divide-gray-100/80 md:hidden">
                @forelse($users as $u)
                    @php
                        $displayName = $u->name;
                        if ($u->role === 'pelanggan' && $u->pelanggan) {
                            $displayName = $u->pelanggan->nama ?: $u->name;
                        }
                        $displayEmail = $u->email;
                        $displayPhone = $u->pelanggan?->no_wa ?: $u->no_telpon;
                        $roleBadge = match($u->role) {
                            'admin' => 'bg-red-50 text-red-700 border-red-100',
                            'teknisi' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'pelanggan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            default => 'bg-gray-50 text-gray-700 border-gray-100',
                        };
                        $roleLabel = ucfirst($u->role);
                        $initials = strtoupper(substr($displayName, 0, 2));
                        $avatarGradient = match($u->role) {
                            'admin' => 'from-red-500 to-red-700',
                            'teknisi' => 'from-blue-500 to-blue-700',
                            'pelanggan' => 'from-emerald-500 to-emerald-700',
                            default => 'from-slate-500 to-slate-700',
                        };
                    @endphp
                    <article class="p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $avatarGradient }} text-sm font-black text-white shadow-lg">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="truncate text-base font-black text-slate-900">{{ $displayName }}</h3>
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $roleBadge }} shrink-0">{{ $roleLabel }}</span>
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-500">{{ $displayPhone ?: '-' }}</p>
                                <p class="mt-1 text-xs {{ $displayEmail ? 'text-slate-500' : 'text-slate-400' }}">{{ $displayEmail ?: 'Belum ada email' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">Terdaftar: {{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <button type="button"
                                @click="$dispatch('open-edit-akun', {
                                    id: '{{ $u->id }}',
                                    name: '{{ addslashes($u->name) }}',
                                    email: '{{ addslashes($u->email ?? '') }}',
                                    no_telpon: '{{ addslashes($u->no_telpon ?? ($u->pelanggan?->no_wa ?? '')) }}',
                                    role: '{{ $u->role }}'
                                })"
                                class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl border border-blue-100 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-widest text-blue-700 shadow-sm transition hover:bg-blue-50">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit</span>
                            </button>
                            <form action="{{ route('admin.akun.destroy', $u) }}" method="POST"
                                data-confirm="Yakin ingin menghapus akun ini?"
                                data-confirm-title="Konfirmasi Hapus"
                                data-confirm-button="Ya, Hapus">
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
                        Belum ada data akun.
                    </div>
                @endforelse
            </div>

            @if($users->hasPages())
                <div class="px-8 py-5 border-t border-gray-100/70 bg-slate-50/40">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- ===================== MODAL: TAMBAH AKUN ===================== --}}
        <div x-show="openTambah" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openTambah = false"></div>
            <div
                x-show="openTambah"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Tambah Akun</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Buat akun baru untuk admin, teknisi, atau pelanggan.</p>
                    </div>
                    <button type="button" @click="openTambah = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('admin.akun.store') }}" method="POST" class="p-8 sm:p-10">
                    @csrf

                    <div class="space-y-5">
                        {{-- Nama --}}
                        <div>
                            <label for="tambah-nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="tambah-nama" value="{{ old('name') }}" required
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Contoh: Budi Santoso">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="tambah-email" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Email</label>
                            <input type="email" name="email" id="tambah-email" value="{{ old('email') }}"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="email@contoh.com">
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label for="tambah-wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor WhatsApp</label>
                            <input type="text" name="no_telpon" id="tambah-wa" value="{{ old('no_telpon') }}"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="08xxxxxxxxxx">
                        </div>

                        {{-- Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="tambah-password" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" id="tambah-password" required
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="Min. 6 karakter">
                            </div>
                            <div>
                                <label for="tambah-password-confirm" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" id="tambah-password-confirm" required
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="Ulangi password">
                            </div>
                        </div>

                        {{-- Role --}}
                        <div>
                            <label for="tambah-role" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="tambah-role" required x-model="tambahRole"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="admin">Admin</option>
                                <option value="teknisi">Teknisi</option>
                                <option value="pelanggan">Pelanggan</option>
                            </select>
                        </div>

                        {{-- Alamat (only for pelanggan) --}}
                        <div x-show="tambahRole === 'pelanggan'" x-transition>
                            <label for="tambah-alamat" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Alamat</label>
                            <textarea name="alamat" id="tambah-alamat" rows="3"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition resize-none"
                                placeholder="Alamat lengkap pelanggan...">{{ old('alamat') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-6">
                        <button type="button" @click="openTambah = false" class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition">Batal</button>
                        <button type="submit" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== MODAL: EDIT AKUN ===================== --}}
        <div x-show="openEdit" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-950/50 backdrop-blur-sm" @click="openEdit = false"></div>
            <div
                x-show="openEdit"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-[2rem] bg-white shadow-2xl shadow-gray-900/20 border border-white/60"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between px-8 py-6 bg-white/95 backdrop-blur border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">Edit Akun</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Perbarui informasi akun.</p>
                    </div>
                    <button type="button" @click="openEdit = false" class="w-11 h-11 rounded-2xl bg-gray-50 text-gray-400 hover:text-red-700 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'{{ url('admin/akun') }}/' + editUser.id" method="POST" class="p-8 sm:p-10">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_edit_user_id" :value="editUser.id">

                    <div class="space-y-5">
                        {{-- Nama --}}
                        <div>
                            <label for="edit-nama" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit-nama" :value="editUser.name" required
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="Nama lengkap">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="edit-email" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Email</label>
                            <input type="email" name="email" id="edit-email" :value="editUser.email"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="email@contoh.com">
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label for="edit-wa" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Nomor WhatsApp</label>
                            <input type="text" name="no_telpon" id="edit-wa" :value="editUser.no_telpon"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 placeholder:text-gray-300 transition"
                                placeholder="08xxxxxxxxxx">
                        </div>

                        {{-- Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="edit-password" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Password Baru</label>
                                <input type="password" name="password" id="edit-password"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <div>
                                <label for="edit-password-confirm" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" id="edit-password-confirm"
                                    class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition"
                                    placeholder="Ulangi password baru">
                            </div>
                        </div>
                        <p class="text-[10px] font-semibold text-slate-400 -mt-3">Kosongkan kedua field di atas jika tidak ingin mengubah password.</p>

                        {{-- Role --}}
                        <div>
                            <label for="edit-role" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="edit-role" required x-model="editRole"
                                class="w-full px-5 py-4 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900 transition">
                                <option value="admin">Admin</option>
                                <option value="teknisi">Teknisi</option>
                                <option value="pelanggan">Pelanggan</option>
                            </select>
                        </div>

                        {{-- Warning for role change --}}
                        <div x-show="editUser.role === 'pelanggan' && editRole !== 'pelanggan'" x-transition class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                                <div>
                                    <p class="text-xs font-black text-amber-800">Perhatian</p>
                                    <p class="text-xs font-semibold text-amber-700 mt-1">Mengubah role dari Pelanggan ke role lain mungkin diblokir jika akun memiliki data transaksi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-6">
                        <button type="button" @click="openEdit = false" class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition">Batal</button>
                        <button type="submit" class="px-10 py-4 bg-gradient-to-r from-red-700 to-red-800 text-white font-black rounded-2xl hover:from-red-800 hover:to-red-900 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
