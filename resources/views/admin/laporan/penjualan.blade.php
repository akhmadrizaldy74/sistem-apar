<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Penjualan Barang & Refill</h2>
                <p class="text-sm text-gray-500 font-medium">{{ $periode }}</p>
            </div>
            <a href="{{ route('admin.laporan.penjualan.pdf', request()->query()) }}" class="inline-flex items-center justify-center px-6 py-3 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-xl shadow-red-700/20">
                Cetak PDF
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        @include('admin.laporan.partials.tabs')

        <form method="GET" class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 grid md:grid-cols-4 gap-4 items-end">
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
                <label for="pelanggan_id" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pelanggan</label>
                <select name="pelanggan_id" id="pelanggan_id" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
                    <option value="">Semua Pelanggan</option>
                    @foreach($pelanggans as $pelanggan)
                        <option value="{{ $pelanggan->id }}" @selected($filters['pelanggan_id'] === $pelanggan->id)>{{ $pelanggan->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition uppercase tracking-widest text-xs">
                    Filter
                </button>
                <a href="{{ route('admin.laporan.penjualan', []) }}" class="flex-1 px-6 py-4 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-xs text-center">
                    Reset
                </a>
            </div>
        </form>

        <div class="grid md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Transaksi Final</p>
                <p class="text-4xl font-black text-gray-900 mt-3">{{ $stats['total_transaksi'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Penjualan Produk</p>
                <p class="text-4xl font-black text-blue-700 mt-3">{{ $stats['produk_transaksi'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Refill</p>
                <p class="text-4xl font-black text-amber-700 mt-3">{{ $stats['refill_transaksi'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pembayaran Final</p>
                <p class="text-3xl font-black text-red-700 mt-3">Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Transaksi</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Item / Layanan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sumber</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50/40 transition">
                                <td class="px-8 py-6 text-sm font-bold text-gray-900">{{ $transaction['tanggal_label'] }}</td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-900">{{ $transaction['pelanggan'] }}</td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $transaction['jenis_transaksi'] === 'Penjualan Produk' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $transaction['jenis_transaksi'] }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-semibold text-gray-700">{{ $transaction['item'] }}</td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">{{ $transaction['jumlah'] }}</td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest">
                                        {{ $transaction['status'] }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">{{ $transaction['source'] }}</td>
                                <td class="px-8 py-6 text-sm font-black text-red-700">Rp {{ number_format($transaction['total'], 0, ',', '.') }}</td>
                                <td class="px-8 py-6 text-right">
                                    @if($transaction['detail_url'])
                                        <a href="{{ $transaction['detail_url'] }}" class="inline-flex items-center justify-center px-4 py-3 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-[10px]">
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-xs font-bold text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada data penjualan final sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
