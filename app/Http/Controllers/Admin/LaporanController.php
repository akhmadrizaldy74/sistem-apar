<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UnitApar;
use App\Models\WebsiteVisit;
use App\Services\AdminAnalyticsService;
use App\Services\FinalRevenueService;
use App\Services\ProductAnalyticsService;
use App\Services\StockHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    public function index(
        Request $request,
        ProductAnalyticsService $productAnalytics,
        FinalRevenueService $finalRevenue,
        AdminAnalyticsService $analytics,
        StockHistoryService $stockHistoryService
    )
    {
        $filters = $this->filters($request);
        $now = now();
        $visitorLimitOptions = [10, 25, 50, 100];
        $visitorLimit = $request->integer('visitor_limit', 10);

        if (! in_array($visitorLimit, $visitorLimitOptions, true)) {
            $visitorLimit = 10;
        }

        $pelanggans = Pelanggan::query()->visibleInDirectory()->orderBy('nama')->get();

        $pesananQuery = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $serviceQuery = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $refillQuery = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $unitQuery = UnitApar::query();

        if ($filters['tanggal_dari']) {
            $unitQuery->whereDate('tgl_produksi', '>=', $filters['tanggal_dari']);
        }

        if ($filters['tanggal_sampai']) {
            $unitQuery->whereDate('tgl_produksi', '<=', $filters['tanggal_sampai']);
        }

        $revenueBreakdown = $finalRevenue->breakdown(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );

        $totalPesanan = (clone $pesananQuery)->count();
        $totalNilaiPesanan = $revenueBreakdown['product'];
        $totalService = (clone $serviceQuery)->count();
        $totalBiayaService = $revenueBreakdown['service'];
        $totalRefill = (clone $refillQuery)->count();
        $totalBiayaRefill = $revenueBreakdown['refill'];
        $totalUnit = $unitQuery->count();
        $totalPengeluaran = $this->sumPengeluaranAmount($this->pengeluaranQuery($filters));
        $totalPemasukan = $revenueBreakdown['total'];
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        $revenueComposition = $analytics->revenueComposition(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id'],
            'Semua transaksi dengan pembayaran valid pada periode laporan.'
        );

        $pendingCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['pending', 'menunggu', 'menunggu persetujuan'])->count();
        $diprosesCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
        $selesaiCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])->count();
        $ditolakCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['ditolak', 'batal'])->count();
        $transactionStatus = [
            'labels' => ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
            'series' => [$pendingCount, $diprosesCount, $selesaiCount, $ditolakCount],
        ];

        $unitStatus = $analytics->unitStatus(Carbon::today());
        $monthlyPurchases = $analytics->monthlyPurchases($now);

        $combinedData = collect();

        $this->applyRevenueSort(
            (clone $pesananQuery)->with(['pelanggan', 'details.produk'])
        )
            ->take(20)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Pesanan',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->productItemSummary($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
                'pengeluaran' => 0,
            ]));

        $this->applyRevenueSort(
            (clone $serviceQuery)->with(['pelanggan', 'teknisi', 'servicePaket', 'service', 'unitApars.produk'])
        )
            ->take(20)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Service',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->serviceLabel($pesanan).' - '.$this->serviceUnitSummary($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
                'pengeluaran' => 0,
            ]));

        $this->applyRevenueSort(
            (clone $refillQuery)->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
        )
            ->take(20)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Refill',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->refillLabel($pesanan).' - '.$this->refillQuantityLabel($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
                'pengeluaran' => 0,
            ]));

        $summary = compact(
            'totalPesanan',
            'totalNilaiPesanan',
            'totalService',
            'totalBiayaService',
            'totalRefill',
            'totalBiayaRefill',
            'totalUnit',
            'totalPengeluaran',
            'totalPemasukan',
            'labaBersih'
        );

        $visitorStats = [
            'totalUnik' => WebsiteVisit::getUniqueVisitors($filters['tanggal_dari'], $filters['tanggal_sampai']),
            'totalKunjungan' => WebsiteVisit::getTotalPageViews($filters['tanggal_dari'], $filters['tanggal_sampai']),
            'hariIni' => WebsiteVisit::getTodayVisitors(),
            'bulanIni' => WebsiteVisit::getThisMonthVisitors(),
        ];

        $mostViewedProducts = $productAnalytics->mostViewedProducts(
            from: $filters['tanggal_dari'],
            to: $filters['tanggal_sampai'],
            limit: 10,
        );

        $mostSoldProducts = $productAnalytics->mostSoldProducts(
            from: $filters['tanggal_dari'],
            to: $filters['tanggal_sampai'],
            pelangganId: $filters['pelanggan_id'],
            limit: 10,
        );

        $pengeluarans = $this->pengeluaranQuery($filters)
            ->orderByDesc('tanggal')
            ->limit(50)
            ->get();
        $stockHistories = $stockHistoryService->recent(
            limit: 15,
            tanggalDari: $filters['tanggal_dari'],
            tanggalSampai: $filters['tanggal_sampai'],
        );

        $visitorQuery = WebsiteVisit::query()
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('visited_at', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('visited_at', '<=', $tanggalSampai))
            ->orderByDesc('visited_at');

        $visitorRecords = $visitorQuery->take($visitorLimit)->get();

        $stockSummary = [
            'produk' => Produk::with('stokBatches')->get()->sum(fn (Produk $produk) => (float) $produk->stok_tersedia),
            'refill' => (float) \App\Models\JenisRefill::sum('stok'),
            'peralatan' => (float) Peralatan::sum('stok'),
        ];

        $charts = [
            'revenueComposition' => $revenueComposition,
            'unitStatus' => $unitStatus,
            'monthlyPurchases' => $monthlyPurchases,
        ];

        return view('admin.laporan.index', compact(
            'filters',
            'summary',
            'charts',
            'revenueComposition',
            'transactionStatus',
            'unitStatus',
            'monthlyPurchases',
            'combinedData',
            'visitorStats',
            'visitorRecords',
            'visitorLimit',
            'visitorLimitOptions',
            'mostViewedProducts',
            'mostSoldProducts',
            'pengeluarans',
            'stockHistories',
            'pelanggans',
            'stockSummary'
        ));
    }

    public function apar(Request $request)
    {
        $filters = $this->filters($request);

        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_produksi', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_produksi', '<=', $tanggalSampai))
            ->latest()
            ->get();

        $stats = [
            'total' => $units->count(),
            'aktif' => $units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->isFuture())->count(),
            'expired' => $units->filter(fn ($unit) => $unit->tgl_expired && $unit->tgl_expired->isPast())->count(),
        ];

        $pelanggans = Pelanggan::query()->visibleInDirectory()->orderBy('nama')->get();

        return view('admin.laporan.apar', compact('units', 'stats', 'filters', 'pelanggans'));
    }

    public function penjualan(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);
        $pelanggans = Pelanggan::query()->visibleInDirectory()->orderBy('nama')->get();

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'details.produk'])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $transactions = $this->buildPenjualanTransactions($pesanans, $refills);

        $stats = [
            'total_transaksi' => $transactions->count(),
            'produk_transaksi' => $transactions->where('jenis_transaksi', 'Penjualan Produk')->count(),
            'refill_transaksi' => $transactions->where('jenis_transaksi', 'Refill APAR')->count(),
            'total_nilai' => (float) $transactions->sum('total'),
        ];

        $periode = $this->buildPeriodeLabel($filters);

        return view('admin.laporan.penjualan', compact(
            'filters',
            'pelanggans',
            'transactions',
            'stats',
            'periode'
        ));
    }

    public function pesanan(Request $request, FinalRevenueService $finalRevenue)
    {
        return $this->penjualan($request, $finalRevenue);
    }

    public function service(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);
        $pelanggans = Pelanggan::query()->visibleInDirectory()->orderBy('nama')->get();

        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with([
                'pelanggan',
                'teknisi',
                'servicePaket',
                'service',
                'unitApars.produk',
            ])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $serviceRows = $this->buildServiceRows($services);

        $stats = [
            'total_transaksi' => $serviceRows->count(),
            'total_biaya' => (float) $serviceRows->sum('total'),
            'transaksi_pelanggan' => $serviceRows->where('source', 'Transaksi Pelanggan')->count(),
            'riwayat_lama' => $serviceRows->where('source', 'Riwayat Lama')->count(),
        ];

        return view('admin.laporan.service', compact('serviceRows', 'stats', 'filters', 'pelanggans'));
    }

    public function keuangan(
        Request $request,
        FinalRevenueService $finalRevenue,
        AdminAnalyticsService $analytics
    )
    {
        $filters = $this->filters($request);

        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'teknisi', 'servicePaket', 'service', 'unitApars.produk'])
            ->get();

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'details.produk'])
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
            ->get();

        $pengeluarans = $this->pengeluaranQuery($filters)->get();

        $pemasukanService = (float) $services->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $pemasukanProduk = (float) $pesanans->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $pemasukanRefill = (float) $refills->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $totalPengeluaran = (float) $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk + $pemasukanRefill,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk + $pemasukanRefill) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count() + $refills->count(),
        ];

        $incomeBreakdown = [
            'produk' => $pemasukanProduk,
            'refill' => $pemasukanRefill,
            'service' => $pemasukanService,
        ];

        $expenseBreakdown = $pengeluarans
            ->groupBy(fn (Pengeluaran $pengeluaran) => $this->expenseTypeLabel($pengeluaran))
            ->map(fn (Collection $items) => (float) $items->sum('effective_amount'))
            ->sortDesc()
            ->all();

        $charts = [
            'revenueComposition' => $analytics->revenueComposition(
                $filters['tanggal_dari'],
                $filters['tanggal_sampai'],
                $filters['pelanggan_id'],
                'Semua transaksi dengan pembayaran valid pada periode laporan keuangan.'
            ),
            'expenseBreakdown' => $analytics->expenseBreakdown($pengeluarans),
            'cashflowTrend' => $analytics->cashflowTrend($filters['pelanggan_id'], now()),
        ];

        $pelanggans = Pelanggan::query()->visibleInDirectory()->orderBy('nama')->get();

        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $monthBreakdown = $finalRevenue->breakdown(
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
                $filters['pelanggan_id']
            );

            $expense = $this->sumPengeluaranAmount(
                Pengeluaran::query()
                    ->whereMonth('tanggal', $month->month)
                    ->whereYear('tanggal', $month->year)
            );

            $trendData[] = [
                'label' => $month->format('M Y'),
                'bulan' => $month->month,
                'tahun' => $month->year,
                'pemasukan_service' => (int) $monthBreakdown['service'],
                'pemasukan_produk' => (int) $monthBreakdown['product'],
                'pemasukan_refill' => (int) $monthBreakdown['refill'],
                'total_pemasukan' => (int) $monthBreakdown['total'],
                'pengeluaran' => (int) $expense,
                'laba' => (int) ($monthBreakdown['total'] - $expense),
            ];
        }

        $records = $this->buildKeuanganRecords($pesanans, $services, $refills, $pengeluarans);

        return view('admin.laporan.keuangan', compact(
            'totals',
            'filters',
            'pelanggans',
            'charts',
            'trendData',
            'incomeBreakdown',
            'expenseBreakdown',
            'records'
        ));
    }

    public function aparPdf(Request $request)
    {
        $filters = $this->filters($request);
        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_produksi', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_produksi', '<=', $tanggalSampai))
            ->latest()
            ->get();

        return Pdf::loadView('admin.laporan.pdf.apar', [
            'units' => $units,
            'filters' => $filters,
        ])->download('laporan-apar.pdf');
    }

    public function penjualanPdf(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'details.produk'])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $transactions = $this->buildPenjualanTransactions($pesanans, $refills);
        $stats = [
            'total_transaksi' => $transactions->count(),
            'produk_transaksi' => $transactions->where('jenis_transaksi', 'Penjualan Produk')->count(),
            'refill_transaksi' => $transactions->where('jenis_transaksi', 'Refill APAR')->count(),
            'total_nilai' => (float) $transactions->sum('total'),
        ];

        return Pdf::loadView('admin.laporan.pdf.penjualan', [
            'filters' => $filters,
            'periode' => $this->buildPeriodeLabel($filters),
            'transactions' => $transactions,
            'stats' => $stats,
        ])->download('laporan-penjualan-refill.pdf');
    }

    public function pesananPdf(Request $request, FinalRevenueService $finalRevenue)
    {
        return $this->penjualanPdf($request, $finalRevenue);
    }

    public function servicePdf(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);
        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with([
                'pelanggan',
                'teknisi',
                'servicePaket',
                'service',
                'unitApars.produk',
            ])
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id')
            ->get();

        $serviceRows = $this->buildServiceRows($services);

        return Pdf::loadView('admin.laporan.pdf.service', [
            'filters' => $filters,
            'periode' => $this->buildPeriodeLabel($filters),
            'serviceRows' => $serviceRows,
            'totalBiaya' => (float) $serviceRows->sum('total'),
        ])->download('laporan-service.pdf');
    }

    public function keuanganPdf(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);

        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'teknisi', 'servicePaket', 'service', 'unitApars.produk'])
            ->get();

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'details.produk'])
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
            ->get();

        $pengeluarans = $this->pengeluaranQuery($filters)->get();

        $pemasukanService = (float) $services->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $pemasukanProduk = (float) $pesanans->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $pemasukanRefill = (float) $refills->sum(fn (Pesanan $pesanan) => $pesanan->payableTotal());
        $totalPengeluaran = (float) $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk + $pemasukanRefill,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk + $pemasukanRefill) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count() + $refills->count(),
        ];

        return Pdf::loadView('admin.laporan.pdf.keuangan', [
            'periode' => $this->buildPeriodeLabel($filters),
            'totals' => $totals,
            'incomeBreakdown' => [
                'produk' => $pemasukanProduk,
                'refill' => $pemasukanRefill,
                'service' => $pemasukanService,
            ],
            'expenseBreakdown' => $pengeluarans
                ->groupBy(fn (Pengeluaran $pengeluaran) => $this->expenseTypeLabel($pengeluaran))
                ->map(fn (Collection $items) => (float) $items->sum('effective_amount'))
                ->sortDesc()
                ->all(),
            'records' => $this->buildKeuanganRecords($pesanans, $services, $refills, $pengeluarans),
        ])->download('laporan-keuangan.pdf');
    }

    protected function filters(Request $request): array
    {
        return [
            'tanggal_dari' => $request->string('tanggal_dari')->toString() ?: null,
            'tanggal_sampai' => $request->string('tanggal_sampai')->toString() ?: null,
            'pelanggan_id' => $request->filled('pelanggan_id') ? (int) $request->pelanggan_id : null,
        ];
    }

    public function indexPdf(
        Request $request,
        ProductAnalyticsService $productAnalytics,
        FinalRevenueService $finalRevenue,
        StockHistoryService $stockHistoryService
    )
    {
        $filters = $this->filters($request);
        $now = now();

        $pesananQuery = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $serviceQuery = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $refillQuery = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );
        $unitQuery = UnitApar::query();

        if ($filters['tanggal_dari']) {
            $unitQuery->whereDate('tgl_produksi', '>=', $filters['tanggal_dari']);
        }

        if ($filters['tanggal_sampai']) {
            $unitQuery->whereDate('tgl_produksi', '<=', $filters['tanggal_sampai']);
        }

        $revenueBreakdown = $finalRevenue->breakdown(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        );

        $totalPesanan = (clone $pesananQuery)->count();
        $totalNilaiPesanan = $revenueBreakdown['product'];
        $totalService = (clone $serviceQuery)->count();
        $totalBiayaService = $revenueBreakdown['service'];
        $totalRefill = (clone $refillQuery)->count();
        $totalBiayaRefill = $revenueBreakdown['refill'];
        $totalUnit = $unitQuery->count();
        $totalPengeluaran = $this->sumPengeluaranAmount($this->pengeluaranQuery($filters));
        $totalPemasukan = $revenueBreakdown['total'];
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        $totalUnik = WebsiteVisit::getUniqueVisitors($filters['tanggal_dari'], $filters['tanggal_sampai']);
        $totalKunjungan = WebsiteVisit::getTotalPageViews($filters['tanggal_dari'], $filters['tanggal_sampai']);

        $pendingCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['pending', 'menunggu', 'menunggu persetujuan'])->count();
        $diprosesCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
        $selesaiCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])->count();
        $ditolakCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['ditolak', 'batal'])->count();

        $expiringLimit = $now->copy()->addDays(30);
        $unitAktif = UnitApar::whereDate('tgl_expired', '>', $expiringLimit)->count();
        $unitAkanExpired = UnitApar::whereBetween('tgl_expired', [$now, $expiringLimit])->count();
        $unitExpired = UnitApar::whereDate('tgl_expired', '<', $now)->count();

        $combinedData = collect();

        $this->applyRevenueSort(
            (clone $pesananQuery)->with(['pelanggan', 'details.produk'])
        )
            ->take(50)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Pesanan',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->productItemSummary($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
            ]));

        $this->applyRevenueSort(
            (clone $serviceQuery)->with(['pelanggan', 'teknisi', 'servicePaket', 'service', 'unitApars.produk'])
        )
            ->take(50)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Service',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->serviceLabel($pesanan).' - '.$this->serviceUnitSummary($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
            ]));

        $this->applyRevenueSort(
            (clone $refillQuery)->with(['pelanggan', 'serviceJenisRefill', 'service', 'unitApars.produk'])
        )
            ->take(50)
            ->get()
            ->each(fn (Pesanan $pesanan) => $combinedData->push([
                'tanggal' => $this->reportTransactionDateValue($pesanan),
                'jenis' => 'Refill',
                'pelanggan' => $this->customerName($pesanan),
                'keterangan' => $this->refillLabel($pesanan).' - '.$this->refillQuantityLabel($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'pemasukan' => (float) $pesanan->payableTotal(),
            ]));

        $pelangganNama = $filters['pelanggan_id'] ? (Pelanggan::find($filters['pelanggan_id'])?->nama ?? null) : null;
        $periode = $this->buildPeriodeLabel($filters);

        $visitorRecordsPdf = WebsiteVisit::query()
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('visited_at', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('visited_at', '<=', $tanggalSampai))
            ->orderByDesc('visited_at')
            ->take(50)
            ->get();

        $visitorStatsPdf = [
            'totalUnik' => $totalUnik,
            'totalKunjungan' => $totalKunjungan,
            'hariIni' => WebsiteVisit::getTodayVisitors(),
        ];

        $mostViewedProductsPdf = $productAnalytics->mostViewedProducts(
            from: $filters['tanggal_dari'],
            to: $filters['tanggal_sampai'],
            limit: 10,
        );

        $mostSoldProductsPdf = $productAnalytics->mostSoldProducts(
            from: $filters['tanggal_dari'],
            to: $filters['tanggal_sampai'],
            pelangganId: $filters['pelanggan_id'],
            limit: 10,
        );

        $pengeluaransPdf = $this->pengeluaranQuery($filters)
            ->orderByDesc('tanggal')
            ->limit(50)
            ->get();
        $stockHistoriesPdf = $stockHistoryService->recent(
            limit: 20,
            tanggalDari: $filters['tanggal_dari'],
            tanggalSampai: $filters['tanggal_sampai'],
        );

        return Pdf::loadView('admin.laporan.pdf.index', [
            'filters' => $filters,
            'periode' => $periode,
            'pelangganNama' => $pelangganNama,
            'summary' => compact(
                'totalPesanan',
                'totalNilaiPesanan',
                'totalService',
                'totalBiayaService',
                'totalRefill',
                'totalBiayaRefill',
                'totalUnit',
                'totalPengeluaran',
                'totalPemasukan',
                'labaBersih'
            ),
            'revenueComposition' => [$totalNilaiPesanan, $totalBiayaService, $totalBiayaRefill],
            'transactionStatus' => compact('pendingCount', 'diprosesCount', 'selesaiCount', 'ditolakCount'),
            'unitStatus' => compact('unitAktif', 'unitAkanExpired', 'unitExpired'),
            'visitorStats' => $visitorStatsPdf,
            'visitorRecords' => $visitorRecordsPdf,
            'mostViewedProducts' => $mostViewedProductsPdf,
            'mostSoldProducts' => $mostSoldProductsPdf,
            'pengeluarans' => $pengeluaransPdf,
            'stockHistories' => $stockHistoriesPdf,
            'combinedData' => $combinedData->sortByDesc('tanggal')->values(),
            'printedAt' => now(),
        ])->download('laporan-apar-'.now()->format('Y-m-d').'.pdf');
    }

    private function buildPeriodeLabel(array $filters): string
    {
        if ($filters['tanggal_dari'] && $filters['tanggal_sampai']) {
            return Carbon::parse($filters['tanggal_dari'])->translatedFormat('d M Y').' - '.Carbon::parse($filters['tanggal_sampai'])->translatedFormat('d M Y');
        }

        if ($filters['tanggal_dari']) {
            return 'Dari '.Carbon::parse($filters['tanggal_dari'])->translatedFormat('d M Y');
        }

        if ($filters['tanggal_sampai']) {
            return 'Sampai '.Carbon::parse($filters['tanggal_sampai'])->translatedFormat('d M Y');
        }

        return 'Semua Waktu';
    }

    private function applyRevenueSort(Builder $query): Builder
    {
        return $query
            ->orderByRaw(Pesanan::revenueRecognitionDateExpression().' DESC')
            ->orderByDesc('id');
    }

    private function pengeluaranQuery(array $filters): Builder
    {
        return Pengeluaran::query()
            ->with(['produk', 'jenisRefill', 'peralatan'])
            ->when($filters['tanggal_dari'], fn (Builder $query, string $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn (Builder $query, string $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai));
    }

    private function sumPengeluaranAmount(Builder $query): float
    {
        return (float) ((clone $query)
            ->selectRaw('COALESCE(SUM('.Pengeluaran::effectiveAmountSql().'), 0) as total_pengeluaran')
            ->value('total_pengeluaran') ?? 0);
    }

    private function buildPenjualanTransactions(Collection $pesanans, Collection $refills): Collection
    {
        $productRows = $pesanans->map(function (Pesanan $pesanan): array {
            $qty = (int) $pesanan->details->sum('jumlah');

            return [
                'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                'pelanggan' => $this->customerName($pesanan),
                'jenis_transaksi' => 'Penjualan Produk',
                'item' => $this->productItemSummary($pesanan),
                'jumlah' => $qty > 0 ? $qty.' unit' : '-',
                'total' => (float) $pesanan->payableTotal(),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'detail_url' => route('admin.pesanan.show', $pesanan),
            ];
        });

        $refillRows = $refills->map(function (Pesanan $pesanan): array {
            return [
                'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                'pelanggan' => $this->customerName($pesanan),
                'jenis_transaksi' => 'Refill APAR',
                'item' => $this->refillLabel($pesanan).' - '.$this->serviceUnitSummary($pesanan),
                'jumlah' => $this->refillQuantityLabel($pesanan),
                'total' => (float) $pesanan->payableTotal(),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'detail_url' => route('admin.pesanan.show', $pesanan),
            ];
        });

        return $productRows
            ->concat($refillRows)
            ->sortByDesc('sort_at')
            ->values();
    }

    private function buildServiceRows(Collection $services): Collection
    {
        return $services
            ->map(function (Pesanan $pesanan): array {
                $peralatanItems = $pesanan->servicePeralatanItems();

                return [
                    'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                    'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                    'pelanggan' => $this->customerName($pesanan),
                    'jenis_service' => $this->serviceLabel($pesanan),
                    'unit' => $this->serviceUnitSummary($pesanan),
                    'jumlah_unit' => max(1, (int) ($pesanan->service_jumlah_unit ?? $pesanan->adminOrderUnitCount() ?? 1)),
                    'peralatan' => $this->peralatanSummary($peralatanItems),
                    'total' => (float) $pesanan->payableTotal(),
                    'status' => $pesanan->publicStatusLabel(),
                    'teknisi' => $pesanan->teknisi?->name ?? '-',
                    'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                    'detail_url' => route('admin.pesanan.show', $pesanan),
                ];
            })
            ->sortByDesc('sort_at')
            ->values();
    }

    private function buildKeuanganRecords(Collection $pesanans, Collection $services, Collection $refills, Collection $pengeluarans): Collection
    {
        $records = collect();

        foreach ($pesanans as $pesanan) {
            $records->push([
                'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                'jenis' => 'Penjualan Produk',
                'keterangan' => $this->productItemSummary($pesanan),
                'pelanggan' => $this->customerName($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'nominal' => (float) $pesanan->payableTotal(),
                'direction' => 'in',
            ]);
        }

        foreach ($services as $pesanan) {
            $records->push([
                'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                'jenis' => 'Service APAR',
                'keterangan' => $this->serviceLabel($pesanan).' - '.$this->serviceUnitSummary($pesanan),
                'pelanggan' => $this->customerName($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'nominal' => (float) $pesanan->payableTotal(),
                'direction' => 'in',
            ]);
        }

        foreach ($refills as $pesanan) {
            $records->push([
                'sort_at' => $this->reportTransactionAt($pesanan)?->timestamp ?? now()->timestamp,
                'tanggal_label' => $this->reportTransactionDateTime($pesanan),
                'jenis' => 'Refill APAR',
                'keterangan' => $this->refillLabel($pesanan).' - '.$this->refillQuantityLabel($pesanan),
                'pelanggan' => $this->customerName($pesanan),
                'status' => $pesanan->publicStatusLabel(),
                'source' => $this->sourceLabel($pesanan->sumber_pesanan),
                'nominal' => (float) $pesanan->payableTotal(),
                'direction' => 'in',
            ]);
        }

        foreach ($pengeluarans as $pengeluaran) {
            $records->push([
                'sort_at' => $pengeluaran->tanggal?->timestamp ?? now()->timestamp,
                'tanggal_label' => $pengeluaran->tanggal?->format('d M Y') ?? '-',
                'jenis' => $this->expenseTypeLabel($pengeluaran),
                'keterangan' => $this->expenseDescription($pengeluaran),
                'pelanggan' => '-',
                'status' => 'Tercatat',
                'source' => $this->expenseSourceLabel($pengeluaran),
                'nominal' => (float) $pengeluaran->effective_amount,
                'direction' => 'out',
            ]);
        }

        return $records->sortByDesc('sort_at')->values();
    }

    private function productItemSummary(Pesanan $pesanan): string
    {
        $names = $pesanan->details->pluck('produk.nama')->filter()->values();

        if ($names->isEmpty()) {
            return $pesanan->transactionDisplayName();
        }

        if ($names->count() <= 2) {
            return $names->implode(', ');
        }

        return $names->take(2)->implode(', ').' +'.($names->count() - 2).' item';
    }

    private function reportTransactionAt(Pesanan $pesanan): ?Carbon
    {
        return $pesanan->recognizedRevenueAt()
            ?? $pesanan->displayTransactionAt()
            ?? optional($pesanan->tanggal)?->copy()->timezone(config('app.timezone'))->startOfDay();
    }

    private function reportTransactionDateValue(Pesanan $pesanan): string
    {
        return $this->reportTransactionAt($pesanan)?->toDateTimeString()
            ?? (optional($pesanan->tanggal)?->toDateTimeString() ?: now()->toDateTimeString());
    }

    private function reportTransactionDateTime(Pesanan $pesanan, string $format = 'd M Y, H:i'): string
    {
        return $this->reportTransactionAt($pesanan)?->format($format) ?? '-';
    }

    private function customerName(Pesanan $pesanan): string
    {
        return (string) ($pesanan->pelanggan?->nama ?? '-');
    }

    private function serviceLabel(Pesanan $pesanan): string
    {
        return $pesanan->servicePaket?->nama
            ?: $pesanan->adminOrderDetailTitle();
    }

    private function refillLabel(Pesanan $pesanan): string
    {
        $breakdownLabels = collect($pesanan->servicePricingBreakdown())
            ->map(fn (array $item) => trim((string) ($item['display_label'] ?? $item['label'] ?? '')))
            ->filter()
            ->unique()
            ->values();

        return (string) ($pesanan->serviceJenisRefill?->nama_label
            ?: ($breakdownLabels->isNotEmpty() ? $breakdownLabels->implode(', ') : $pesanan->adminOrderDetailTitle()));
    }

    private function serviceUnitSummary(Pesanan $pesanan): string
    {
        $display = $pesanan->serviceUnitDisplay();

        return (string) ($display['summary']
            ?? $display['detail_label']
            ?? '-');
    }

    private function refillQuantityLabel(Pesanan $pesanan): string
    {
        $totalKg = (float) ($pesanan->service_total_kg ?? 0);

        if ($totalKg > 0) {
            return $this->formatQty($totalKg).' Kg';
        }

        $unitCount = (int) ($pesanan->service_jumlah_unit ?? $pesanan->adminOrderUnitCount() ?? 0);

        return $unitCount > 0 ? $unitCount.' unit' : '-';
    }

    private function peralatanSummary(array $items): string
    {
        $summary = collect($items)
            ->filter(fn ($item) => filled($item['nama'] ?? null))
            ->map(fn ($item) => trim((string) $item['nama']).' x'.max(1, (int) ($item['jumlah'] ?? 1)))
            ->values();

        return $summary->isNotEmpty() ? $summary->implode(', ') : 'Tidak ada peralatan';
    }

    private function expenseTypeLabel(Pengeluaran $pengeluaran): string
    {
        return match ($pengeluaran->jenis_pengeluaran) {
            Pengeluaran::JENIS_PEMBELIAN_APAR => 'Pembelian Stok APAR',
            Pengeluaran::JENIS_PEMBELIAN_REFILL => 'Pembelian Stok Refill',
            Pengeluaran::JENIS_PEMBELIAN_PERALATAN => 'Pembelian Peralatan Service',
            default => $pengeluaran->jenis_pengeluaran_label,
        };
    }

    private function expenseDescription(Pengeluaran $pengeluaran): string
    {
        $itemLabel = $this->expenseItemLabel($pengeluaran);
        $qtyLabel = $this->expenseQuantityLabel($pengeluaran);

        return match ($pengeluaran->jenis_pengeluaran) {
            Pengeluaran::JENIS_PEMBELIAN_APAR => $this->appendExpenseNote(
                'Pembelian stok APAR untuk '.$itemLabel.($qtyLabel !== '' ? ' sebanyak '.$qtyLabel : '').'.',
                $pengeluaran,
                $itemLabel,
            ),
            Pengeluaran::JENIS_PEMBELIAN_REFILL => $this->appendExpenseNote(
                'Pembelian stok refill untuk '.$itemLabel.($qtyLabel !== '' ? ' sebanyak '.$qtyLabel : '').'.',
                $pengeluaran,
                $itemLabel,
            ),
            Pengeluaran::JENIS_PEMBELIAN_PERALATAN => $this->appendExpenseNote(
                'Pembelian peralatan service untuk '.$itemLabel.($qtyLabel !== '' ? ' sebanyak '.$qtyLabel : '').'.',
                $pengeluaran,
                $itemLabel,
            ),
            default => $this->appendExpenseNote(
                $itemLabel !== '-' ? $itemLabel . '.' : 'Pengeluaran operasional lainnya.',
                $pengeluaran,
                $itemLabel,
            ),
        };
    }

    private function expenseSourceLabel(Pengeluaran $pengeluaran): string
    {
        return $pengeluaran->isStockAffecting()
            ? 'Menu Pengeluaran Stok'
            : 'Operasional';
    }

    private function expenseItemLabel(Pengeluaran $pengeluaran): string
    {
        return (string) ($pengeluaran->display_item_name ?: $pengeluaran->nama_item ?: '-');
    }

    private function expenseQuantityLabel(Pengeluaran $pengeluaran): string
    {
        $qty = (float) ($pengeluaran->qty ?? 0);
        if ($qty <= 0) {
            return '';
        }

        return $this->formatQty($qty).' '.trim((string) ($pengeluaran->satuan ?: 'unit'));
    }

    private function appendExpenseNote(string $baseText, Pengeluaran $pengeluaran, ?string $ignoreIfSame = null): string
    {
        $baseText = trim($baseText);
        if ($baseText !== '' && !str_ends_with($baseText, '.')) {
            $baseText .= '.';
        }

        $note = trim((string) ($pengeluaran->keterangan ?? ''));
        $ignoreIfSame = trim((string) $ignoreIfSame);

        if ($note === '' || strcasecmp($note, $ignoreIfSame) === 0) {
            return $baseText;
        }

        return trim($baseText . ' Catatan: ' . $note);
    }

    private function sourceLabel(?string $source): string
    {
        if (! filled($source)) {
            return '-';
        }

        return Pesanan::isLegacySourceValue($source)
            ? 'Riwayat Lama'
            : 'Transaksi Pelanggan';
    }

    private function formatQty(float $value): string
    {
        if ((float) (int) $value === $value) {
            return number_format($value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
    }
}
