@props(['tasks', 'emptyMessage' => 'Belum ada tugas aktif.'])

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @forelse($tasks as $task)
        <div class="bg-white rounded-[2rem] border border-slate-200 p-6 hover:shadow-lg transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
            <!-- Top Color Accent Gradient Border based on status -->
            @php
                $statusColor = $task->status === 'ditugaskan ke teknisi' ? 'from-amber-500 to-yellow-400' : 'from-blue-600 to-sky-500';
            @endphp
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r {{ $statusColor }}"></div>

            <div>
                <!-- Header Info -->
                <div class="flex justify-between items-start gap-4 mb-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-black uppercase tracking-widest">
                            {{ $task->tipe === 'produk' ? 'Pesanan Produk' : (($task->service_jenis_layanan === 'refill') ? 'Refill APAR' : 'Service APAR') }}
                        </span>
                        <h3 class="text-lg font-black text-slate-900 mt-2">
                            {{ $task->technicianTaskDateTime() }}
                        </h3>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5">
                            Pelanggan: {{ $task->pelanggan?->nama ?? '-' }}
                        </p>
                    </div>

                    <!-- Status Badge -->
                    <div>
                        @if($task->status === 'ditugaskan ke teknisi')
                            <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest shadow-sm">
                                Ditugaskan
                            </span>
                        @elseif($task->status === 'dikerjakan teknisi')
                            <span class="px-3 py-1.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest shadow-sm">
                                Sedang Dikerjakan
                            </span>
                        @else
                            <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest shadow-sm">
                                Selesai Final
                            </span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-4">
                    <!-- Customer Details -->
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Pelanggan</p>
                        <p class="text-sm font-black text-slate-900 mt-0.5">{{ $task->pelanggan?->nama ?? $task->nama_penerima ?? '-' }}</p>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5 flex items-center gap-1.5">
                            <!-- Phone Icon SVG -->
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            {{ $task->pelanggan?->no_wa ?? $task->nomor_wa_penerima ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold text-slate-500 mt-0.5 flex items-start gap-1.5">
                            <!-- Location Pin Icon SVG -->
                            <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>{{ $task->pelanggan?->alamat ?? $task->alamat_pengiriman ?? '-' }}</span>
                        </p>
                    </div>

                    <!-- Task details by type -->
                    @if($task->tipe === 'produk')
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Produk yang Dipesan</p>
                            <div class="mt-1 space-y-1 bg-slate-50 rounded-xl p-3 border border-slate-100">
                                @forelse($task->details as $detail)
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-bold text-slate-800">{{ $detail->produk?->nama ?? '-' }}</span>
                                        <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-black text-[10px]">{{ $detail->jumlah }} Unit</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic">Tidak ada detail produk.</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Catatan Pesanan</p>
                            <p class="text-xs font-semibold text-slate-600 mt-1 italic">{{ $task->keterangan ?: 'Tidak ada catatan.' }}</p>
                        </div>
                    @elseif($task->service_jenis_layanan === 'refill')
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Unit APAR</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->service_jenis_apar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Jenis Refil</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->serviceJenisRefill?->nama ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Ukuran APAR</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->service_ukuran_apar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Jumlah Unit</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->service_jumlah_unit ?? 0 }} unit</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Metode Penanganan</p>
                                <p class="font-bold text-slate-800 mt-0.5 capitalize">{{ $task->service_metode_penanganan ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Catatan Keluhan</p>
                            <p class="text-xs font-semibold text-slate-600 mt-1 italic">{{ $task->service_keluhan ?? $task->keterangan ?: 'Tidak ada catatan.' }}</p>
                        </div>
                    @else
                        <!-- Service APAR -->
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Unit APAR</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->service_jenis_apar ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Jenis Service</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $task->servicePaket?->nama ?? '-' }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400">Metode Penanganan</p>
                                <p class="font-bold text-slate-800 mt-0.5 capitalize">{{ $task->service_metode_penanganan ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Catatan Keluhan</p>
                            <p class="text-xs font-semibold text-slate-600 mt-1 italic">{{ $task->service_keluhan ?? $task->keterangan ?: 'Tidak ada catatan.' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Section -->
            <div class="mt-6 pt-4 border-t border-slate-100" x-data="{ showDone: false }">
                <div class="flex gap-2">
                    @if($task->status === 'ditugaskan ke teknisi')
                        <form action="{{ route('teknisi.tugas.mulai', $task) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-widest shadow-md transition duration-150">
                                Kerjakan
                            </button>
                        </form>
                    @elseif($task->status === 'dikerjakan teknisi')
                        <button type="button" @click="showDone = !showDone" class="w-full px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-widest shadow-md transition duration-150">
                            Selesai
                        </button>
                    @endif
                </div>

                <div x-show="showDone" x-cloak class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 transition-all duration-300">
                    <form action="{{ route('teknisi.tugas.selesai', $task) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="block text-[10px] font-black uppercase tracking-widest text-emerald-800">Laporan Hasil Pengerjaan</label>
                        <textarea name="catatan" rows="3" required class="w-full rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400" placeholder="Tuliskan catatan hasil pengerjaan di sini..."></textarea>
                        <button type="submit" class="w-full px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-widest shadow-md transition duration-150">
                            Kirim Laporan Selesai
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-1 lg:col-span-2 bg-white rounded-[2rem] border border-slate-200 p-12 text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            <p class="text-slate-500 font-bold">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>
