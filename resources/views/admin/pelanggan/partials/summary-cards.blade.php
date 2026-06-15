<div class="grid grid-cols-1 gap-6 md:grid-cols-3">
    <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Pelanggan</p>
                <p class="text-5xl font-black text-slate-900">{{ number_format($summary['totalPelanggan']) }}</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-white shadow-lg shadow-red-500/30">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan Aktif</p>
                <p class="text-5xl font-black text-emerald-700">{{ number_format($summary['pelangganAktif']) }}</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-500">Pelanggan aktif dihitung dari pelanggan yang sudah memiliki riwayat pembelian produk.</p>
    </div>

    <div class="rounded-3xl border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur-md">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Total Transaksi Pelanggan</p>
                <p class="text-5xl font-black text-blue-700">{{ number_format($summary['totalTransaksiPelanggan']) }}</p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-lg shadow-blue-500/30">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
        </div>
        <p class="mt-4 text-xs font-semibold leading-relaxed text-slate-500">Total transaksi hanya menghitung riwayat pembelian produk pelanggan.</p>
    </div>
</div>
