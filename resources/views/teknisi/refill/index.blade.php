<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Tugas Refill Internal</h1>
                <p class="text-slate-500 mt-2 font-medium">Daftar stok APAR expired yang perlu Anda refill.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <p class="font-bold text-sm">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-bold text-sm">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ openModal: false, task: null }">
            @forelse($tugasRefill as $tugas)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-red-100">
                            {{ $tugas->jumlah_refill }} Unit
                        </span>
                        @if($tugas->status === 'menunggu')
                            <span class="px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-lg">Menunggu</span>
                        @elseif($tugas->status === 'diproses')
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-lg">Diproses</span>
                        @endif
                    </div>

                    <h3 class="font-black text-slate-900 text-lg leading-tight">{{ $tugas->produk->nama }}</h3>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ $tugas->produk->merek }} - {{ $tugas->produk->kapasitas }}</p>

                    <div class="mt-5 space-y-3 flex-1">
                        <div class="flex items-start gap-3 bg-slate-50 p-3 rounded-2xl">
                            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expired Lama</p>
                                <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $tugas->stokBatch->tgl_expired ? $tugas->stokBatch->tgl_expired->format('d M Y') : '-' }}</p>
                            </div>
                        </div>

                        @if($tugas->catatan_admin)
                            <div class="flex items-start gap-3 bg-amber-50/50 p-3 rounded-2xl border border-amber-100/50">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Catatan Admin</p>
                                    <p class="text-xs font-semibold text-amber-800 mt-0.5">{{ $tugas->catatan_admin }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100 flex gap-3">
                        @if($tugas->status === 'menunggu')
                            <form action="{{ route('teknisi.refill-stock.mulai', $tugas) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-black rounded-xl text-xs uppercase tracking-widest transition shadow-lg shadow-slate-900/20">Mulai Refill</button>
                            </form>
                        @elseif($tugas->status === 'diproses')
                            <button type="button" @click="task = {{ $tugas->toJson() }}; openModal = true;" class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl text-xs uppercase tracking-widest transition shadow-lg shadow-red-600/30 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Selesai Refill
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 px-4 flex flex-col items-center justify-center bg-white rounded-3xl border border-slate-200 border-dashed text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Tidak ada tugas refill</h3>
                    <p class="text-slate-500 font-medium mt-1">Saat ini tidak ada tugas refill internal yang perlu dikerjakan.</p>
                </div>
            @endforelse

            <!-- Selesai Modal -->
            <div x-show="openModal" style="display: none" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true" @click="openModal = false"></div>

                    <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white rounded-3xl shadow-2xl">
                        
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-lg font-black text-slate-900" id="modal-title">Selesaikan Refill</h3>
                            <button type="button" @click="openModal = false" class="text-slate-400 hover:text-red-600 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form :action="task ? '/teknisi/refill-stock/' + task.id + '/selesai' : ''" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="p-6 space-y-5 bg-slate-50/50">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Tanggal Selesai Refill</label>
                                    <input type="date" name="tanggal_refill" required :value="new Date().toISOString().split('T')[0]" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600/20 focus:border-red-600 font-bold text-slate-900 text-sm transition">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Catatan Teknisi (Opsional)</label>
                                    <textarea name="catatan_teknisi" rows="3" class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600/20 focus:border-red-600 font-bold text-slate-900 text-sm transition" placeholder="Tulis catatan jika ada..."></textarea>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Bukti Foto (Opsional)</label>
                                    <input type="file" name="bukti_foto" accept="image/*" class="w-full px-5 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-600/20 font-bold text-slate-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition">
                                </div>
                            </div>
                            
                            <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
                                <button type="button" @click="openModal = false" class="px-6 py-3.5 text-xs font-black text-slate-500 uppercase tracking-widest hover:text-slate-900 transition rounded-xl">Batal</button>
                                <button type="submit" class="px-6 py-3.5 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-600/30 uppercase tracking-widest text-xs flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Konfirmasi Selesai
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
