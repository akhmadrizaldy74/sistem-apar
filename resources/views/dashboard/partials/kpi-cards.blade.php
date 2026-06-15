@php
    $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
@endphp

<div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-8">
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Produk</p>
        <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalProduk']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Pelanggan</p>
        <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalPelanggan']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Pesanan</p>
        <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalPesanan']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Komplain</p>
        <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalKomplain']) }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Unit APAR</p>
        <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalUnitApar']) }}</p>
    </div>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-emerald-600">Pendapatan Bulan Ini</p>
        <p class="text-base font-black text-emerald-700">{{ $formatRupiah($kpis['pendapatanBulanIni']) }}</p>
    </div>
    <div class="rounded-xl border border-red-200 bg-red-50 p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-red-400">Prioritas</p>
        <p class="text-lg font-black text-red-600">{{ number_format($monitoringPrioritas) }}</p>
    </div>
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 shadow-sm">
        <p class="mb-1 text-[9px] font-bold uppercase tracking-wider text-blue-500">Pengunjung Hari Ini</p>
        <p class="text-lg font-black text-blue-600">{{ number_format($visitorStats['hariIni']) }}</p>
    </div>
</div>
