<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Pembelian Barang & Stok</h2>
                <p class="text-sm text-gray-500 font-medium">{{ $periode }}</p>
            </div>
            <a href="{{ route('admin.laporan.pembelian.pdf', request()->query()) }}" class="inline-flex items-center justify-center px-6 py-3 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-xl shadow-red-700/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Cetak PDF
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        @include('admin.laporan.partials.tabs')

        <!-- Filter Form -->
        <form method="GET" class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 grid md:grid-cols-5 gap-4 items-end">
            <div>
                <label for="tanggal_dari" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Dari</label>
                <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ $filters['tanggal_dari'] }}"
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
            </div>
            <div>
                <label for="tanggal_sampai" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ $filters['tanggal_sampai'] }}"
                    class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
            </div>
            <div>
                <label for="supplier_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Supplier</label>
                <select name="supplier_id" id="supplier_id" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null) == $supplier->id)>{{ $supplier->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Status PO</label>
                <select name="status" id="status" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(($filters['status'] ?? null) === 'draft')>Draft</option>
                    <option value="dikirim" @selected(($filters['status'] ?? null) === 'dikirim')>Dikirim</option>
                    <option value="diterima" @selected(($filters['status'] ?? null) === 'diterima')>Diterima</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition uppercase tracking-widest text-xs">
                    Filter
                </button>
                <a href="{{ route('admin.laporan.pembelian') }}" class="flex-1 px-6 py-4 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-xs text-center">
                    Reset
                </a>
            </div>
        </form>

        <!-- KPI Cards -->
        <div class="grid md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Nominal Pembelian</p>
                <p class="text-3xl font-black text-red-700">Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-gray-500">Total nilai belanja PO</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Transaksi PO</p>
                <p class="text-4xl font-black text-gray-900">{{ $stats['total_po'] }}</p>
                <p class="text-xs font-semibold text-gray-500">{{ $stats['diterima_count'] }} diterima, {{ $stats['dikirim_count'] }} dikirim, {{ $stats['draft_count'] }} draft</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total APAR Dibeli</p>
                <p class="text-4xl font-black text-blue-700">{{ number_format($stats['total_produk'], 0, ',', '.') }}</p>
                <p class="text-xs font-semibold text-gray-500">Unit APAR masuk ke stok</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 space-y-2">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Refill & Peralatan</p>
                <p class="text-2xl font-black text-emerald-700">{{ number_format($stats['total_refill'], 0, ',', '.') }} Kg / {{ number_format($stats['total_peralatan'], 0, ',', '.') }} Unit</p>
                <p class="text-xs font-semibold text-gray-500">Media refill & peralatan service</p>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">No PO & Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">No Surat Jalan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Rincian Item</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($purchaseOrders as $po)
                            @php
                                $statusClass = match ($po->status) {
                                    'diterima' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'dikirim' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                    default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/40 transition">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900">{{ $po->nomor_po }}</p>
                                    <p class="text-xs font-semibold text-gray-500">{{ $po->tanggal ? $po->tanggal->format('d M Y') : $po->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $po->supplier?->nama_supplier ?? '-' }}</p>
                                    <p class="text-xs font-semibold text-gray-500">{{ $po->supplier?->telepon ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">
                                    {{ $po->no_surat_jalan ?: '-' }}
                                </td>
                                <td class="px-8 py-6">
                                    <ul class="text-xs font-semibold text-gray-700 space-y-1">
                                        @foreach($po->details as $item)
                                            <li>• {{ $item->nama_item }} ({{ $item->jumlah }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusClass }}">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-900">
                                    Rp {{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-sm font-medium text-gray-500">
                                    Belum ada data pembelian (Purchase Order) sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
