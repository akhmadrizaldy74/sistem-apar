<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
    <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pesanan</p>
        <p class="text-4xl font-black text-gray-900">{{ $summary['totalPesanan'] }}</p>
    </div>
    <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Item Terjual</p>
        <p class="text-4xl font-black text-emerald-700">{{ $summary['totalItem'] }}</p>
    </div>
    <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nilai Pesanan</p>
        <p class="text-4xl font-black text-red-700">Rp {{ number_format((float) $summary['nilaiPesanan'], 0, ',', '.') }}</p>
    </div>
    <div class="bg-white p-5 sm:p-6 lg:p-8 rounded-2xl shadow-sm border border-gray-100">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Online / Offline</p>
        <div class="flex items-end gap-3">
            <div>
                <p class="text-4xl font-black text-amber-700 leading-none">{{ $summary['pesananOnline'] }}</p>
                <p class="mt-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Online</p>
            </div>
            <span class="pb-1 text-2xl font-black text-gray-300">/</span>
            <div>
                <p class="text-4xl font-black text-slate-700 leading-none">{{ $summary['pesananOffline'] }}</p>
                <p class="mt-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Offline</p>
            </div>
        </div>
    </div>
</div>
