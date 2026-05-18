@props(['tasks', 'emptyMessage' => 'Belum ada tugas aktif.'])

<div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Tanggal</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Detail Tugas</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-5">
                            <p class="text-xs font-black text-slate-800">{{ optional($task->tanggal)->format('d M Y') }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-1">TUGAS-{{ $task->id }}</p>
                        </td>
                        <td class="px-6 py-5">
                            <p class="text-sm font-black text-slate-900">{{ $task->pelanggan?->nama ?? '-' }}</p>
                            <p class="text-xs font-semibold text-slate-500 mt-1">{{ $task->pelanggan?->alamat ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-5">
                            @if($task->tipe === 'produk')
                                <p class="text-sm font-black text-slate-900">{{ $task->details->count() }} item produk</p>
                                <p class="text-xs font-semibold text-slate-500 mt-1">
                                    {{ $task->details->pluck('produk.nama')->filter()->take(3)->implode(', ') ?: '-' }}
                                </p>
                            @else
                                <p class="text-sm font-black text-slate-900">{{ ($task->service_jenis_layanan ?? 'service') === 'refill' ? 'Refil APAR' : 'Service APAR' }}</p>
                                <p class="text-xs font-semibold text-slate-500 mt-1">
                                    {{ $task->service_jenis_apar ?? '-' }} - {{ $task->service_jumlah_unit ?? 0 }} unit - {{ $task->service_metode_penanganan ?? '-' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">{{ $task->service_keluhan ?: ($task->keterangan ?? '-') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            @if($task->status === 'ditugaskan ke teknisi')
                                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest">Ditugaskan</span>
                            @else
                                <span class="px-3 py-1.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest">Dikerjakan</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            <div x-data="{ showDone: false }">
                                <div class="flex justify-end gap-2">
                                    @if($task->status === 'ditugaskan ke teknisi')
                                        <form action="{{ route('teknisi.tugas.mulai', $task) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-black uppercase tracking-widest hover:bg-blue-700 transition">
                                                Terima Tugas
                                            </button>
                                        </form>
                                    @endif
                                    @if($task->status === 'dikerjakan teknisi')
                                        <button type="button" @click="showDone = !showDone" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                                            Selesai
                                        </button>
                                    @endif
                                </div>
                                <div x-show="showDone" x-cloak class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                    <form action="{{ route('teknisi.tugas.selesai', $task) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <textarea name="catatan" rows="2" class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-400" placeholder="Laporan hasil pengerjaan (opsional)"></textarea>
                                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-700 transition">
                                            Kirim Laporan Selesai
                                        </button>
                                    </form>
                                </div>
                            </div>
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
