<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Manajemen Pembelian</h2>
                <p class="text-base font-semibold leading-7 text-slate-500">Kelola pemesanan barang (PO) ke supplier, surat jalan, dan penerimaan stok.</p>
            </div>
            <a href="{{ route('admin.purchase-orders.create') }}" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 flex items-center gap-2 uppercase tracking-widest text-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Pembelian
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pembelian -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-2">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pembelian</p>
                <p class="text-2xl font-black text-slate-900">Rp {{ number_format($stats['total_pembelian'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-slate-500">{{ $stats['total_transaksi'] ?? 0 }} transaksi tercatat</p>
            </div>

            <!-- Pembelian APAR -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-2">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pembelian APAR</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_apar'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-slate-500">Unit APAR yang masuk ke stok</p>
            </div>

            <!-- Pembelian Refill -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-2">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pembelian Refill</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_refill'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-slate-500">Akumulasi kuantitas media refill</p>
            </div>

            <!-- Pembelian Peralatan -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-2">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pembelian Peralatan</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total_peralatan'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-slate-500">Akumulasi unit peralatan</p>
            </div>
        </div>

        <!-- Search & Filter Card -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('admin.purchase-orders.index') }}" class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center flex-1">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No PO, Surat Jalan, Supplier..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 transition outline-none" />
                    </div>

                    <!-- Filter Status -->
                    <select name="status" onchange="this.form.submit()" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="dikirim" {{ request('status') === 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                        <option value="diterima" {{ request('status') === 'diterima' ? 'selected' : '' }}>Diterima</option>
                    </select>

                    <!-- Filter Supplier -->
                    <select name="supplier_id" onchange="this.form.submit()" class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-red-500 outline-none">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                        @endforeach
                    </select>

                    @if(request('search') || request('status') || request('supplier_id'))
                        <a href="{{ route('admin.purchase-orders.index') }}" class="px-4 py-3 text-xs font-bold text-red-600 hover:bg-red-50 rounded-2xl transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            Reset
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
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">No. PO</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Supplier</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">Jumlah Item</th>
                            <th class="px-6 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-right pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchaseOrders as $po)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="text-base font-black text-red-600 hover:underline">
                                        {{ $po->nomor_po }}
                                    </a>
                                    @if($po->no_surat_jalan)
                                        <p class="text-xs text-slate-400 font-semibold mt-0.5">SJ: {{ $po->no_surat_jalan }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-900">{{ $po->supplier?->nama_supplier ?? '-' }}</p>
                                    @if($po->supplier?->no_wa)
                                        <p class="text-xs text-slate-400 font-medium">WA: {{ $po->supplier->no_wa }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-sm font-semibold text-slate-700">
                                    {{ $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-5 text-sm font-black text-slate-900">
                                    Rp {{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($po->status === 'draft')
                                        <span class="inline-flex items-center px-4 py-1.5 bg-slate-100 text-slate-600 border border-slate-200 text-[11px] font-black uppercase tracking-widest rounded-full">
                                            Draft
                                        </span>
                                    @elseif($po->status === 'dikirim')
                                        <span class="inline-flex items-center px-4 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-black uppercase tracking-widest rounded-full">
                                            Dikirim
                                        </span>
                                    @elseif($po->status === 'diterima')
                                        <span class="inline-flex items-center px-4 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-black uppercase tracking-widest rounded-full">
                                            Diterima
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center text-sm font-bold text-slate-700">
                                    {{ $po->details->count() }} item
                                </td>
                                <td class="px-6 py-5 text-right pr-8">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Detail Button -->
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 hover:text-slate-900 transition" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        @if($po->status === 'draft')
                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.purchase-orders.edit', $po->id) }}" class="p-2.5 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition" title="Edit PO">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>

                                            <!-- Hapus Button -->
                                            <form action="{{ route('admin.purchase-orders.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus Purchase Order {{ $po->nomor_po }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition" title="Hapus PO">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <p class="text-base font-bold text-slate-600">Belum Ada Purchase Order</p>
                                        <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah PO" untuk membuat pesanan pembelian baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
                <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
