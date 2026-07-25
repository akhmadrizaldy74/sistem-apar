<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Support\RegisteredRefillUnitSupport;
use App\Models\UnitApar;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AdminAnalyticsService
{
    private const REVENUE_LABELS = ['Penjualan Produk', 'Layanan Service', 'Isi Ulang APAR'];

    private const REVENUE_COLORS = ['#2563eb', '#8b5cf6', '#d97706'];

    private const UNIT_STATUS_LABELS = ['Stok Normal', 'Mendekati Expired', 'Sudah Expired'];

    private const UNIT_STATUS_COLORS = ['#10b981', '#f59e0b', '#ef4444'];

    private const EXPENSE_COLORS = ['#dc2626', '#d97706', '#2563eb', '#8b5cf6', '#64748b'];

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
        $expiringLimit = $today->copy()->addDays(RegisteredRefillUnitSupport::REFILL_WARNING_DAYS);

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

        PurchaseOrder::query()
            ->whereYear('tanggal_po', $year)
            ->get()
            ->each(function (PurchaseOrder $po) use (&$monthlyTotals) {
                $month = (int) Carbon::parse($po->tanggal_po ?: $po->created_at)->month;
                if ($month >= 1 && $month <= 12) {
                    $monthlyTotals[$month] += (float) $po->total;
                }
            });

        $hasRealData = collect($monthlyTotals)->contains(fn (float $total) => $total > 0);

        return [
            'labels' => array_values(self::MONTH_LABELS),
            'shortLabels' => array_values(self::MONTH_SHORT_LABELS),
            'series' => array_values($monthlyTotals),
            'year' => $year,
            'isFallback' => false,
            'sourceLabel' => $hasRealData
                ? 'Menampilkan data real-time pengeluaran pembelian stok yang tersimpan pada tahun berjalan.'
                : 'Belum ada pengeluaran pembelian stok yang tersimpan pada tahun berjalan.',
            'valueLabel' => 'Total Pembelian',
            'lineColor' => '#dc2626',
            'lineFill' => '#fecaca',
        ];
    }

    public function expenseBreakdown(Collection $purchaseOrders): array
    {
        $amounts = PurchaseOrderDetail::query()
            ->whereHas('purchaseOrder', function ($q) use ($purchaseOrders) {
                if ($purchaseOrders->isNotEmpty()) {
                    $q->whereIn('id', $purchaseOrders->pluck('id'));
                }
            })
            ->get()
            ->groupBy('kategori')
            ->map(fn (Collection $items) => (float) $items->sum('subtotal'))
            ->sortDesc();

        $lastExpenseColor = self::EXPENSE_COLORS[count(self::EXPENSE_COLORS) - 1];

        return [
            'labels' => $amounts->keys()->map(fn ($k) => ucfirst($k))->values()->all(),
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

            $expense = (float) (PurchaseOrder::query()
                ->whereYear('tanggal_po', $month->year)
                ->whereMonth('tanggal_po', $month->month)
                ->sum('total') ?? 0);

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
