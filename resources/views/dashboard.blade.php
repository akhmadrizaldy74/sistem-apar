<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
        $monitoringPrioritas = $kpis['unitAkanExpired'] + $kpis['unitExpired'] + ($notifications['urgentOrdersCount'] ?? 0);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-600">Dashboard</p>
                <h2 class="mt-1 text-xl font-black text-slate-900 md:text-2xl">Ringkasan Operasional</h2>
                <p class="mt-0.5 text-sm text-slate-500">Pantau data utama bisnis APAR Anda.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">

        {{-- A. KPI Cards - Compact Grid --}}
        <div id="dashboard-kpi-grid">
            @include('dashboard.partials.kpi-cards', [
                'kpis' => $kpis,
                'notifications' => $notifications,
                'visitorStats' => $visitorStats,
                'monitoringPrioritas' => $monitoringPrioritas,
            ])
        </div>

        {{-- B. Analitik Ringkas - Chart (2 Charts Only) --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 px-4 py-3 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm">Analitik Ringkas</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                <div class="text-center">
                    <h4 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Sumber Pendapatan</h4>
                    <p class="mb-2 text-[10px] text-slate-400">{{ $charts['revenueComposition']['scopeLabel'] ?? 'Semua transaksi selesai final' }}</p>
                    <div class="flex h-[230px] items-center justify-center overflow-hidden">
                        <div id="revenue-composition-chart" class="mx-auto" style="height: 220px; width: 220px; max-height: 220px; max-width: 220px;"></div>
                    </div>
                    <div id="revenue-composition-legend" class="mx-auto mt-2 flex w-fit flex-col items-start gap-1 text-[10px] font-semibold text-slate-600"></div>
                </div>
                <div class="text-center">
                    <h4 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Status Unit APAR</h4>
                    <div class="flex h-[230px] items-center justify-center overflow-hidden">
                        <div id="unit-status-chart" class="mx-auto" style="height: 220px; width: 220px; max-height: 220px; max-width: 220px;"></div>
                    </div>
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
        <div id="dashboard-priority-panel">
            @include('dashboard.partials.priority-panel', [
                'kpis' => $kpis,
                'notifications' => $notifications,
                'monitoringPrioritas' => $monitoringPrioritas,
            ])
        </div>

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
                    emerald: '#059669', blue: '#2563eb', soft: '#e2e8f0',
                    rose: '#dc2626', yellow: '#f59e0b'
                };

                const rupiah = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v || 0);
                const numberId = (v) => new Intl.NumberFormat('id-ID').format(v || 0);
                const percentId = (v) => `${Number(v || 0).toFixed(1)}%`;

                const makeRevenueChart = (config) => {
                    const hasData = (config.series || []).some(v => Number(v) > 0);
                    const total = (config.series || []).reduce((a, b) => a + Number(b || 0), 0);

                    return {
                        chart: {
                            type: 'donut',
                            height: 220,
                            width: 220,
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 }
                        },
                        series: hasData ? config.series : [1],
                        labels: hasData ? config.labels : ['Belum Ada Data'],
                        colors: hasData ? config.colors : [palette.soft],
                        stroke: { width: 3, colors: ['#ffffff'] },
                        dataLabels: { enabled: false },
                        legend: { show: false },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: (val) => {
                                    if (!hasData) return 'Belum ada data';
                                    const percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return rupiah(val) + ' (' + percent + '%)';
                                }
                            }
                        },
                        states: {
                            hover: { filter: { type: 'none' } },
                            active: { filter: { type: 'none' } }
                        },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                customScale: 0.82,
                                donut: {
                                    size: '72%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            offsetY: -8
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '14px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 700,
                                            color: '#0f172a',
                                            offsetY: 6,
                                            formatter: (val) => hasData ? rupiah(val) : rupiah(0)
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Total',
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            formatter: (w) => {
                                                if (!hasData) return rupiah(0);
                                                const sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                return rupiah(sum);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    };
                };

                const makeDonut = (config) => {
                    const hasData = (config.series || []).some(v => Number(v) > 0);
                    return {
                        chart: { type: 'donut', height: 220, width: 220, toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
                        series: hasData ? config.series : [1],
                        labels: hasData ? config.labels : ['Belum Ada Data'],
                        colors: hasData ? config.colors : [palette.soft],
                        stroke: { width: 3, colors: ['#ffffff'] },
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom', fontSize: '10px', labels: { colors: '#64748b' } },
                        states: {
                            hover: { filter: { type: 'none' } },
                            active: { filter: { type: 'none' } }
                        },
                        plotOptions: {
                            pie: {
                                expandOnClick: false,
                                customScale: 1.0,
                                donut: {
                                    size: '72%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            offsetY: -8
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '14px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 700,
                                            color: '#0f172a',
                                            offsetY: 6,
                                            formatter: (v) => config.valueFormatter ? config.valueFormatter(v) : v
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: config.totalLabel,
                                            fontSize: '11px',
                                            fontFamily: 'system-ui, sans-serif',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            formatter: () => config.totalFormatter(config.series || [])
                                        }
                                    }
                                }
                            }
                        }
                    };
                };

                const escapeHtml = (value) => String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const renderRevenueLegend = (container, config) => {
                    if (!container) {
                        return;
                    }

                    const labels = config.labels || [];
                    const series = (config.series || []).map((value) => Number(value || 0));
                    const colors = config.colors || [];
                    const total = series.reduce((sum, value) => sum + value, 0);

                    if (total <= 0) {
                        container.innerHTML = `
                            <div class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-semibold text-slate-500">
                                Belum ada data pendapatan
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = labels.map((label, index) => {
                        const value = series[index] || 0;
                        const percentage = total > 0 ? (value / total) * 100 : 0;
                        const color = colors[index] || palette.soft;

                        return `
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color:${color}"></span>
                                <span class="text-slate-700">${escapeHtml(label)} — ${rupiah(value)} — ${percentId(percentage)}</span>
                            </div>
                        `;
                    }).join('');
                };

                new ApexCharts(document.querySelector('#revenue-composition-chart'), makeRevenueChart({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: [palette.rose, palette.blue, palette.yellow],
                })).render();

                renderRevenueLegend(document.querySelector('#revenue-composition-legend'), {
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: [palette.rose, palette.blue, palette.yellow],
                });

                new ApexCharts(document.querySelector('#unit-status-chart'), makeDonut({
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: [palette.emerald, palette.amber, palette.red],
                    totalLabel: 'Unit',
                    totalFormatter: (s) => numberId(s.reduce((a, b) => a + Number(b || 0), 0)),
                    valueFormatter: (v) => numberId(v)
                })).render();

                window.createPollingUpdater({
                    url: @js(route('admin.realtime.dashboard')),
                    interval: 10000,
                    onSuccess(payload) {
                        const kpiGrid = document.getElementById('dashboard-kpi-grid');
                        const priorityPanel = document.getElementById('dashboard-priority-panel');

                        if (kpiGrid && typeof payload.kpi_html === 'string') {
                            kpiGrid.innerHTML = payload.kpi_html;
                        }

                        if (priorityPanel && typeof payload.priority_html === 'string') {
                            priorityPanel.innerHTML = payload.priority_html;
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
