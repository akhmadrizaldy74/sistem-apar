<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900 md:text-lg">Prioritas</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    {{ number_format((int) ($notifications['urgentOrdersCount'] ?? 0)) }} pesanan menunggu,
                    {{ number_format((int) ($kpis['unitAkanExpired'] ?? 0)) }} unit akan expired,
                    {{ number_format((int) ($kpis['unitExpired'] ?? 0)) }} unit expired.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-red-100 px-4 py-1.5 text-sm font-bold text-red-700">
                {{ number_format($monitoringPrioritas) }} prioritas
            </span>
        </div>
    </div>

    <div class="grid gap-3 border-b border-slate-100 p-5 md:grid-cols-3 sm:px-6">
        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-3">
            <p class="text-sm font-bold text-blue-700">Pesanan Menunggu</p>
            <p class="mt-2 text-2xl font-black text-blue-800">{{ number_format((int) ($notifications['urgentOrdersCount'] ?? 0)) }}</p>
            <p class="mt-2 text-sm font-medium leading-6 text-blue-700/80">Pesanan yang masih perlu diproses admin.</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-3">
            <p class="text-sm font-bold text-amber-700">Unit Akan Expired</p>
            <p class="mt-2 text-2xl font-black text-amber-800">{{ number_format((int) ($kpis['unitAkanExpired'] ?? 0)) }}</p>
            <p class="mt-2 text-sm font-medium leading-6 text-amber-700/80">Perlu dijadwalkan sebelum masa berlaku habis.</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50/80 px-4 py-3">
            <p class="text-sm font-bold text-red-700">Unit Expired</p>
            <p class="mt-2 text-2xl font-black text-red-800">{{ number_format((int) ($kpis['unitExpired'] ?? 0)) }}</p>
            <p class="mt-2 text-sm font-medium leading-6 text-red-700/80">Butuh penanganan cepat agar tidak terlewat.</p>
        </div>
    </div>

    @if($monitoringPrioritas > 0)
        <div class="divide-y divide-slate-100">
            @if($notifications['urgentOrdersCount'] > 0)
                <a href="{{ route('admin.pesanan.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-900">Pesanan Perlu Diproses</p>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ number_format($notifications['urgentOrdersCount']) }} pesanan masih menunggu tindak lanjut admin.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
            @if($kpis['unitExpired'] > 0)
                <a href="{{ route('admin.unit-apar.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-900">Unit Sudah Expired</p>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ number_format($kpis['unitExpired']) }} unit perlu penanganan segera agar tidak terlewat.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
            @if($kpis['unitAkanExpired'] > 0)
                <a href="{{ route('admin.unit-apar.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-900">Unit Akan Expired</p>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ number_format($kpis['unitAkanExpired']) }} unit perlu dijadwalkan lebih awal untuk service atau refill.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>
    @else
        <div class="px-5 py-5 sm:px-6">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4 text-sm font-medium leading-6 text-emerald-800">
                Semua transaksi utama dalam kondisi aman. Belum ada pesanan menunggu dan belum ada unit APAR yang perlu tindakan segera saat ini.
            </div>
        </div>
    @endif
</div>
