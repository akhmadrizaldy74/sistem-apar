<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Keuangan</h2>
                <p class="text-sm text-gray-500 font-medium">Pemasukan final dan pengeluaran operasional dalam satu laporan.</p>
            </div>
            <a href="{{ route('admin.laporan.keuangan.pdf', request()->query()) }}" class="inline-flex items-center justify-center px-6 py-3 bg-red-700 text-white rounded-2xl text-sm font-black hover:bg-red-800 transition shadow-xl shadow-red-700/20">
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
                <a href="{{ route('admin.laporan.keuangan', []) }}" class="flex-1 px-6 py-4 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-xs text-center">
                    Reset
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pemasukan</p>
                <p class="text-3xl font-black text-emerald-700 mt-3">Rp {{ number_format($totals['total_pemasukan'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Pengeluaran</p>
                <p class="text-3xl font-black text-red-700 mt-3">Rp {{ number_format($totals['total_pengeluaran'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Laba Bersih</p>
                <p class="text-3xl font-black {{ $totals['laba_bersih'] >= 0 ? 'text-blue-700' : 'text-red-700' }} mt-3">Rp {{ number_format($totals['laba_bersih'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Transaksi</p>
                <p class="text-3xl font-black text-gray-900 mt-3">{{ $totals['total_transaksi'] }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rincian Pemasukan</p>
                <div class="mt-5 space-y-4">
                    <div class="flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-4">
                        <span class="text-sm font-black text-emerald-700">Penjualan Produk</span>
                        <span class="text-sm font-black text-emerald-800">Rp {{ number_format($incomeBreakdown['produk'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-amber-50 px-4 py-4">
                        <span class="text-sm font-black text-amber-700">Refill</span>
                        <span class="text-sm font-black text-amber-800">Rp {{ number_format($incomeBreakdown['refill'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-blue-50 px-4 py-4">
                        <span class="text-sm font-black text-blue-700">Service</span>
                        <span class="text-sm font-black text-blue-800">Rp {{ number_format($incomeBreakdown['service'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rincian Pengeluaran</p>
                <div class="mt-5 space-y-4">
                    @forelse($expenseBreakdown as $label => $amount)
                        <div class="flex items-center justify-between rounded-2xl bg-red-50 px-4 py-4">
                            <span class="text-sm font-black text-red-700">{{ $label }}</span>
                            <span class="text-sm font-black text-red-800">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-gray-50 px-4 py-4 text-sm font-semibold text-gray-500">
                            Belum ada pengeluaran dalam periode ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tren 6 Bulan</p>
                    <h3 class="text-xl font-black text-gray-900 mt-1">Grafik Arus Kas</h3>
                </div>
                <div class="flex gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span> Pemasukan</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-400"></span> Pengeluaran</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-500"></span> Laba</span>
                </div>
            </div>

            @php
                $maxValue = max(
                    collect($trendData)->max('total_pemasukan') ?: 1,
                    collect($trendData)->max('pengeluaran') ?: 1,
                    collect($trendData)->max('laba') ?: 1
                );
            @endphp

            <div class="flex items-end gap-3 h-64">
                @foreach($trendData as $item)
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <div class="w-full flex flex-col gap-1 h-full justify-end items-center">
                            <div class="w-full flex gap-1 items-end justify-center h-full">
                                @php $barScale = $maxValue > 0 ? ($item['total_pemasukan'] / $maxValue) * 100 : 0; @endphp
                                <div class="w-6 bg-emerald-500 rounded-t-sm transition-all" style="height: {{ max($barScale, 2) }}%"></div>
                            </div>
                            <div class="w-full flex gap-1 items-end justify-center">
                                @php $expScale = $maxValue > 0 ? ($item['pengeluaran'] / $maxValue) * 100 : 0; @endphp
                                <div class="w-6 bg-red-400 rounded-t-sm transition-all" style="height: {{ max($expScale, 2) }}%"></div>
                            </div>
                            <div class="w-full flex gap-1 items-end justify-center">
                                @php $labaScale = $maxValue > 0 ? (abs($item['laba']) / $maxValue) * 100 : 0; @endphp
                                <div class="w-6 bg-blue-500 rounded-t-sm transition-all" style="height: {{ max($labaScale, 2) }}%"></div>
                            </div>
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-center mt-2">{{ explode(' ', $item['label'])[0] }}</p>
                        <p class="text-[9px] font-bold text-gray-400">{{ $item['tahun'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-xl font-black text-gray-900">Detail Laporan Keuangan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Sumber</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ $record['tanggal_label'] }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $record['direction'] === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $record['jenis'] }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-sm font-semibold text-gray-700">{{ $record['keterangan'] }}</td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-700">{{ $record['pelanggan'] }}</td>
                                <td class="px-8 py-5 text-sm font-semibold text-gray-700">{{ $record['status'] }}</td>
                                <td class="px-8 py-5 text-sm font-semibold text-gray-700">{{ $record['source'] }}</td>
                                <td class="px-8 py-5 text-sm font-black {{ $record['direction'] === 'in' ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $record['direction'] === 'in' ? '' : '- ' }}Rp {{ number_format($record['nominal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-sm font-medium text-gray-500">Belum ada transaksi sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
