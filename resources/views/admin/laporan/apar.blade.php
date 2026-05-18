<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan APAR</h2>
                <p class="text-sm text-gray-500 font-medium">Filter data APAR berdasarkan tanggal produksi dan pelanggan</p>
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
                <a href="{{ route('admin.laporan.apar.pdf', request()->query()) }}" class="flex-1 px-6 py-4 bg-white text-gray-700 font-black rounded-2xl border border-gray-100 hover:shadow-lg transition uppercase tracking-widest text-xs text-center">
                    PDF
                </a>

            </div>
        </form>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total APAR</p>
                <p class="text-4xl font-black text-gray-900 mt-3">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Aktif</p>
                <p class="text-4xl font-black text-emerald-600 mt-3">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Expired</p>
                <p class="text-4xl font-black text-red-700 mt-3">{{ $stats['expired'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/60">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Produk</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Produksi</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Beli</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Expired</th>
                            <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($units as $unit)
                            <tr class="hover:bg-gray-50/40 transition">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $unit->pelanggan->nama }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $unit->no_seri }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $unit->produk?->nama ?? '-' }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $unit->produk?->jenisApar?->nama ?? 'APAR' }}</p>
                                </td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">{{ optional($unit->tgl_produksi)->format('d M Y') ?? '-' }}</td>
                                <td class="px-8 py-6 text-sm font-bold text-gray-700">{{ optional($unit->tgl_beli)->format('d M Y') ?? '-' }}</td>
                                <td class="px-8 py-6 text-sm font-bold {{ $unit->tgl_expired->isPast() ? 'text-red-700' : 'text-gray-700' }}">{{ $unit->tgl_expired->format('d M Y') }}</td>
                                <td class="px-8 py-6">
                                    @php
                                        $daysLeft = $unit->tgl_expired ? now()->diffInDays($unit->tgl_expired, false) : null;
                                        if ($daysLeft === null) { $badgeClass = 'bg-gray-50 text-gray-500'; $label = '-'; }
                                        elseif ($daysLeft < 0) { $badgeClass = 'bg-red-50 text-red-700'; $label = 'Expired'; }
                                        elseif ($daysLeft <= 30) { $badgeClass = 'bg-amber-50 text-amber-700'; $label = 'Hampir Kedaluarsa'; }
                                        else { $badgeClass = 'bg-emerald-50 text-emerald-700'; $label = 'Aktif'; }
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest {{ $badgeClass }}">
                                        {{ $label }}
                                    </span>
                                    @if($daysLeft !== null && $daysLeft >= 0)
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $daysLeft }} hari lagi</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-10 text-center text-sm font-semibold text-gray-500">Belum ada data APAR sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
