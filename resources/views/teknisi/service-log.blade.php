<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Service Log</h1>
                <p class="text-slate-500 mt-2 font-medium">Daftar service APAR yang perlu dikerjakan dan riwayat penyelesaian tanpa rincian keuangan.</p>
            </div>
        </div>

        {{-- Service Pending (butuh kerja teknisi) --}}
        @if($pendingServices->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                Service Menunggu Laporan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ openModal: null }">
                @foreach($pendingServices as $service)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all flex flex-col">
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-blue-100">
                            {{ $service->servicePaket?->nama ?? $service->jenis_service }}
                        </span>
                        <span class="px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-lg">Menunggu Laporan</span>
                    </div>

                    <h3 class="font-black text-slate-900 text-base leading-tight">{{ $service->display_unit_label }}</h3>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ $service->display_customer_name }}</p>

                    <div class="mt-4 space-y-2 flex-1">
                        @if($service->servicePaket?->rincian_layanan)
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rincian Layanan</p>
                            <p class="text-xs font-semibold text-slate-700 mt-1">{{ $service->servicePaket->rincian_layanan }}</p>
                        </div>
                        @endif

                        @if($service->estimasi_peralatan)
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Estimasi Peralatan</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($service->estimasi_peralatan as $est)
                                <span class="inline-flex px-2.5 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-600">
                                    {{ $est['nama'] }} ×{{ $est['jumlah'] }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <button type="button" @click="openModal = {{ $service->id }}" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl text-xs uppercase tracking-widest transition shadow-lg shadow-red-600/30">
                            Kirim Laporan
                        </button>
                    </div>

                    {{-- Modal Laporan --}}
                    <div x-show="openModal === {{ $service->id }}" style="display: none" class="fixed inset-0 z-50 overflow-y-auto p-3 sm:p-4" aria-labelledby="modal-{{ $service->id }}" role="dialog" aria-modal="true">
                        <div class="flex min-h-full items-start justify-center text-center sm:items-center">
                            <div x-show="openModal === {{ $service->id }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true" @click="openModal = null"></div>

                            <div x-show="openModal === {{ $service->id }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="app-modal-shell relative my-3 max-w-xl text-left align-middle transition-all transform sm:my-6">
                                <div class="app-modal-header flex items-start justify-between border-b border-slate-100 bg-white/95 p-5 backdrop-blur sm:items-center sm:p-6">
                                    <h3 class="text-lg font-black text-slate-900">Laporan Service — {{ $service->display_unit_label }}</h3>
                                    <button type="button" @click="openModal = null" class="text-slate-400 hover:text-red-600 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <form action="{{ route('teknisi.service-log.laporan', $service) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="app-modal-body space-y-5 bg-slate-50/50 p-5 sm:p-6">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Peralatan Yang Dipakai</label>
                                            <div class="space-y-2" id="peralatan-list-{{ $service->id }}">
                                                @if($service->estimasi_peralatan)
                                                    @foreach($service->estimasi_peralatan as $idx => $est)
                                                    <div class="flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-200">
                                                        <span class="text-xs font-bold text-slate-700 flex-1">{{ $est['nama'] }}</span>
                                                        <input type="number" name="peralatan_used[{{ $idx }}][jumlah]" min="0" value="{{ $est['jumlah'] }}"
                                                            placeholder="Jumlah" class="w-20 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-900 text-center">
                                                        <input type="hidden" name="peralatan_used[{{ $idx }}][id]" value="{{ $est['peralatan_id'] }}">
                                                    </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <p class="text-[10px] text-slate-400 mt-1">Isi jumlah 0 jika peralatan tidak jadi dipakai.</p>
                                        </div>

                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Catatan Teknisi (Opsional)</label>
                                            <textarea name="catatan_teknisi" rows="3" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl font-bold text-slate-900 text-sm transition" placeholder="Tulis catatan pekerjaan..."></textarea>
                                        </div>

                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Foto Laporan (Opsional)</label>
                                            <input type="file" name="laporan_foto" accept="image/*" class="w-full px-5 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition">
                                        </div>
                                    </div>

                                    <div class="app-modal-footer flex flex-col-reverse gap-3 border-t border-slate-100 p-5 sm:flex-row sm:justify-end sm:p-6">
                                        <button type="button" @click="openModal = null" class="w-full px-6 py-3.5 text-xs font-black text-slate-500 uppercase tracking-widest hover:text-slate-900 transition rounded-xl sm:w-auto">Batal</button>
                                        <button type="submit" class="w-full px-6 py-3.5 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-600/30 uppercase tracking-widest text-xs flex items-center justify-center gap-2 sm:w-auto">
                                            Kirim Laporan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reported (menunggu konfirmasi admin) --}}
        @if($reportedServices->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Menunggu Konfirmasi Admin
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($reportedServices as $service)
                <div class="bg-white rounded-2xl border border-amber-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-lg">{{ $service->servicePaket?->nama ?? $service->jenis_service }}</span>
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-lg">Menunggu Konfirmasi</span>
                    </div>
                    <p class="font-black text-slate-900 text-sm">{{ $service->display_unit_label }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $service->display_customer_name }}</p>
                    @if($service->catatan_teknisi)
                    <p class="text-xs text-slate-600 mt-2 italic">{{ $service->catatan_teknisi }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Completed --}}
        @if($completedServices->count() > 0)
        <div>
            <h2 class="text-lg font-black text-slate-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Service Selesai
            </h2>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="responsive-table-wrap">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Unit / Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Paket</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Peralatan Terpakai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($completedServices as $service)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-xs font-bold text-slate-900">{{ optional($service->tgl_service)->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ $service->display_unit_label }}</p>
                                <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ $service->display_customer_name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-700">{{ $service->servicePaket?->nama ?? $service->jenis_service }}</td>
                            <td class="px-6 py-4">
                                @if($service->stok_kurang_history)
                                    @foreach($service->stok_kurang_history as $h)
                                    <span class="inline-flex px-2 py-1 bg-gray-100 rounded text-[10px] font-bold text-gray-600 mr-1 mb-1">{{ $h['nama'] }} ×{{ $h['jumlah'] }}</span>
                                    @endforeach
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        @endif

        @if($pendingServices->isEmpty() && $reportedServices->isEmpty() && $completedServices->isEmpty())
        <div class="py-16 px-4 flex flex-col items-center justify-center bg-white rounded-3xl border border-slate-200 border-dashed text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/></svg>
            </div>
            <h3 class="text-lg font-black text-slate-900">Belum ada service log</h3>
            <p class="text-slate-500 font-medium mt-1">Service log akan muncul di sini setelah admin menginput.</p>
        </div>
        @endif
    </div>
</x-app-layout>
