<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\User;
use App\Models\WebsiteVisit;
use App\Services\AdminAnalyticsService;
use App\Services\FinalRevenueService;
use App\Services\ProductExpiryAlertService;
use App\Services\ProductAnalyticsService;
use App\Services\StockAlertService;
use App\Support\RegisteredRefillUnitSupport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(
        ProductAnalyticsService $productAnalytics,
        FinalRevenueService $finalRevenue,
        AdminAnalyticsService $analytics,
        StockAlertService $stockAlerts,
        ProductExpiryAlertService $productExpiryAlerts
    )
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isTeknisi()) {
            return redirect()->route('teknisi.dashboard');
        }

        if (! $user->isAdmin()) {
            return redirect()->route('home');
        }

        $today = Carbon::today();
        $now = now();
        $expiringLimit = $today->copy()->addDays(RegisteredRefillUnitSupport::REFILL_WARNING_DAYS);

        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $monthlyRevenue = $finalRevenue->breakdown($monthStart, $monthEnd);
        $revenueComposition = $analytics->revenueComposition();

        $paidProductOrders = Pesanan::query()
            ->where('tipe', 'produk')
            ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
            ->where(function (Builder $query) {
                $query->whereNotNull('pembayaran_terkonfirmasi_at')
                    ->orWhereNotNull('bukti_pembayaran')
                    ->orWhere('metode_pembayaran', 'cash');
            });

        $waitingStatuses = [
            Pesanan::STATUS_PERMINTAAN_MASUK,
            'menunggu persetujuan',
            'menunggu diproses admin',
            Pesanan::STATUS_MENUNGGU_PENJADWALAN,
            Pesanan::STATUS_MENUNGGU_PERSETUJUAN_BIAYA,
            Pesanan::STATUS_DIREVIEW_ADMIN,
            Pesanan::STATUS_PENDING,
            Pesanan::STATUS_DISETUJUI,
            'menunggu',
        ];

        $inProgressStatuses = [
            Pesanan::STATUS_DIPROSES,
            Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT,
            Pesanan::STATUS_MENUNGGU_PENGAMBILAN,
        ];

        $completedStatuses = [Pesanan::STATUS_SELESAI, Pesanan::STATUS_SELESAI_FINAL];
        $rejectedStatuses = [Pesanan::STATUS_DITOLAK, 'batal'];

        $statusCounts = Pesanan::query()
            ->selectRaw('LOWER(COALESCE(status, "")) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $transactionStatusChart = [
            'Menunggu' => $this->countBucket($statusCounts, $waitingStatuses),
            'Diproses' => $this->countBucket($statusCounts, $inProgressStatuses),
            'Selesai' => $this->countBucket($statusCounts, $completedStatuses),
            'Ditolak / Batal' => $this->countBucket($statusCounts, $rejectedStatuses),
        ];

        $unitStatusChart = $analytics->unitStatus($today);

        $months = collect(range(5, 0))->map(function (int $offset) use ($now, $finalRevenue) {
            $month = $now->copy()->subMonths($offset)->startOfMonth();
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();
            $breakdown = $finalRevenue->breakdown($start, $end);

            return [
                'label' => $month->translatedFormat('M Y'),
                'product' => $breakdown['product'],
                'service' => $breakdown['service'],
                'refill' => $breakdown['refill'],
                'total' => $breakdown['total'],
            ];
        });

        $transferProofs = Pesanan::query()
            ->with('pelanggan')
            ->where('tipe', 'produk')
            ->whereNotNull('bukti_pembayaran')
            ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $urgentOrders = Pesanan::query()
            ->with('pelanggan')
            ->whereIn('status', $waitingStatuses)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $expiringUnits = UnitApar::query()
            ->with(['pelanggan', 'produk'])
            ->whereBetween('tgl_expired', [$today, $expiringLimit])
            ->orderBy('tgl_expired')
            ->take(5)
            ->get();

        $expiredUnits = UnitApar::query()
            ->with(['pelanggan', 'produk'])
            ->whereDate('tgl_expired', '<', $today)
            ->orderBy('tgl_expired')
            ->take(6)
            ->get();

        $monthlyPurchases = $analytics->monthlyPurchases($now);

        return view('dashboard', [
            'kpis' => [
                'totalProduk' => Produk::count(),
                'totalPelanggan' => Pelanggan::count(),
                'pendapatanKeseluruhan' => array_sum(array_map('floatval', $revenueComposition['series'] ?? [])),
                'totalPesanan' => Pesanan::count(),
                'totalKomplain' => Complain::count(),
                'totalUnitApar' => UnitApar::count(),
                'pendapatanBulanIni' => $monthlyRevenue['total'],
                'unitAkanExpired' => $unitStatusChart['series'][1] ?? 0,
                'unitExpired' => $unitStatusChart['series'][2] ?? 0,
            ],
            'charts' => [
                'revenueComposition' => $revenueComposition,
                'transactionStatus' => [
                    'labels' => array_keys($transactionStatusChart),
                    'series' => array_values($transactionStatusChart),
                ],
                'unitStatus' => $unitStatusChart,
                'revenueTrend' => [
                    'labels' => $months->pluck('label')->all(),
                    'series' => [
                        [
                            'name' => 'Total Pendapatan',
                            'data' => $months->pluck('total')->all(),
                        ],
                        [
                            'name' => 'Penjualan Produk',
                            'data' => $months->pluck('product')->all(),
                        ],
                        [
                            'name' => 'Service APAR',
                            'data' => $months->pluck('service')->all(),
                        ],
                        [
                            'name' => 'Refill APAR',
                            'data' => $months->pluck('refill')->all(),
                        ],
                    ],
                ],
                'monthlyPurchases' => $monthlyPurchases,
            ],
            'latest' => [
                'orders' => Pesanan::query()
                    ->with('pelanggan')
                    ->latest('created_at')
                    ->take(5)
                    ->get(),
                'services' => Service::query()
                    ->with(['unitApar.pelanggan', 'pesanan.pelanggan'])
                    ->latest('tgl_service')
                    ->take(5)
                    ->get(),
                'refills' => Refill::query()
                    ->with(['unitApar.pelanggan', 'jenisRefill', 'service.pesanan.pelanggan'])
                    ->latest('tgl_refill')
                    ->take(5)
                    ->get(),
                'payments' => (clone $paidProductOrders)
                    ->with('pelanggan')
                    ->orderByDesc('pembayaran_terkonfirmasi_at')
                    ->orderByDesc('updated_at')
                    ->take(5)
                    ->get(),
            ],
            'notifications' => [
                'transferProofs' => $transferProofs,
                'transferProofCount' => Pesanan::query()
                    ->where('tipe', 'produk')
                    ->whereNotNull('bukti_pembayaran')
                    ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
                    ->count(),
                'urgentOrders' => $urgentOrders,
                'urgentOrdersCount' => Pesanan::query()->whereIn('status', $waitingStatuses)->count(),
                'expiringUnits' => $expiringUnits,
                'expiredUnits' => $expiredUnits,
            ],
            'visitorStats' => [
                'hariIni' => WebsiteVisit::getTodayVisitors(),
            ],
            'topViewedProducts' => $productAnalytics->mostViewedProducts(limit: 5),
            'topSoldProducts' => $productAnalytics->mostSoldProducts(limit: 5),
            'stockAlerts' => $stockAlerts->adminDashboard(),
            'productExpiryAlerts' => $productExpiryAlerts->adminDashboard(),
        ]);
    }

    private function countBucket($counts, array $keys): int
    {
        return collect($keys)->sum(function (string $key) use ($counts) {
            return (int) ($counts[strtolower($key)] ?? 0);
        });
    }
}
