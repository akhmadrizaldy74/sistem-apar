<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center w-full gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Penjualan Barang & Refill</h2>
                <p class="text-sm text-gray-500 font-medium">Periode: {{ $monthLabel }}</p>
            </div>
            <a href="{{ route('admin.laporan.index', request()->only('periode')) }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-900 font-bold rounded-2xl hover:shadow-md transition">
                Kembali ke Pusat Laporan
            </a>
        </div>
    </x-slot>

    <div class="space-y-8">
        <form method="GET" class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row gap-4 items-end">
            <div>
                <label for="periode" class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Periode</label>
                <input type="month" name="periode" id="periode" value="{{ request('periode') }}"
                    class="px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-red-600/20 font-bold text-gray-900">
            </div>
            <button type="submit" class="px-8 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                Terapkan Filter
            </button>
        </form>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900">Penjualan Barang</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($units as $unit)
                                <tr>
                                    <td class="px-8 py-5 text-xs font-bold text-gray-900">{{ optional($unit->created_at)->format('d M Y') }}</td>
                                    <td class="px-8 py-5 text-xs font-semibold text-gray-700">{{ $unit->pelanggan->nama }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $unit->produk->nama }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $unit->produk->jenisApar->nama ?? 'APAR' }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-xs font-black text-red-700">Rp {{ number_format($unit->produk->harga ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada data penjualan barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900">Penjualan Refill</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</th>
                                <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($refills as $refill)
                                <tr>
                                    <td class="px-8 py-5 text-xs font-bold text-gray-900">{{ $refill->tgl_refill->format('d M Y') }}</td>
                                    <td class="px-8 py-5 text-xs font-semibold text-gray-700">{{ $refill->unitApar->pelanggan->nama }}</td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900">{{ $refill->jenisRefill->nama }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $refill->unitApar->no_seri }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-xs font-black text-red-700">Rp {{ number_format($refill->biaya, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada data penjualan refill.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
