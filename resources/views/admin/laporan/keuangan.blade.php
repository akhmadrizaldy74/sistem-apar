<x-app-layout>
    @php
        $formatRupiah = static fn ($amount) => 'Rp ' . number_format((float) $amount, 0, ',', '.');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold text-red-600">Laporan Keuangan</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">Ringkasan Keuangan</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-500 md:text-[15px]">
                    Pemasukan final, pengeluaran operasional, dan arus kas bulanan dengan visual yang konsisten dengan dashboard admin.
                </p>
            </div>
            <a href="{{ route('admin.laporan.keuangan.pdf', request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-700 px-5 py-3 text-sm font-black text-white shadow-xl shadow-red-700/20 transition hover:bg-red-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak PDF
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1360px] space-y-6 text-[13px] md:text-sm">
        @include('admin.laporan.partials.tabs')

        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto]">
                <div>
                    <label for="tanggal_dari" class="mb-2 block text-sm font-bold text-slate-600">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" id="tanggal_dari" value="{{ $filters['tanggal_dari'] }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="tanggal_sampai" class="mb-2 block text-sm font-bold text-slate-600">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" id="tanggal_sampai" value="{{ $filters['tanggal_sampai'] }}"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="pelanggan_id" class="mb-2 block text-sm font-bold text-slate-600">Pelanggan</label>
                    <select name="pelanggan_id" id="pelanggan_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                        <option value="">Semua Pelanggan</option>
                        @foreach($pelanggans as $pelanggan)
                            <option value="{{ $pelanggan->id }}" @selected($filters['pelanggan_id'] === $pelanggan->id)>{{ $pelanggan->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-3 lg:justify-end">
                    <button type="submit" class="rounded-2xl bg-red-700 px-5 py-3 text-sm font-black text-white transition hover:bg-red-800">
                        Tampilkan
                    </button>
                    <a href="{{ route('admin.laporan.keuangan', []) }}" class="rounded-2xl border border-red-200 px-5 py-3 text-center text-sm font-black text-red-700 transition hover:bg-red-50">
                        Reset Filter
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-sm">
                <p class="text-sm font-bold text-emerald-700">Total Pemasukan</p>
                <p class="mt-2 text-2xl font-black text-emerald-800">{{ $formatRupiah($totals['total_pemasukan']) }}</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50/80 p-5 shadow-sm">
                <p class="text-sm font-bold text-red-700">Total Pengeluaran</p>
                <p class="mt-2 text-2xl font-black text-red-800">{{ $formatRupiah($totals['total_pengeluaran']) }}</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50/80 p-5 shadow-sm">
                <p class="text-sm font-bold text-blue-700">Laba Bersih</p>
                <p class="mt-2 text-2xl font-black {{ $totals['laba_bersih'] >= 0 ? 'text-blue-800' : 'text-red-800' }}">{{ $formatRupiah($totals['laba_bersih']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-600">Total Transaksi</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totals['total_transaksi']) }}</p>
            </div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900 md:text-lg">Analitik Keuangan</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Diagram donut dan arus kas dibuat searah dengan gaya dashboard agar mudah dibandingkan.</p>
            </div>
            <div class="grid gap-5 p-5 xl:grid-cols-2 sm:p-6">
                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Sumber Pendapatan Final</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                Diagram ini memakai label, warna, dan perhitungan yang sama dengan dashboard admin.
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Final</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="finance-revenue-composition-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="finance-revenue-composition-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>

                <article class="flex min-h-[360px] flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="text-base font-black text-slate-900">Komposisi Pengeluaran</h4>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">
                                Pengeluaran APAR, refill, peralatan, dan operasional ditampilkan dengan warna yang tetap konsisten.
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600">Operasional</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-col justify-between gap-4">
                        <div class="flex min-h-[250px] items-center justify-center">
                            <div id="finance-expense-breakdown-chart" class="h-[250px] w-full max-w-[290px]"></div>
                        </div>
                        <div id="finance-expense-breakdown-legend" class="grid gap-2 text-sm font-semibold text-slate-600"></div>
                    </div>
                </article>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 md:text-lg">Grafik Arus Kas Bulanan</h3>
                        <p class="mt-1 text-sm font-medium text-slate-500">Perbandingan pemasukan, pengeluaran, dan laba bersih dalam enam bulan terakhir.</p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-3">
                            <p class="text-xs font-bold text-emerald-700">Penjualan Produk</p>
                            <p class="mt-1 text-sm font-black text-emerald-800">{{ $formatRupiah($incomeBreakdown['produk']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/80 px-4 py-3">
                            <p class="text-xs font-bold text-blue-700">Service APAR</p>
                            <p class="mt-1 text-sm font-black text-blue-800">{{ $formatRupiah($incomeBreakdown['service']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-3">
                            <p class="text-xs font-bold text-amber-700">Refill APAR</p>
                            <p class="mt-1 text-sm font-black text-amber-800">{{ $formatRupiah($incomeBreakdown['refill']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4">
                    <div class="h-[320px] sm:h-[360px]">
                        <div id="finance-cashflow-trend-chart" class="h-full w-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900">Detail Laporan Keuangan</h3>
                <p class="mt-1 text-sm font-medium text-slate-500">Rincian transaksi pemasukan final dan pengeluaran operasional dalam periode aktif.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Tanggal</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Jenis</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Keterangan</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Pelanggan</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Status</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Sumber</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($records as $record)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3 text-sm font-semibold text-slate-900">{{ $record['tanggal_label'] }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $record['direction'] === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $record['jenis'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $record['keterangan'] }}</td>
                                <td class="px-5 py-3 text-sm font-semibold text-slate-700">{{ $record['pelanggan'] }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $record['status'] }}</td>
                                <td class="px-5 py-3 text-sm text-slate-700">{{ $record['source'] }}</td>
                                <td class="px-5 py-3 text-sm font-black {{ $record['direction'] === 'in' ? 'text-emerald-700' : 'text-red-700' }}">
                                    {{ $record['direction'] === 'in' ? '' : '- ' }}{{ $formatRupiah($record['nominal']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada transaksi sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script type="application/json" id="finance-revenue-composition-data">@json($charts['revenueComposition'])</script>
    <script type="application/json" id="finance-expense-breakdown-data">@json($charts['expenseBreakdown'])</script>
    <script type="application/json" id="finance-cashflow-trend-data">@json($charts['cashflowTrend'])</script>

    @push('scripts')
        @include('admin.partials.analytics-charts-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const chartKit = window.AdminAnalyticsCharts;

                if (!chartKit) {
                    return;
                }

                const revenueComposition = chartKit.parseJson('finance-revenue-composition-data');
                const expenseBreakdown = chartKit.parseJson('finance-expense-breakdown-data');
                const cashflowTrend = chartKit.parseJson('finance-cashflow-trend-data');

                chartKit.createChart('#finance-revenue-composition-chart', chartKit.makeCurrencyDonutChart({
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    totalLabel: revenueComposition.totalLabel || 'Total',
                }));

                chartKit.renderLegend(document.querySelector('#finance-revenue-composition-legend'), {
                    labels: revenueComposition.labels,
                    series: revenueComposition.series,
                    colors: revenueComposition.colors,
                    valueFormatter: (value) => chartKit.rupiah(value),
                    emptyLabel: 'Belum ada data pendapatan final.',
                });

                chartKit.createChart('#finance-expense-breakdown-chart', chartKit.makeCurrencyDonutChart({
                    labels: expenseBreakdown.labels,
                    series: expenseBreakdown.series,
                    colors: expenseBreakdown.colors,
                    totalLabel: expenseBreakdown.totalLabel || 'Total',
                }));

                chartKit.renderLegend(document.querySelector('#finance-expense-breakdown-legend'), {
                    labels: expenseBreakdown.labels,
                    series: expenseBreakdown.series,
                    colors: expenseBreakdown.colors,
                    valueFormatter: (value) => chartKit.rupiah(value),
                    emptyLabel: 'Belum ada data pengeluaran.',
                });

                chartKit.createChart('#finance-cashflow-trend-chart', chartKit.makeCashflowTrendChart({
                    labels: cashflowTrend.labels,
                    series: cashflowTrend.series,
                    colors: cashflowTrend.colors,
                }));
            });
        </script>
    @endpush
</x-app-layout>
