<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
        $monitoringPrioritas = $kpis['unitAkanExpired'] + $kpis['unitExpired'] + ($notifications['urgentOrdersCount'] ?? 0);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-600">Dashboard</p>
                <h2 class="mt-1 text-xl font-black text-slate-900 md:text-2xl">Ringkasan Sistem APAR</h2>
                <p class="mt-0.5 text-sm text-slate-500">Pantau data utama bisnis APAR Anda.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">

        {{-- A. KPI Cards - Compact Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1">Produk</p>
                <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalProduk']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pelanggan</p>
                <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalPelanggan']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mb-1">Unit APAR</p>
                <p class="text-lg font-black text-slate-900">{{ number_format($kpis['totalUnitApar']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Pendapatan Bulan Ini</p>
                <p class="text-base font-black text-emerald-700">{{ $formatRupiah($kpis['pendapatanBulanIni']) }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-red-400 mb-1">Prioritas</p>
                <p class="text-lg font-black text-red-600">{{ number_format($monitoringPrioritas) }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 shadow-sm">
                <p class="text-[9px] font-bold uppercase tracking-wider text-blue-500 mb-1">Pengunjung Hari Ini</p>
                <p class="text-lg font-black text-blue-600">{{ number_format($visitorStats['hariIni']) }}</p>
            </div>
        </div>

        {{-- B. Analitik Ringkas - Chart (2 Charts Only) --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm">Analitik Ringkas</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                <div class="text-center">
                    <h4 class="mb-2 text-[9px] font-bold uppercase tracking-wider text-gray-500">Sumber Pendapatan</h4>
                    <div id="revenue-composition-chart" class="mx-auto" style="height: 160px;"></div>
                </div>
                <div class="text-center">
                    <h4 class="mb-2 text-[9px] font-bold uppercase tracking-wider text-gray-500">Status Unit APAR</h4>
                    <div id="unit-status-chart" class="mx-auto" style="height: 160px;"></div>
                </div>
            </div>
        </div>

        {{-- C. Produk Sering Dilihat & Dibeli --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Produk Paling Dilihat --}}
            <div class="bg-white rounded-xl border border-violet-200 shadow-sm overflow-hidden">
                <div class="border-b border-violet-100 px-4 py-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-sm">Produk Paling Dilihat</h3>
                        <a href="{{ route('admin.laporan.index') }}" class="text-[10px] font-semibold text-violet-600 hover:text-violet-700">Selengkapnya &rarr;</a>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($topViewedProducts as $idx => $product)
                    <div class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50/30">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-violet-100 text-violet-700 text-[9px] font-bold">{{ $idx + 1 }}</span>
                            <span class="text-xs font-semibold text-gray-900">{{ $product['product_name'] }}</span>
                        </div>
                        <span class="text-xs font-bold text-violet-600">{{ number_format($product['view_count']) }}x</span>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center text-[10px] text-gray-400">Belum ada data produk yang dilihat.</div>
                    @endforelse
                </div>
            </div>

            {{-- Produk Paling Dibeli --}}
            <div class="bg-white rounded-xl border border-emerald-200 shadow-sm overflow-hidden">
                <div class="border-b border-emerald-100 px-4 py-3 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-sm">Produk Paling Dibeli</h3>
                        <a href="{{ route('admin.laporan.index') }}" class="text-[10px] font-semibold text-emerald-600 hover:text-emerald-700">Selengkapnya &rarr;</a>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($topSoldProducts as $idx => $product)
                    <div class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50/30">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-[9px] font-bold">{{ $idx + 1 }}</span>
                            <span class="text-xs font-semibold text-gray-900">{{ $product['product_name'] }}</span>
                        </div>
                        <span class="text-xs font-bold text-emerald-600">{{ number_format($product['total_sold']) }}x</span>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center text-[10px] text-gray-400">Belum ada data produk yang dibeli.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- D. Tindak Lanjut Prioritas --}}
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

    </div>

    <script type="application/json" id="dashboard-revenue-composition-data">@json($charts['revenueComposition'])</script>
    <script type="application/json" id="dashboard-unit-status-data">@json($charts['unitStatus'])</script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const parseJson = (id, fallback = {}) => {
                    const el = document.getElementById(id);
                    if (!el) return fallback;
                    try { return JSON.parse(el.textContent || ''); } catch { return fallback; }
                };

                const revenueComposition = parseJson('dashboard-revenue-composition-data');
                const unitStatus = parseJson('dashboard-unit-status-data');

                const palette = {
                    red: '#dc2626', navy: '#1e3a8a', amber: '#f59e0b',
                    emerald: '#059669', blue: '#2563eb', soft: '#e2e8f0'
                };

                const rupiah = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
                const numberId = (v) => new Intl.NumberFormat('id-ID').format(v || 0);

                const makeDonut = (config) => {
                    const hasData = (config.series || []).some(v => Number(v) > 0);
                    return {
                        chart: { type: 'donut', height: 160, toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
                        series: hasData ? config.series : [1],
                        labels: hasData ? config.labels : ['Belum Ada Data'],
                        colors: hasData ? config.colors : [palette.soft],
                        stroke: { width: 0 },
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom', fontSize: '10px', labels: { colors: '#64748b' } },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '75%',
                                    labels: {
                                        show: true,
                                        name: { show: true, color: '#94a3b8', fontSize: '9px' },
                                        value: { show: true, color: '#0f172a', fontSize: '14px', fontWeight: 700, formatter: (v) => config.valueFormatter ? config.valueFormatter(v) : v },
                                        total: { show: true, label: config.totalLabel, color: '#94a3b8', fontSize: '9px', formatter: () => config.totalFormatter(config.series || []) }
                                    }
                                }
                            }
                        }
                    };
                };

                new ApexCharts(document.querySelector('#revenue-composition-chart'), makeDonut({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: [palette.red, palette.navy, palette.amber],
                    totalLabel: 'Total',
                    totalFormatter: (s) => rupiah(s.reduce((a, b) => a + Number(b || 0), 0)),
                    valueFormatter: (v) => rupiah(v)
                })).render();

                new ApexCharts(document.querySelector('#unit-status-chart'), makeDonut({
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: [palette.emerald, palette.amber, palette.red],
                    totalLabel: 'Unit',
                    totalFormatter: (s) => numberId(s.reduce((a, b) => a + Number(b || 0), 0)),
                    valueFormatter: (v) => numberId(v)
                })).render();
            });
        </script>
    @endpush
</x-app-layout>