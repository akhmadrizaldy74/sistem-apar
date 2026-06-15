@if($notifications['urgentOrdersCount'] > 0 || $kpis['unitExpired'] > 0 || $kpis['unitAkanExpired'] > 0)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Butuh Tindak Lanjut</h3>
                </div>
                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">{{ $monitoringPrioritas }}</span>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @if($notifications['urgentOrdersCount'] > 0)
                <a href="{{ route('admin.pesanan.index') }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50/30 transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Pesanan Perlu Diproses</p>
                            <p class="text-[10px] text-gray-500">{{ $notifications['urgentOrdersCount'] }} pesanan menunggu tindak lanjut</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
            @if($kpis['unitExpired'] > 0)
                <a href="{{ route('admin.unit-apar.index') }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50/30 transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-100 text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Unit Sudah Expired</p>
                            <p class="text-[10px] text-gray-500">{{ $kpis['unitExpired'] }} unit perlu penanganan segera</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
            @if($kpis['unitAkanExpired'] > 0)
                <a href="{{ route('admin.unit-apar.index') }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50/30 transition">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Unit Akan Expired</p>
                            <p class="text-[10px] text-gray-500">{{ $kpis['unitAkanExpired'] }} unit perlu di-schedule refill</p>
                        </div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>
    </div>
@endif
