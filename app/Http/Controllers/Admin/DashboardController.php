<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\WebsiteVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke()
    {
        /** @var \App\Models\User|null $user */
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
        $expiringLimit = $today->copy()->addDays(30);

        $paidProductOrders = Pesanan::query()
            ->where('tipe', 'produk')
            ->whereNotIn('status', [Pesanan::STATUS_DITOLAK])
            ->where(function (Builder $query) {
                $query->whereNotNull('pembayaran_terkonfirmasi_at')
                    ->orWhereNotNull('bukti_pembayaran')
                    ->orWhere('metode_pembayaran', 'cash');
            });

        $serviceRevenueQuery = Service::query()
            ->where(function (Builder $query) {
                $query->whereNull('jenis_service')
                    ->orWhere(function (Builder $nested) {
                        $nested->where('jenis_service', '!=', 'Refill')
                            ->where('jenis_service', 'not like', '%Refill%');
                    });
            });

        $refillRevenueQuery = Refill::query();

        $productRevenueMonth = $this->sumPesananAmount(
            (clone $paidProductOrders)->whereBetween('tanggal', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ])
        );

        $serviceRevenueMonth = $this->sumAmount(
            (clone $serviceRevenueQuery)->whereBetween('tgl_service', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ]),
            'biaya'
        );

        $refillRevenueMonth = $this->sumAmount(
            (clone $refillRevenueQuery)->whereBetween('tgl_refill', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ]),
            'biaya'
        );

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

        $unitStatusChart = [
            'Aktif' => UnitApar::query()->whereDate('tgl_expired', '>', $expiringLimit)->count(),
            'Akan Expired' => UnitApar::query()->whereBetween('tgl_expired', [$today, $expiringLimit])->count(),
            'Expired' => UnitApar::query()->whereDate('tgl_expired', '<', $today)->count(),
        ];

        $months = collect(range(5, 0))->map(function (int $offset) use ($now, $paidProductOrders, $serviceRevenueQuery, $refillRevenueQuery) {
            $month = $now->copy()->subMonths($offset)->startOfMonth();
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();

            $product = $this->sumPesananAmount(
                (clone $paidProductOrders)->whereBetween('tanggal', [$start, $end])
            );

            $service = $this->sumAmount(
                (clone $serviceRevenueQuery)->whereBetween('tgl_service', [$start, $end]),
                'biaya'
            );

            $refill = $this->sumAmount(
                (clone $refillRevenueQuery)->whereBetween('tgl_refill', [$start, $end]),
                'biaya'
            );

            return [
                'label' => $month->translatedFormat('M Y'),
                'product' => $product,
                'service' => $service,
                'refill' => $refill,
                'total' => $product + $service + $refill,
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

        return view('dashboard', [
            'kpis' => [
                'totalProduk' => Produk::count(),
                'totalPelanggan' => Pelanggan::count(),
                'totalUnitApar' => UnitApar::count(),
                'pendapatanBulanIni' => $productRevenueMonth + $serviceRevenueMonth + $refillRevenueMonth,
                'unitAkanExpired' => $unitStatusChart['Akan Expired'],
                'unitExpired' => $unitStatusChart['Expired'],
            ],
            'charts' => [
                'revenueComposition' => [
                    'labels' => ['Penjualan Produk', 'Service APAR', 'Refill APAR'],
                    'series' => [$productRevenueMonth, $serviceRevenueMonth, $refillRevenueMonth],
                ],
                'transactionStatus' => [
                    'labels' => array_keys($transactionStatusChart),
                    'series' => array_values($transactionStatusChart),
                ],
                'unitStatus' => [
                    'labels' => array_keys($unitStatusChart),
                    'series' => array_values($unitStatusChart),
                ],
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
            'topViewedProducts' => WebsiteVisit::getMostViewedProducts(null, null, 5)
                ->map(function ($item) {
                    $product = \App\Models\Produk::with('jenisApar')->find($item->product_id);
                    return [
                        'product_name' => $product?->nama ?? 'Produk #' . $item->product_id,
                        'view_count' => $item->view_count,
                    ];
                }),
            'topSoldProducts' => $this->getTopSoldProducts(5),
        ]);
    }

    private function countBucket($counts, array $keys): int
    {
        return collect($keys)->sum(function (string $key) use ($counts) {
            return (int) ($counts[strtolower($key)] ?? 0);
        });
    }

    private function sumPesananAmount(Builder $query): float
    {
        return (float) ($query
            ->selectRaw('COALESCE(SUM(COALESCE(total_harga, total, 0)), 0) as total_amount')
            ->value('total_amount') ?? 0);
    }

    private function sumAmount(Builder $query, string $column): float
    {
        return (float) ($query
            ->selectRaw("COALESCE(SUM({$column}), 0) as total_amount")
            ->value('total_amount') ?? 0);
    }

    private function getTopSoldProducts(int $limit = 5): \Illuminate\Support\Collection
    {
        $completedOrderStatuses = ['selesai', 'dikonfirmasi admin', 'selesai final'];
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        return \App\Models\PesananDetail::query()
            ->selectRaw('produk_id, SUM(jumlah) as total_sold')
            ->whereHas('pesanan', function ($q) use ($completedOrderStatuses, $startOfMonth, $endOfMonth) {
                $q->where('tipe', 'produk')
                    ->whereIn('status', $completedOrderStatuses)
                    ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
            })
            ->groupBy('produk_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $product = \App\Models\Produk::find($item->produk_id);
                return [
                    'product_name' => $product?->nama ?? 'Produk #' . $item->produk_id,
                    'total_sold' => $item->total_sold,
                ];
            });
    }
}
