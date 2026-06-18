<div class="grid grid-cols-1 items-stretch gap-5 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
    <div class="flex min-h-[168px] h-full flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <p class="mb-3 text-[13px] font-black uppercase tracking-[0.16em] text-gray-500">Total Transaksi</p>
        <p class="text-[30px] font-black leading-none tracking-tight text-gray-900">{{ $summary['totalTransaksi'] }}</p>
    </div>
    <div class="flex min-h-[168px] h-full flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <p class="mb-3 text-[13px] font-black uppercase tracking-[0.16em] text-gray-500">Pembelian Unit</p>
        <p class="text-[30px] font-black leading-none tracking-tight text-red-700">{{ $summary['totalPembelianUnit'] }}</p>
    </div>
    <div class="flex min-h-[168px] h-full flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <p class="mb-3 text-[13px] font-black uppercase tracking-[0.16em] text-gray-500">Refill APAR</p>
        <p class="text-[30px] font-black leading-none tracking-tight text-emerald-700">{{ $summary['totalRefill'] }}</p>
    </div>
    <div class="flex min-h-[168px] h-full flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <p class="mb-3 text-[13px] font-black uppercase tracking-[0.16em] text-gray-500">Service APAR</p>
        <p class="text-[30px] font-black leading-none tracking-tight text-violet-700">{{ $summary['totalService'] }}</p>
    </div>
    <div class="flex min-h-[168px] h-full flex-col justify-between rounded-3xl border border-gray-100 bg-white p-6 shadow-sm md:col-span-2 lg:col-span-2 2xl:col-span-2">
        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div>
                <p class="text-[13px] font-black uppercase tracking-[0.16em] text-gray-500">Penghasilan Pesanan</p>
            </div>
            <select
                id="pesanan-revenue-period"
                class="min-w-[156px] rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-[13px] font-black text-gray-700 outline-none transition focus:border-red-500 focus:bg-white"
                onchange="const url = new URL(window.location.href); url.searchParams.set('revenue_period', this.value); window.location = url.toString();"
            >
                <option value="month" @selected(($summary['revenuePeriod'] ?? 'month') === 'month')>Bulan Ini</option>
                <option value="all" @selected(($summary['revenuePeriod'] ?? 'month') === 'all')>Keseluruhan</option>
            </select>
        </div>
        <p class="mt-5 text-[30px] font-black leading-none tracking-tight text-gray-900">
            Rp {{ number_format((float) $summary['penghasilanPesanan'], 0, ',', '.') }}
        </p>
        <p class="mt-3 text-sm font-semibold text-gray-500">Menghitung transaksi Selesai Final.</p>
    </div>
</div>
