@props(['tasks', 'emptyMessage' => 'Belum ada riwayat tugas.'])

<div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Selesai</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Detail</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Catatan Teknisi</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-5">
                            <p class="text-xs font-black text-slate-800">{{ optional($task->teknisi_selesai_at)->format('d M Y H:i') ?: '-' }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-1">ORD-{{ $task->id }}</p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-sm font-black text-slate-900">{{ $task->pelanggan?->nama ?? '-' }}</p>
                            <p class="text-xs font-semibold text-slate-500 mt-1">{{ $task->pelanggan?->alamat ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-5">
                            @if($task->tipe === 'produk')
                                <p class="text-sm font-black text-slate-900">Pesanan Produk</p>
                                <p class="text-xs font-semibold text-slate-500 mt-1">{{ $task->details->pluck('produk.nama')->filter()->take(3)->implode(', ') ?: '-' }}</p>
                            @else
                                <p class="text-sm font-black text-slate-900">{{ strtoupper((string) ($task->service_jenis_layanan ?? 'service')) }}</p>
                                <p class="text-xs font-semibold text-slate-500 mt-1">{{ $task->service_jenis_apar ?? '-' }} - {{ $task->service_jumlah_unit ?? 0 }} unit</p>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-sm text-slate-700">{{ $task->teknisi_catatan ?: '-' }}</td>
                        <td class="px-6 py-5">
                            @php
                                $statusClass = match($task->status) {
                                    'selesai final' => 'bg-emerald-100 text-emerald-700',
                                    'dikonfirmasi admin' => 'bg-blue-100 text-blue-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest {{ $statusClass }}">
                                {{ $task->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm font-semibold text-slate-400">{{ $emptyMessage }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
