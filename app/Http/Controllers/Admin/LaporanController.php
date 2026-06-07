<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\WebsiteVisit;
use App\Services\FinalRevenueService;
use App\Services\ProductAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request, ProductAnalyticsService $productAnalytics, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);
        $now = now();
        $visitorLimitOptions = [10, 25, 50, 100];
        $visitorLimit = $request->integer('visitor_limit', 10);

        if (! in_array($visitorLimit, $visitorLimitOptions, true)) {
            $visitorLimit = 10;
        }

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

        $revenueComposition = [
            'labels' => ['Penjualan Produk', 'Service APAR', 'Refill APAR'],
            'series' => [$totalNilaiPesanan, $totalBiayaService, $totalBiayaRefill],
        ];

        $pendingCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['pending', 'menunggu', 'menunggu persetujuan'])->count();
        $diprosesCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
        $selesaiCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])->count();
        $ditolakCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['ditolak', 'batal'])->count();
        $transactionStatus = [
            'labels' => ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
            'series' => [$pendingCount, $diprosesCount, $selesaiCount, $ditolakCount],
        ];

        $expiringLimit = $now->copy()->addDays(30);
        $unitAktif = UnitApar::whereDate('tgl_expired', '>', $expiringLimit)->count();
        $unitAkanExpired = UnitApar::whereBetween('tgl_expired', [$now, $expiringLimit])->count();
        $unitExpired = UnitApar::whereDate('tgl_expired', '<', $now)->count();
        $unitStatus = [
            'labels' => ['Aktif', 'Akan Expired', 'Expired'],
            'series' => [$unitAktif, $unitAkanExpired, $unitExpired],
        ];

        $combinedData = collect();

        (clone $pesananQuery)
            ->with('pelanggan')
            ->latest('tanggal')
            ->take(20)
            ->get()
            ->each(fn ($pesanan) => $combinedData->push([
                'tanggal' => $pesanan->tanggal,
                'jenis' => 'Pesanan',
                'pelanggan' => $pesanan->pelanggan?->nama ?? '-',
                'keterangan' => $pesanan->transactionDisplayName().' - '.$pesanan->displayTransactionDateTime(),
                'status' => $pesanan->status,
                'pemasukan' => (float) $pesanan->total,
                'pengeluaran' => 0,
            ]));

        (clone $serviceQuery)
            ->with(['unitApar.pelanggan', 'pesanan.pelanggan'])
            ->latest('tgl_service')
            ->take(20)
            ->get()
            ->each(fn ($service) => $combinedData->push([
                'tanggal' => $service->tgl_service,
                'jenis' => 'Service',
                'pelanggan' => $service->unitApar?->pelanggan?->nama ?? $service->display_customer_name ?? '-',
                'keterangan' => $service->transactionDisplayName().' - '.$service->displayTransactionDateTime(),
                'status' => $service->pesanan?->status ?? $service->status_konfirmasi ?? '-',
                'pemasukan' => (float) $service->biaya,
                'pengeluaran' => 0,
            ]));

        (clone $refillQuery)
            ->with(['unitApar.pelanggan', 'service.pesanan.pelanggan', 'jenisRefill'])
            ->latest('tgl_refill')
            ->take(20)
            ->get()
            ->each(fn ($refill) => $combinedData->push([
                'tanggal' => $refill->tgl_refill,
                'jenis' => 'Refill',
                'pelanggan' => $refill->unitApar?->pelanggan?->nama ?? $refill->service?->pesanan?->pelanggan?->nama ?? '-',
                'keterangan' => $refill->transactionDisplayName().' - '.$refill->displayTransactionDateTime(),
                'status' => $refill->service?->pesanan?->status ?? '-',
                'pemasukan' => (float) $refill->biaya,
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

        $visitorQuery = WebsiteVisit::query()
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('visited_at', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('visited_at', '<=', $tanggalSampai))
            ->orderByDesc('visited_at');

        $visitorRecords = $visitorQuery->take($visitorLimit)->get();

        return view('admin.laporan.index', compact(
            'filters',
            'summary',
            'revenueComposition',
            'transactionStatus',
            'unitStatus',
            'combinedData',
            'visitorStats',
            'visitorRecords',
            'visitorLimit',
            'visitorLimitOptions',
            'mostViewedProducts',
            'mostSoldProducts',
            'pengeluarans'
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

        $pelanggans = Pelanggan::orderBy('nama')->get();

        return view('admin.laporan.apar', compact('units', 'stats', 'filters', 'pelanggans'));
    }

    public function service(Request $request)
    {
        $filters = $this->filters($request);

        $services = Service::with(['unitApar.pelanggan', 'unitApar.produk'])
            ->where('jenis_service', '!=', 'Refill')
            ->when($filters['pelanggan_id'], function ($query, $pelangganId) {
                $query->whereHas('unitApar', fn ($unitQuery) => $unitQuery->where('pelanggan_id', $pelangganId));
            })
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_service', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_service', '<=', $tanggalSampai))
            ->latest('tgl_service')
            ->get();

        $stats = [
            'total_transaksi' => $services->count(),
            'total_biaya' => $services->sum('biaya'),
        ];

        $pelanggans = Pelanggan::orderBy('nama')->get();

        return view('admin.laporan.service', compact('services', 'stats', 'filters', 'pelanggans'));
    }

    public function pesanan(Request $request)
    {
        $filters = $this->filters($request);

        $pesanans = Pesanan::with(['pelanggan', 'details.produk.jenisApar'])
            ->where('tipe', 'produk')
            ->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->latest('tanggal')
            ->get();

        $stats = [
            'total_transaksi' => $pesanans->count(),
            'total_item' => $pesanans->sum(fn ($pesanan) => $pesanan->details->sum('jumlah')),
            'total_nilai' => $pesanans->sum('total'),
        ];

        $pelanggans = Pelanggan::orderBy('nama')->get();

        return view('admin.laporan.pesanan', compact('pesanans', 'stats', 'filters', 'pelanggans'));
    }

    public function keuangan(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);

        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['unitApar.pelanggan', 'unitApar.produk', 'pesanan.pelanggan'])
            ->get();

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with('pelanggan')
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['unitApar.pelanggan', 'service.pesanan.pelanggan', 'jenisRefill'])
            ->get();

        $pengeluarans = $this->pengeluaranQuery($filters)->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $pemasukanRefill = $refills->sum('biaya');
        $totalPengeluaran = $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk + $pemasukanRefill,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk + $pemasukanRefill) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count() + $refills->count(),
        ];

        $pelanggans = Pelanggan::orderBy('nama')->get();

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

        return view('admin.laporan.keuangan', compact(
            'services',
            'pesanans',
            'refills',
            'pengeluarans',
            'totals',
            'filters',
            'pelanggans',
            'trendData'
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

    public function servicePdf(Request $request)
    {
        $filters = $this->filters($request);
        $services = Service::with(['unitApar.pelanggan', 'unitApar.produk'])
            ->where('jenis_service', '!=', 'Refill')
            ->when($filters['pelanggan_id'], function ($query, $pelangganId) {
                $query->whereHas('unitApar', fn ($unitQuery) => $unitQuery->where('pelanggan_id', $pelangganId));
            })
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_service', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_service', '<=', $tanggalSampai))
            ->latest('tgl_service')
            ->get();

        return Pdf::loadView('admin.laporan.pdf.service', [
            'services' => $services,
            'filters' => $filters,
            'totalBiaya' => $services->sum('biaya'),
        ])->download('laporan-service.pdf');
    }

    public function pesananPdf(Request $request)
    {
        $filters = $this->filters($request);
        $pesanans = Pesanan::with(['pelanggan', 'details.produk.jenisApar'])
            ->where('tipe', 'produk')
            ->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->latest('tanggal')
            ->get();

        return Pdf::loadView('admin.laporan.pdf.pesanan', [
            'pesanans' => $pesanans,
            'filters' => $filters,
            'stats' => [
                'total_transaksi' => $pesanans->count(),
                'total_item' => $pesanans->sum(fn ($pesanan) => $pesanan->details->sum('jumlah')),
                'total_nilai' => $pesanans->sum('total'),
            ],
        ])->download('laporan-pesanan.pdf');
    }

    public function keuanganPdf(Request $request, FinalRevenueService $finalRevenue)
    {
        $filters = $this->filters($request);

        $services = $finalRevenue->serviceTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['unitApar.pelanggan', 'unitApar.produk', 'pesanan.pelanggan'])
            ->get();

        $pesanans = $finalRevenue->productOrdersQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with('pelanggan')
            ->get();

        $refills = $finalRevenue->refillTransactionsQuery(
            $filters['tanggal_dari'],
            $filters['tanggal_sampai'],
            $filters['pelanggan_id']
        )
            ->with(['unitApar.pelanggan', 'service.pesanan.pelanggan', 'jenisRefill'])
            ->get();

        $pengeluarans = $this->pengeluaranQuery($filters)->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $pemasukanRefill = $refills->sum('biaya');
        $totalPengeluaran = $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk + $pemasukanRefill,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk + $pemasukanRefill) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count() + $refills->count(),
        ];

        return Pdf::loadView('admin.laporan.pdf.keuangan', [
            'services' => $services,
            'pesanans' => $pesanans,
            'refills' => $refills,
            'pengeluarans' => $pengeluarans,
            'filters' => $filters,
            'totals' => $totals,
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

    public function indexPdf(Request $request, ProductAnalyticsService $productAnalytics, FinalRevenueService $finalRevenue)
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

        (clone $pesananQuery)
            ->with('pelanggan')
            ->latest('tanggal')
            ->take(50)
            ->get()
            ->each(fn ($pesanan) => $combinedData->push([
                'tanggal' => $pesanan->tanggal,
                'jenis' => 'Pesanan',
                'pelanggan' => $pesanan->pelanggan?->nama ?? '-',
                'keterangan' => $pesanan->transactionDisplayName().' - '.$pesanan->displayTransactionDateTime(),
                'status' => $pesanan->status,
                'pemasukan' => (float) $pesanan->total,
            ]));

        (clone $serviceQuery)
            ->with(['unitApar.pelanggan', 'pesanan.pelanggan'])
            ->latest('tgl_service')
            ->take(50)
            ->get()
            ->each(fn ($service) => $combinedData->push([
                'tanggal' => $service->tgl_service,
                'jenis' => 'Service',
                'pelanggan' => $service->unitApar?->pelanggan?->nama ?? $service->display_customer_name ?? '-',
                'keterangan' => $service->transactionDisplayName().' - '.$service->displayTransactionDateTime(),
                'status' => $service->pesanan?->status ?? $service->status_konfirmasi ?? '-',
                'pemasukan' => (float) $service->biaya,
            ]));

        (clone $refillQuery)
            ->with(['unitApar.pelanggan', 'service.pesanan.pelanggan', 'jenisRefill'])
            ->latest('tgl_refill')
            ->take(50)
            ->get()
            ->each(fn ($refill) => $combinedData->push([
                'tanggal' => $refill->tgl_refill,
                'jenis' => 'Refill',
                'pelanggan' => $refill->unitApar?->pelanggan?->nama ?? $refill->service?->pesanan?->pelanggan?->nama ?? '-',
                'keterangan' => $refill->transactionDisplayName().' - '.$refill->displayTransactionDateTime(),
                'status' => $refill->service?->pesanan?->status ?? '-',
                'pemasukan' => (float) $refill->biaya,
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

    private function pengeluaranQuery(array $filters): Builder
    {
        return Pengeluaran::query()
            ->when($filters['tanggal_dari'], fn (Builder $query, string $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn (Builder $query, string $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai));
    }

    private function sumPengeluaranAmount(Builder $query): float
    {
        return (float) ((clone $query)
            ->selectRaw('COALESCE(SUM('.Pengeluaran::effectiveAmountSql().'), 0) as total_pengeluaran')
            ->value('total_pengeluaran') ?? 0);
    }
}
