<?php

namespace App\Services;

use App\Models\Pengeluaran;
use App\Models\UnitApar;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AdminAnalyticsService
{
    private const REVENUE_LABELS = [
        'Penjualan Produk',
        'Service APAR',
        'Refill APAR',
    ];

    private const REVENUE_COLORS = [
        '#ef4444',
        '#2563eb',
        '#f59e0b',
    ];

    private const UNIT_STATUS_LABELS = [
        'Aktif',
        'Akan Expired',
        'Expired',
    ];

    private const UNIT_STATUS_COLORS = [
        '#10b981',
        '#f59e0b',
        '#dc2626',
    ];

    private const MONTH_LABELS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private const MONTH_SHORT_LABELS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    private const PURCHASE_FALLBACK = [
        1 => 500000.0,
        2 => 800000.0,
        3 => 600000.0,
        4 => 1000000.0,
        5 => 700000.0,
        6 => 1200000.0,
        7 => 900000.0,
        8 => 1400000.0,
        9 => 1100000.0,
        10 => 1600000.0,
        11 => 1300000.0,
        12 => 1800000.0,
    ];

    private const EXPENSE_COLORS = [
        '#ef4444',
        '#f97316',
        '#f59e0b',
        '#fb7185',
        '#94a3b8',
    ];

    public function __construct(private readonly FinalRevenueService $finalRevenue)
    {
    }

    public function revenueComposition(
        ?string $from = null,
        ?string $to = null,
        ?int $pelangganId = null,
        string $scopeLabel = 'Semua transaksi dengan pembayaran valid'
    ): array {
        $breakdown = $this->finalRevenue->breakdown($from, $to, $pelangganId);

        return [
            'labels' => self::REVENUE_LABELS,
            'series' => [
                (float) ($breakdown['product'] ?? 0),
                (float) ($breakdown['service'] ?? 0),
                (float) ($breakdown['refill'] ?? 0),
            ],
            'colors' => self::REVENUE_COLORS,
            'scopeLabel' => $scopeLabel,
            'totalLabel' => 'Total',
        ];
    }

    public function unitStatus(?CarbonInterface $today = null): array
    {
        $today = $today ? Carbon::instance($today) : Carbon::today();
        $expiringLimit = $today->copy()->addDays(30);

        return [
            'labels' => self::UNIT_STATUS_LABELS,
            'series' => [
                UnitApar::query()->whereDate('tgl_expired', '>', $expiringLimit)->count(),
                UnitApar::query()->whereBetween('tgl_expired', [$today, $expiringLimit])->count(),
                UnitApar::query()->whereDate('tgl_expired', '<', $today)->count(),
            ],
            'colors' => self::UNIT_STATUS_COLORS,
            'totalLabel' => 'Total Unit',
        ];
    }

    public function monthlyPurchases(?CarbonInterface $referenceDate = null): array
    {
        $referenceDate = $referenceDate ? Carbon::instance($referenceDate) : now();
        $year = (int) $referenceDate->year;
        $monthlyTotals = array_fill(1, 12, 0.0);

        Pengeluaran::query()
            ->whereIn('jenis_pengeluaran', [
                Pengeluaran::JENIS_PEMBELIAN_APAR,
                Pengeluaran::JENIS_PEMBELIAN_REFILL,
                Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            ])
            ->whereYear('tanggal', $year)
            ->get(['tanggal', 'nominal', 'total'])
            ->each(function (Pengeluaran $pengeluaran) use (&$monthlyTotals): void {
                $month = (int) optional($pengeluaran->tanggal)->format('n');

                if ($month < 1 || $month > 12) {
                    return;
                }

                $monthlyTotals[$month] += (float) $pengeluaran->effective_amount;
            });

        $hasRealData = collect($monthlyTotals)->contains(fn (float $total) => $total > 0);

        if (! $hasRealData) {
            $monthlyTotals = self::PURCHASE_FALLBACK;
        }

        return [
            'labels' => array_values(self::MONTH_LABELS),
            'shortLabels' => array_values(self::MONTH_SHORT_LABELS),
            'series' => array_values($monthlyTotals),
            'year' => $year,
            'isFallback' => ! $hasRealData,
            'sourceLabel' => $hasRealData
                ? 'Menggunakan total nominal pengeluaran pembelian stok yang sudah tersimpan pada tahun berjalan.'
                : 'Menggunakan data visual sementara karena belum ada pengeluaran pembelian stok pada tahun berjalan.',
            'valueLabel' => 'Total Pembelian',
            'lineColor' => '#dc2626',
            'lineFill' => '#fecaca',
        ];
    }

    public function expenseBreakdown(Collection $pengeluarans): array
    {
        $amounts = $pengeluarans
            ->groupBy(fn (Pengeluaran $pengeluaran) => $pengeluaran->jenis_pengeluaran_label)
            ->map(fn (Collection $items) => (float) $items->sum('effective_amount'))
            ->sortDesc();
        $lastExpenseColor = self::EXPENSE_COLORS[count(self::EXPENSE_COLORS) - 1];

        return [
            'labels' => $amounts->keys()->values()->all(),
            'series' => $amounts->values()->all(),
            'colors' => collect(self::EXPENSE_COLORS)
                ->pad(max($amounts->count(), count(self::EXPENSE_COLORS)), $lastExpenseColor)
                ->take($amounts->count())
                ->values()
                ->all(),
            'scopeLabel' => 'Pengeluaran operasional yang tersimpan pada periode aktif.',
            'totalLabel' => 'Total',
        ];
    }

    public function cashflowTrend(?int $pelangganId = null, ?CarbonInterface $referenceDate = null, int $months = 6): array
    {
        $referenceDate = $referenceDate ? Carbon::instance($referenceDate) : now();
        $labels = [];
        $incomeSeries = [];
        $expenseSeries = [];
        $profitSeries = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $month = $referenceDate->copy()->subMonths($offset)->startOfMonth();
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();
            $breakdown = $this->finalRevenue->breakdown($start, $end, $pelangganId);

            $expense = (float) (Pengeluaran::query()
                ->whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->selectRaw('COALESCE(SUM('.Pengeluaran::effectiveAmountSql().'), 0) as total_pengeluaran')
                ->value('total_pengeluaran') ?? 0);

            $labels[] = $month->translatedFormat('M Y');
            $incomeSeries[] = (float) ($breakdown['total'] ?? 0);
            $expenseSeries[] = $expense;
            $profitSeries[] = (float) (($breakdown['total'] ?? 0) - $expense);
        }

        return [
            'labels' => $labels,
            'series' => [
                [
                    'name' => 'Pemasukan',
                    'data' => $incomeSeries,
                ],
                [
                    'name' => 'Pengeluaran',
                    'data' => $expenseSeries,
                ],
                [
                    'name' => 'Laba Bersih',
                    'data' => $profitSeries,
                ],
            ],
            'colors' => [
                '#10b981',
                '#ef4444',
                '#2563eb',
            ],
        ];
    }
}
