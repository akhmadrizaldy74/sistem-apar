<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
        $monthlyPurchases = $charts['monthlyPurchases'] ?? [
            'labels' => [],
            'shortLabels' => [],
            'series' => [],
            'year' => now()->year,
            'isFallback' => true,
            'sourceLabel' => '',
            'valueLabel' => 'Total Pembelian',
        ];
        $purchaseSeries = collect($monthlyPurchases['series'] ?? [])->map(fn ($value) => (float) $value)->values();
        $purchaseLabels = collect($monthlyPurchases['labels'] ?? [])->values();
        $purchasePeakIndex = $purchaseSeries->isNotEmpty() ? $purchaseSeries->search($purchaseSeries->max()) : null;
        $purchasePeakMonth = is_int($purchasePeakIndex) ? ($purchaseLabels[$purchasePeakIndex] ?? '-') : '-';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <div class="max-w-3xl">
                <p class="text-sm font-bold text-red-600">Dashboard Admin</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">Ringkasan Operasional</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500 md:text-[15px]">
                    Pantau data utama bisnis APAR dalam satu tampilan yang lebih nyaman dibaca untuk kerja harian di laptop maupun mobile.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1360px] space-y-6 text-[13px] md:text-sm">
        <div id="dashboard-kpi-grid">
            @include('dashboard.partials.kpi-cards', [
                'kpis' => $kpis,
                'visitorStats' => $visitorStats,
            ])
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900 md:text-lg">Analitik Ringkas</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Perbandingan pendapatan final dan status unit APAR dengan layout yang seimbang.</p>
            </div>
            <div class="grid gap-5 p-5 lg:grid-cols-2 sm:p-6">
                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Sumber Pendapatan</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                {{ $charts['revenueComposition']['scopeLabel'] ?? 'Semua transaksi selesai final' }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Final</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="revenue-composition-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="revenue-composition-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>

                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Status Unit APAR</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                Ringkasan unit aktif, mendekati masa expired, dan yang sudah expired.
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">Unit</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="unit-status-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="unit-status-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 md:text-lg">Grafik Pembelian Bulanan</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Perbandingan pembelian dari Januari sampai Desember.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                            Tahun {{ $monthlyPurchases['year'] ?? now()->year }}
                        </span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ !empty($monthlyPurchases['isFallback']) ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ !empty($monthlyPurchases['isFallback']) ? 'Data visual sementara' : 'Data pengeluaran tersimpan' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="space-y-5 p-5 sm:p-6">
                <div class="grid gap-3 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Total Pembelian Tahun Ini</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $formatRupiah($purchaseSeries->sum()) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Bulan Tertinggi</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $purchasePeakMonth }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <p class="text-xs font-bold text-slate-500">Catatan Data</p>
                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">{{ $monthlyPurchases['sourceLabel'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">
                    <div class="h-[310px] sm:h-[350px]">
                        <div id="monthly-purchases-chart" class="h-full w-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-violet-200 bg-white shadow-sm">
                <div class="border-b border-violet-100 bg-violet-50/40 px-5 py-4 sm:px-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900">Produk Paling Dilihat</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">Pantau produk yang paling sering menarik perhatian pengunjung.</p>
                        </div>
                        <a href="{{ route('admin.laporan.index') }}" class="text-sm font-bold text-violet-600 hover:text-violet-700">Lihat laporan</a>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($topViewedProducts as $idx => $product)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-slate-50 sm:px-6">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-black text-violet-700">{{ $idx + 1 }}</span>
                                <span class="truncate text-sm font-semibold text-slate-900">{{ $product['product_name'] }}</span>
                            </div>
                            <span class="shrink-0 text-sm font-black text-violet-600">{{ number_format($product['view_count']) }}x</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm font-medium text-slate-500 sm:px-6">Belum ada data produk yang dilihat.</div>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">
                <div class="border-b border-emerald-100 bg-emerald-50/40 px-5 py-4 sm:px-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-black text-slate-900">Produk Paling Dibeli</h3>
                            <p class="mt-1 text-sm font-medium text-slate-500">Lihat produk yang paling banyak terjual dari transaksi final.</p>
                        </div>
                        <a href="{{ route('admin.laporan.index') }}" class="text-sm font-bold text-emerald-600 hover:text-emerald-700">Lihat laporan</a>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($topSoldProducts as $idx => $product)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-slate-50 sm:px-6">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-emerald-700">{{ $idx + 1 }}</span>
                                <span class="truncate text-sm font-semibold text-slate-900">{{ $product['product_name'] }}</span>
                            </div>
                            <span class="shrink-0 text-sm font-black text-emerald-600">{{ number_format($product['total_sold']) }}x</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm font-medium text-slate-500 sm:px-6">Belum ada data produk yang dibeli.</div>
                    @endforelse
                </div>
            </section>
        </div>

    </div>

    <script type="application/json" id="dashboard-revenue-composition-data">@json($charts['revenueComposition'])</script>
    <script type="application/json" id="dashboard-unit-status-data">@json($charts['unitStatus'])</script>
    <script type="application/json" id="dashboard-monthly-purchases-data">@json($charts['monthlyPurchases'])</script>

    @push('scripts')
        @include('admin.partials.analytics-charts-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chartKit = window.AdminAnalyticsCharts;
                let selectedRevenuePeriod = 'month';

                if (!chartKit) {
                    return;
                }

                const applyRevenuePeriod = (root = document) => {
                    const revenueCard = root.querySelector('[data-revenue-card]');

                    if (!revenueCard) {
                        return;
                    }

                    const period = selectedRevenuePeriod === 'overall' ? 'overall' : 'month';
                    const amount = period === 'overall' ? revenueCard.dataset.overallValue : revenueCard.dataset.monthValue;
                    const hint = period === 'overall' ? revenueCard.dataset.overallHint : revenueCard.dataset.monthHint;
                    const amountTarget = revenueCard.querySelector('[data-revenue-amount]');
                    const hintTarget = revenueCard.querySelector('[data-revenue-hint]');
                    const selectTarget = revenueCard.querySelector('[data-revenue-period]');

                    if (amountTarget) {
                        amountTarget.textContent = amount ?? '';
                    }

                    if (hintTarget) {
                        hintTarget.textContent = hint ?? '';
                    }

                    if (selectTarget) {
                        selectTarget.value = period;
                    }
                };

                const revenueComposition = chartKit.parseJson('dashboard-revenue-composition-data');
                const unitStatus = chartKit.parseJson('dashboard-unit-status-data');
                const monthlyPurchases = chartKit.parseJson('dashboard-monthly-purchases-data');

                chartKit.createChart('#revenue-composition-chart', chartKit.makeCurrencyDonutChart({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    totalLabel: revenueComposition.totalLabel || 'Total',
                }));

                chartKit.renderLegend(document.querySelector('#revenue-composition-legend'), {
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    valueFormatter: (value) => chartKit.rupiah(value),
                    emptyLabel: 'Belum ada data pendapatan final.',
                });

                chartKit.createChart('#unit-status-chart', chartKit.makeCountDonutChart({
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: unitStatus.colors,
                    totalLabel: unitStatus.totalLabel || 'Total Unit',
                    unitLabel: 'unit',
                }));

                chartKit.renderLegend(document.querySelector('#unit-status-legend'), {
                    labels: unitStatus.labels,
                    series: unitStatus.series,
                    colors: unitStatus.colors,
                    valueFormatter: (value) => `${chartKit.numberId(value)} unit`,
                    emptyLabel: 'Belum ada data status unit.',
                });

                chartKit.createChart('#monthly-purchases-chart', chartKit.makeMonthlyPurchasesChart({
                    labels: monthlyPurchases.labels,
                    shortLabels: monthlyPurchases.shortLabels,
                    series: monthlyPurchases.series,
                    year: monthlyPurchases.year,
                    valueLabel: monthlyPurchases.valueLabel,
                    lineColor: monthlyPurchases.lineColor,
                    lineFill: monthlyPurchases.lineFill,
                }));

                applyRevenuePeriod();

                document.addEventListener('change', (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLSelectElement) || !target.matches('[data-revenue-period]')) {
                        return;
                    }

                    selectedRevenuePeriod = target.value === 'overall' ? 'overall' : 'month';
                    applyRevenuePeriod();
                });

                window.createPollingUpdater({
                    url: @js(route('admin.realtime.dashboard')),
                    interval: 10000,
                    onSuccess(payload) {
                        const kpiGrid = document.getElementById('dashboard-kpi-grid');

                        if (kpiGrid && typeof payload.kpi_html === 'string') {
                            kpiGrid.innerHTML = payload.kpi_html;
                            applyRevenuePeriod(kpiGrid);
                        }
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
