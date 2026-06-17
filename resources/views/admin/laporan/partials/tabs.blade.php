@php
    $reportTabs = [
        [
            'label' => 'Laporan Keseluruhan',
            'route' => route('admin.laporan.index', request()->query()),
            'active' => request()->routeIs('admin.laporan.index'),
        ],
        [
            'label' => 'Penjualan Barang & Refill',
            'route' => route('admin.laporan.penjualan', request()->query()),
            'active' => request()->routeIs('admin.laporan.penjualan') || request()->routeIs('admin.laporan.pesanan'),
        ],
        [
            'label' => 'Laporan Service',
            'route' => route('admin.laporan.service', request()->query()),
            'active' => request()->routeIs('admin.laporan.service'),
        ],
        [
            'label' => 'Laporan Keuangan',
            'route' => route('admin.laporan.keuangan', request()->query()),
            'active' => request()->routeIs('admin.laporan.keuangan'),
        ],
    ];
@endphp

<div class="overflow-x-auto">
    <div class="inline-flex min-w-full gap-2 rounded-2xl border border-gray-100 bg-white p-2 shadow-sm">
        @foreach($reportTabs as $tab)
            <a
                href="{{ $tab['route'] }}"
                class="inline-flex flex-1 items-center justify-center rounded-xl px-4 py-3 text-xs font-black uppercase tracking-widest transition {{ $tab['active'] ? 'bg-red-700 text-white shadow-lg shadow-red-700/20' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</div>
