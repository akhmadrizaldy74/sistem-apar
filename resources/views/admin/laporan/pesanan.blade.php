<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Pesanan</h2>
                <p class="text-sm text-gray-500 font-medium">Rekap pesanan produk WhatsApp dengan filter tanggal dan pelanggan</p>
            </div>
            <a href="{{ route('admin.laporan.index') }}" class="px-6 py-3 bg-white border border-gray-100 rounded-2xl text-sm font-black text-gray-700 hover:shadow-lg transition">
                Kembali ke Pusat Laporan
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
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
                <a href="{{ route('admin.laporan.pesanan.pdf', request()->query()) }}" class="flex-1 px-6 py-4 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-xs text-center">
                    PDF
                </a>

            </div>
        </form>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Transaksi</p>
                <p class="text-4xl font-black text-gray-900 mt-3">{{ $stats['total_transaksi'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Item</p>
                <p class="text-4xl font-black text-emerald-600 mt-3">{{ $stats['total_item'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Nilai</p>
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
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan Item</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Unit</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pesanans as $pesanan)
                            <tr class="hover:bg-gray-50/40 transition">
                                <td class="px-8 py-6 text-sm font-bold text-gray-900">{{ $pesanan->tanggal->format('d M Y') }}</td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $pesanan->pelanggan?->nama ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $pesanan->pelanggan?->no_wa ?? '-' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                        Produk
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $pesanan->details->count() }} item</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                        {{ $pesanan->details->pluck('produk.nama')->filter()->take(2)->implode(', ') ?: 'Pesanan WhatsApp' }}
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">{{ $pesanan->details->sum('jumlah') }} unit</td>
                                <td class="px-8 py-6 text-sm font-black text-red-700">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('admin.pesanan.show', $pesanan) }}" class="inline-flex items-center justify-center px-4 py-3 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-[10px]">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada data pesanan sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
