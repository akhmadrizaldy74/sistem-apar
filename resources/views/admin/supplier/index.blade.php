<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Supplier</h2>
                <p class="text-base font-semibold leading-7 text-slate-500">Kelola data supplier untuk pengadaan dan Purchase Order (PO).</p>
            </div>
            <a href="{{ route('admin.suppliers.create') }}" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Supplier
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Search & Filter Card -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center flex-1">
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kontak, WA, email..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 transition outline-none" />
                    </div>

                    <select name="status" onchange="this.form.submit()" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 rounded-2xl transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Reset Filter
                        </a>
                    @endif
                </div>

                <button type="submit" class="px-6 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition text-xs tracking-wider uppercase">
                    Cari
                </button>
            </form>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center w-16">No</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Nama Supplier</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Kontak Person</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">No. Telepon</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">No. WhatsApp</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Email</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-right pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suppliers as $index => $supplier)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5 text-sm font-bold text-slate-400 text-center">
                                    {{ $suppliers->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-base font-black text-slate-900">{{ $supplier->nama_supplier }}</p>
                                    @if($supplier->alamat)
                                        <p class="text-xs text-slate-400 font-medium line-clamp-1 mt-0.5" title="{{ $supplier->alamat }}">{{ $supplier->alamat }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-700">
                                    {{ $supplier->kontak_person ?: '-' }}
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-700">
                                    {{ $supplier->no_telepon ?: '-' }}
                                </td>
                                <td class="px-6 py-5">
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $supplier->no_wa) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold hover:bg-emerald-100 transition">
                                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.14 4.162 4.162-1.144z"/></svg>
                                        {{ $supplier->no_wa }}
                                    </a>
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-700">
                                    {{ $supplier->email ?: '-' }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($supplier->status === 'aktif')
                                        <span class="inline-flex items-center px-4 py-1.5 bg-emerald-50 text-emerald-700 text-[11px] font-black uppercase tracking-widest rounded-full">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-4 py-1.5 bg-slate-100 text-slate-600 text-[11px] font-black uppercase tracking-widest rounded-full">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right pr-8">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 hover:text-slate-900 transition" title="Edit Supplier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>

                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus supplier {{ $supplier->nama_supplier }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition" title="Hapus Supplier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0v-4a1 1 0 011-1h2a1 1 0 011 1v4m-6 0h6" /></svg>
                                        <p class="text-base font-bold text-slate-600">Belum Ada Data Supplier</p>
                                        <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Supplier" untuk menambahkan data baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
