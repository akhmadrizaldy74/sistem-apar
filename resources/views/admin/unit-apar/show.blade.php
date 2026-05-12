<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.unit-apar.index') }}"
               class="p-2 bg-white rounded-xl border border-gray-100 text-gray-400 hover:text-red-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Detail Unit APAR</h2>
                <p class="text-sm text-gray-500 font-medium">{{ $unit->no_seri }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $today     = now();
        $isExpired = $unit->tgl_expired && $unit->tgl_expired->lte($today);
        $daysLeft  = $unit->tgl_expired ? $today->diffInDays($unit->tgl_expired, false) : null;
        $isNear    = !$isExpired && $daysLeft !== null && $daysLeft <= 30;
        $status    = $isExpired ? 'expired' : ($isNear ? 'hampir' : 'aktif');

        $badgeClass = match($status) {
            'expired' => 'bg-red-50 text-red-700 border border-red-200',
            'hampir'  => 'bg-amber-50 text-amber-700 border border-amber-200',
            default   => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };
        $badgeLabel = match($status) {
            'expired' => 'Expired',
            'hampir'  => 'Hampir Expired',
            default   => 'Aktif',
        };
    @endphp

    <div class="max-w-5xl space-y-6">

        {{-- ALERT EXPIRED / HAMPIR EXPIRED --}}
        @if($isExpired)
        <div class="flex items-start gap-4 bg-red-50 border border-red-200 rounded-2xl px-6 py-5">
            <svg class="w-6 h-6 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-black text-red-800">Unit APAR ini sudah melewati masa berlaku!</p>
                <p class="text-xs font-semibold text-red-600 mt-1">
                    Expired sejak {{ $unit->tgl_expired->format('d M Y') }}. Segera lakukan service atau refill.
                </p>
            </div>
        </div>
        @elseif($isNear)
        <div class="flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-6 py-5">
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-black text-amber-800">Masa berlaku unit hampir habis!</p>
                <p class="text-xs font-semibold text-amber-600 mt-1">
                    Sisa {{ $daysLeft }} hari lagi — expired pada {{ $unit->tgl_expired->format('d M Y') }}.
                </p>
            </div>
        </div>
        @endif

        {{-- CARD 1: INFO UNIT --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-gray-900">Informasi Unit</span>
                </div>
                <a href="{{ route('admin.unit-apar.edit', $unit) }}"
                   class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-gray-50 text-gray-600 hover:bg-red-50 hover:text-red-700 transition">
                    Edit Unit
                </a>
            </div>

            <div class="px-8 py-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-6 gap-y-5">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nomor Seri</p>
                    <p class="text-sm font-bold text-gray-900">{{ $unit->no_seri ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                    <a href="{{ route('admin.pelanggan.edit', $unit->pelanggan) }}"
                       class="text-sm font-bold text-blue-600 hover:underline">
                        {{ $unit->pelanggan->nama ?? '-' }}
                    </a>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Produk</p>
                    <p class="text-sm font-bold text-gray-900">{{ $unit->produk->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Ukuran / Bahan</p>
                    <p class="text-sm font-bold text-gray-900">{{ $unit->ukuran ?? '-' }} / {{ $unit->bahan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Produksi</p>
                    <p class="text-sm font-bold text-gray-900">
                        {{ optional($unit->tgl_produksi)->format('d M Y') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Beli</p>
                    <p class="text-sm font-bold text-gray-900">
                        {{ optional($unit->tgl_beli)->format('d M Y') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Expired</p>
                    <p class="text-sm font-bold {{ $isExpired ? 'text-red-700' : ($isNear ? 'text-amber-700' : 'text-gray-900') }}">
                        {{ optional($unit->tgl_expired)->format('d M Y') ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Lokasi Unit</p>
                    <p class="text-sm font-bold text-gray-900">{{ $unit->lokasi_unit ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kondisi Awal</p>
                    <p class="text-sm font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $unit->kondisi_awal ?? '-') }}</p>
                </div>
                @if($unit->catatan_unit)
                <div class="col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Catatan</p>
                    <p class="text-sm font-semibold text-gray-700">{{ $unit->catatan_unit }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- STATS RINGKASAN --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Service</p>
                <p class="text-3xl font-black text-blue-600">{{ $services->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Refill</p>
                <p class="text-3xl font-black text-emerald-600">{{ $refills->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Total Biaya</p>
                <p class="text-3xl font-black text-red-700">
                    Rp {{ number_format($services->sum('biaya') + $refills->sum('biaya'), 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- CARD 2: RIWAYAT SERVICE --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486a4 4 0 00-5.656-5.656L4.458 13.343"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-gray-900">Riwayat Service</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black">
                        {{ $services->count() }}
                    </span>
                </div>
                <a href="{{ route('admin.service.index') }}"
                   class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                    + Input Service
                </a>
            </div>

            @if($services->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Service</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($services->sortByDesc('tgl_service') as $service)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ optional($service->tgl_service)->format('d M Y') ?? '-' }}
                                </p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest">
                                    {{ $service->jenis_service ?? '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-semibold text-gray-600">{{ $service->keterangan ?: '-' }}</p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($service->biaya ?? 0, 0, ',', '.') }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-100 bg-gray-50/30">
                        <tr>
                            <td colspan="3" class="px-8 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">
                                Total Biaya Service
                            </td>
                            <td class="px-8 py-4 text-right">
                                <p class="text-sm font-black text-blue-700">
                                    Rp {{ number_format($services->sum('biaya'), 0, ',', '.') }}
                                </p>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="px-8 py-10 text-center">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19.428 15.428a4 4 0 00-5.656-5.656l-8.486 8.485A2 2 0 108.114 21l8.485-8.486"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Belum ada riwayat service</p>
                <p class="text-xs font-semibold text-gray-400 mt-1">Riwayat service unit ini akan muncul di sini.</p>
            </div>
            @endif
        </div>

        {{-- CARD 3: RIWAYAT REFILL --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <span class="text-sm font-black text-gray-900">Riwayat Refill</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black">
                        {{ $refills->count() }}
                    </span>
                </div>
                <a href="{{ route('admin.refill.index') }}"
                   class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                    + Input Refill
                </a>
            </div>

            @if($refills->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Jenis Refill</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Expired Baru</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($refills->sortByDesc('tgl_refill') as $refill)
                        <tr class="hover:bg-gray-50/30 transition-colors">
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ optional($refill->tgl_refill)->format('d M Y') ?? '-' }}
                                </p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest">
                                    {{ $refill->jenisRefill->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $newExpiry = \Carbon\Carbon::parse($refill->tgl_refill)->addYear();
                                @endphp
                                <p class="text-xs font-bold text-gray-600">
                                    {{ $newExpiry->format('d M Y') }}
                                </p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($refill->biaya ?? 0, 0, ',', '.') }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-100 bg-gray-50/30">
                        <tr>
                            <td colspan="3" class="px-8 py-4 text-xs font-black text-gray-500 uppercase tracking-widest">
                                Total Biaya Refill
                            </td>
                            <td class="px-8 py-4 text-right">
                                <p class="text-sm font-black text-emerald-700">
                                    Rp {{ number_format($refills->sum('biaya'), 0, ',', '.') }}
                                </p>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="px-8 py-10 text-center">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Belum ada riwayat refill</p>
                <p class="text-xs font-semibold text-gray-400 mt-1">Riwayat refill unit ini akan muncul di sini.</p>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>