<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.purchase-orders.show', $purchaseOrder->id) }}" class="p-2.5 bg-white rounded-2xl border border-slate-200 text-slate-400 hover:text-red-700 hover:border-red-200 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Input Surat Jalan Supplier</h2>
                <p class="text-sm text-slate-500 font-semibold">Nomor PO: {{ $purchaseOrder->nomor_po }} (Supplier: {{ $purchaseOrder->supplier?->nama_supplier }})</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-8 sm:p-12 space-y-6">
            <form action="{{ route('admin.purchase-orders.simpan-surat-jalan', $purchaseOrder->id) }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="no_surat_jalan" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Nomor Surat Jalan <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="no_surat_jalan" id="no_surat_jalan" value="{{ old('no_surat_jalan', $purchaseOrder->no_surat_jalan) }}" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none"
                        placeholder="Contoh: SJ-2026/07/001">
                    <x-input-error :messages="$errors->get('no_surat_jalan')" class="mt-2" />
                </div>

                <div>
                    <label for="tanggal_surat_jalan" class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">
                        Tanggal Surat Jalan <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="tanggal_surat_jalan" id="tanggal_surat_jalan" value="{{ old('tanggal_surat_jalan', $purchaseOrder->tanggal_surat_jalan ? $purchaseOrder->tanggal_surat_jalan->format('Y-m-d') : date('Y-m-d')) }}" required
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 font-bold text-slate-900 transition outline-none">
                    <x-input-error :messages="$errors->get('tanggal_surat_jalan')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.purchase-orders.show', $purchaseOrder->id) }}" class="px-8 py-4 bg-slate-600 text-white font-black rounded-2xl hover:bg-slate-700 transition uppercase tracking-widest text-xs">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-red-700 text-white font-black rounded-2xl hover:bg-red-800 transition shadow-xl shadow-red-700/30 uppercase tracking-widest text-xs">
                        Simpan Surat Jalan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
