@once
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (() => {
            if (window.AdminAnalyticsCharts) {
                return;
            }

            const palette = {
                rose: '#ef4444',
                blue: '#2563eb',
                amber: '#f59e0b',
                emerald: '#10b981',
                red: '#dc2626',
                orange: '#f97316',
                slate: '#94a3b8',
                soft: '#cbd5e1',
            };

            const parseJson = (id, fallback = {}) => {
                const element = document.getElementById(id);

                if (!element) {
                    return fallback;
                }

                try {
                    return JSON.parse(element.textContent || '');
                } catch {
                    return fallback;
                }
            };

            const rupiah = (value) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(Number(value) || 0);

            const numberId = (value) => new Intl.NumberFormat('id-ID').format(Number(value) || 0);
            const percentId = (value) => `${Number(value || 0).toFixed(1)}%`;

            const compactNumber = (value) => {
                const amount = Number(value) || 0;

                if (Math.abs(amount) >= 1000000000) {
                    return `Rp ${(amount / 1000000000).toFixed(1).replace('.0', '')} M`;
                }

                if (Math.abs(amount) >= 1000000) {
                    return `Rp ${(amount / 1000000).toFixed(1).replace('.0', '')} jt`;
                }

                if (Math.abs(amount) >= 1000) {
                    return `Rp ${(amount / 1000).toFixed(1).replace('.0', '')} rb`;
                }

                return `Rp ${numberId(amount)}`;
            };

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const createChart = (selector, options) => {
                if (typeof ApexCharts === 'undefined') {
                    return null;
                }

                const target = document.querySelector(selector);

                if (!target) {
                    return null;
                }

                const chart = new ApexCharts(target, options);
                chart.render();

                return chart;
            };

            const renderLegend = (container, config) => {
                if (!container) {
                    return;
                }

                const labels = config.labels || [];
                const series = (config.series || []).map((value) => Number(value || 0));
                const colors = config.colors || [];
                const total = series.reduce((sum, value) => sum + value, 0);

                if (total <= 0) {
                    container.innerHTML = `
                        <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500">
                            ${escapeHtml(config.emptyLabel || 'Belum ada data')}
                        </div>
                    `;
                    return;
                }

                container.innerHTML = labels.map((label, index) => {
                    const value = series[index] || 0;
                    const percentage = total > 0 ? (value / total) * 100 : 0;
                    const color = colors[index] || palette.soft;
                    const formattedValue = config.valueFormatter ? config.valueFormatter(value) : numberId(value);

                    return `
                        <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="mt-1 inline-block h-3 w-3 shrink-0 rounded-full" style="background-color:${color}"></span>
                                <span class="truncate text-sm font-semibold text-slate-700">${escapeHtml(label)}</span>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-black text-slate-900">${escapeHtml(formattedValue)}</p>
                                <p class="text-xs font-semibold text-slate-500">${escapeHtml(percentId(percentage))}</p>
                            </div>
                        </div>
                    `;
                }).join('');
            };

            const baseDonutOptions = {
                chart: {
                    type: 'donut',
                    height: 260,
                    width: '100%',
                    parentHeightOffset: 0,
                    toolbar: { show: false },
                    fontFamily: 'Manrope, system-ui, sans-serif',
                    animations: { enabled: true, easing: 'easeinout', speed: 800 },
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { width: 4, colors: ['#ffffff'] },
                states: {
                    hover: { filter: { type: 'none' } },
                    active: { filter: { type: 'none' } },
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        customScale: 0.92,
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    color: '#64748b',
                                    offsetY: -10,
                                },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: 800,
                                    color: '#0f172a',
                                    offsetY: 10,
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    color: '#64748b',
                                },
                            },
                        },
                    },
                },
                tooltip: {
                    enabled: true,
                    style: { fontSize: '13px' },
                },
            };

            const makeCurrencyDonutChart = (config) => {
                const series = (config.series || []).map((value) => Number(value || 0));
                const hasData = series.some((value) => value > 0);
                const total = series.reduce((sum, value) => sum + value, 0);

                return {
                    ...baseDonutOptions,
                    series: hasData ? series : [1],
                    labels: hasData ? (config.labels || []) : ['Belum Ada Data'],
                    colors: hasData ? (config.colors || []) : [palette.soft],
                    tooltip: {
                        ...baseDonutOptions.tooltip,
                        y: {
                            formatter: (value) => {
                                if (!hasData) {
                                    return 'Belum ada data';
                                }

                                const percentage = total > 0 ? ((Number(value) / total) * 100) : 0;
                                return `${rupiah(value)} (${percentId(percentage)})`;
                            },
                        },
                    },
                    plotOptions: {
                        ...baseDonutOptions.plotOptions,
                        pie: {
                            ...baseDonutOptions.plotOptions.pie,
                            donut: {
                                ...baseDonutOptions.plotOptions.pie.donut,
                                labels: {
                                    ...baseDonutOptions.plotOptions.pie.donut.labels,
                                    value: {
                                        ...baseDonutOptions.plotOptions.pie.donut.labels.value,
                                        formatter: (value) => hasData ? compactNumber(value) : compactNumber(0),
                                    },
                                    total: {
                                        ...baseDonutOptions.plotOptions.pie.donut.labels.total,
                                        label: config.totalLabel || 'Total',
                                        formatter: (context) => {
                                            if (!hasData) {
                                                return compactNumber(0);
                                            }

                                            const sum = context.globals.seriesTotals.reduce((carry, value) => carry + Number(value || 0), 0);
                                            return compactNumber(sum);
                                        },
                                    },
                                },
                            },
                        },
                    },
                };
            };

            const makeCountDonutChart = (config) => {
                const series = (config.series || []).map((value) => Number(value || 0));
                const hasData = series.some((value) => value > 0);
                const unitLabel = config.unitLabel || 'unit';

                return {
                    ...baseDonutOptions,
                    series: hasData ? series : [1],
                    labels: hasData ? (config.labels || []) : ['Belum Ada Data'],
                    colors: hasData ? (config.colors || []) : [palette.soft],
                    tooltip: {
                        ...baseDonutOptions.tooltip,
                        y: {
                            formatter: (value) => hasData ? `${numberId(value)} ${unitLabel}` : 'Belum ada data',
                        },
                    },
                    plotOptions: {
                        ...baseDonutOptions.plotOptions,
                        pie: {
                            ...baseDonutOptions.plotOptions.pie,
                            donut: {
                                ...baseDonutOptions.plotOptions.pie.donut,
                                labels: {
                                    ...baseDonutOptions.plotOptions.pie.donut.labels,
                                    value: {
                                        ...baseDonutOptions.plotOptions.pie.donut.labels.value,
                                        formatter: (value) => hasData ? numberId(value) : numberId(0),
                                    },
                                    total: {
                                        ...baseDonutOptions.plotOptions.pie.donut.labels.total,
                                        label: config.totalLabel || 'Total',
                                        formatter: () => hasData ? numberId(series.reduce((sum, value) => sum + value, 0)) : numberId(0),
                                    },
                                },
                            },
                        },
                    },
                };
            };

            const makeMonthlyPurchasesChart = (config) => {
                const fullLabels = config.labels || [];
                const shortLabels = config.shortLabels || fullLabels;
                const series = (config.series || []).map((value) => Number(value || 0));

                return {
                    chart: {
                        type: 'line',
                        height: '100%',
                        parentHeightOffset: 0,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'Manrope, system-ui, sans-serif',
                        animations: { enabled: true, easing: 'easeinout', speed: 700 },
                    },
                    series: [
                        {
                            name: config.valueLabel || 'Total Pembelian',
                            data: series,
                        },
                    ],
                    colors: [config.lineColor || palette.red],
                    stroke: {
                        width: 3.5,
                        curve: 'smooth',
                        lineCap: 'round',
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.28,
                            opacityTo: 0.04,
                            stops: [0, 95, 100],
                            colorStops: [
                                [
                                    { offset: 0, color: config.lineFill || '#fecaca', opacity: 0.28 },
                                    { offset: 100, color: config.lineFill || '#fecaca', opacity: 0.04 },
                                ],
                            ],
                        },
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 0,
                        hover: { size: 6 },
                    },
                    dataLabels: { enabled: false },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4,
                        padding: { left: 16, right: 22, top: 8, bottom: 6 },
                    },
                    xaxis: {
                        categories: shortLabels,
                        tickPlacement: 'between',
                        labels: {
                            rotate: 0,
                            hideOverlappingLabels: false,
                            trim: false,
                            style: {
                                fontSize: '12px',
                                fontWeight: 700,
                                colors: '#64748b',
                            },
                        },
                        axisBorder: { color: '#cbd5e1' },
                        axisTicks: { color: '#cbd5e1' },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactNumber(value),
                            style: {
                                fontSize: '12px',
                                fontWeight: 700,
                                colors: ['#64748b'],
                            },
                        },
                    },
                    tooltip: {
                        theme: 'light',
                        style: { fontSize: '13px' },
                        x: {
                            formatter: (_, options) => {
                                const monthLabel = fullLabels[options.dataPointIndex] || shortLabels[options.dataPointIndex] || '';
                                return `${monthLabel} ${config.year || ''}`.trim();
                            },
                        },
                        y: {
                            formatter: (value) => rupiah(value),
                        },
                    },
                    noData: {
                        text: 'Belum ada data pembelian',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: {
                            color: '#64748b',
                            fontSize: '14px',
                            fontFamily: 'Manrope, system-ui, sans-serif',
                        },
                    },
                    responsive: [
                        {
                            breakpoint: 640,
                            options: {
                                chart: { height: 300 },
                                grid: {
                                    padding: { left: 10, right: 14, top: 8, bottom: 2 },
                                },
                            },
                        },
                    ],
                };
            };

            const makeCashflowTrendChart = (config) => {
                return {
                    chart: {
                        type: 'line',
                        height: '100%',
                        parentHeightOffset: 0,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'Manrope, system-ui, sans-serif',
                        animations: { enabled: true, easing: 'easeinout', speed: 700 },
                    },
                    series: config.series || [],
                    colors: config.colors || [palette.emerald, palette.red, palette.blue],
                    stroke: {
                        width: [4, 4, 4],
                        curve: 'smooth',
                    },
                    markers: {
                        size: 4,
                        hover: { size: 6 },
                    },
                    fill: {
                        type: 'solid',
                        opacity: [0.08, 0.08, 0.08],
                    },
                    dataLabels: { enabled: false },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        fontSize: '13px',
                        fontWeight: 700,
                        labels: { colors: '#475569' },
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4,
                        padding: { left: 8, right: 8, top: 8, bottom: 0 },
                    },
                    xaxis: {
                        categories: config.labels || [],
                        labels: {
                            rotate: 0,
                            trim: false,
                            style: {
                                fontSize: '12px',
                                fontWeight: 700,
                                colors: '#64748b',
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            formatter: (value) => compactNumber(value),
                            style: {
                                fontSize: '12px',
                                fontWeight: 700,
                                colors: ['#64748b'],
                            },
                        },
                    },
                    tooltip: {
                        theme: 'light',
                        shared: true,
                        intersect: false,
                        style: { fontSize: '13px' },
                        y: {
                            formatter: (value) => rupiah(value),
                        },
                    },
                };
            };

            window.AdminAnalyticsCharts = {
                palette,
                parseJson,
                rupiah,
                numberId,
                percentId,
                compactNumber,
                createChart,
                renderLegend,
                makeCurrencyDonutChart,
                makeCountDonutChart,
                makeMonthlyPurchasesChart,
                makeCashflowTrendChart,
            };
        })();
    </script>
@endonce
