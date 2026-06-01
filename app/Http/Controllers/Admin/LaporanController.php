<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\Refill;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\WebsiteVisit;
use App\Services\ProductAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request, ProductAnalyticsService $productAnalytics)
    {
        $filters = $this->filters($request);
        $now = now();
        $visitorLimitOptions = [10, 25, 50, 100];
        $visitorLimit = $request->integer('visitor_limit', 10);
        if (!in_array($visitorLimit, $visitorLimitOptions, true)) {
            $visitorLimit = 10;
        }

        // Base queries
        $pesananQuery = Pesanan::query()->where('tipe', 'produk');
        $serviceQuery = Service::query()->where('jenis_service', '!=', 'Refill');
        $unitQuery = UnitApar::query();

        // Apply date filters
        if ($filters['tanggal_dari']) {
            $pesananQuery->whereDate('tanggal', '>=', $filters['tanggal_dari']);
            $serviceQuery->whereDate('tgl_service', '>=', $filters['tanggal_dari']);
            $unitQuery->whereDate('tgl_produksi', '>=', $filters['tanggal_dari']);
        }
        if ($filters['tanggal_sampai']) {
            $pesananQuery->whereDate('tanggal', '<=', $filters['tanggal_sampai']);
            $serviceQuery->whereDate('tgl_service', '<=', $filters['tanggal_sampai']);
            $unitQuery->whereDate('tgl_produksi', '<=', $filters['tanggal_sampai']);
        }

        // Summary stats
        $totalPesanan = $pesananQuery->count();
        $totalNilaiPesanan = (float) $pesananQuery->sum('total');
        $totalService = $serviceQuery->count();
        $totalBiayaService = (float) $serviceQuery->sum('biaya');
        $totalRefill = Refill::when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_refill', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_refill', '<=', $s))
            ->count();
        $totalBiayaRefill = (float) Refill::when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_refill', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_refill', '<=', $s))
            ->sum('biaya');
        $totalUnit = $unitQuery->count();
        $totalPengeluaran = $this->sumPengeluaranAmount($this->pengeluaranQuery($filters));
        $totalPemasukan = $totalNilaiPesanan + $totalBiayaService + $totalBiayaRefill;
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        // Charts data
        $revenueComposition = [
            'labels' => ['Penjualan Produk', 'Service APAR', 'Refill APAR'],
            'series' => [$totalNilaiPesanan, $totalBiayaService, $totalBiayaRefill],
        ];

        // Transaction status
        $pendingCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['pending', 'menunggu', 'menunggu persetujuan'])->count();
        $diprosesCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
        $selesaiCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])->count();
        $ditolakCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['ditolak', 'batal'])->count();
        $transactionStatus = [
            'labels' => ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
            'series' => [$pendingCount, $diprosesCount, $selesaiCount, $ditolakCount],
        ];

        // Unit status
        $expiringLimit = $now->copy()->addDays(30);
        $unitAktif = UnitApar::whereDate('tgl_expired', '>', $expiringLimit)->count();
        $unitAkanExpired = UnitApar::whereBetween('tgl_expired', [$now, $expiringLimit])->count();
        $unitExpired = UnitApar::whereDate('tgl_expired', '<', $now)->count();
        $unitStatus = [
            'labels' => ['Aktif', 'Akan Expired', 'Expired'],
            'series' => [$unitAktif, $unitAkanExpired, $unitExpired],
        ];

        // Combined table data
        $combinedData = collect();
        Pesanan::with('pelanggan')->where('tipe', 'produk')
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tanggal', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tanggal', '<=', $s))
            ->latest('tanggal')->take(20)->each(fn($p) => $combinedData->push([
                'tanggal' => $p->tanggal,
                'jenis' => 'Pesanan',
                'pelanggan' => $p->pelanggan?->nama ?? '-',
                'keterangan' => $p->transactionDisplayName() . ' • ' . $p->displayTransactionDateTime(),
                'status' => $p->status,
                'pemasukan' => (float) $p->total,
                'pengeluaran' => 0,
            ]));
        Service::with('unitApar.pelanggan')
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_service', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_service', '<=', $s))
            ->where('jenis_service', '!=', 'Refill')
            ->latest('tgl_service')->take(20)->each(fn($s) => $combinedData->push([
                'tanggal' => $s->tgl_service,
                'jenis' => 'Service',
                'pelanggan' => $s->unitApar?->pelanggan?->nama ?? $s->display_customer_name ?? '-',
                'keterangan' => $s->transactionDisplayName() . ' • ' . $s->displayTransactionDateTime(),
                'status' => $s->status_konfirmasi ?? '-',
                'pemasukan' => (float) $s->biaya,
                'pengeluaran' => 0,
            ]));

        $summary = compact('totalPesanan', 'totalNilaiPesanan', 'totalService', 'totalBiayaService',
            'totalRefill', 'totalBiayaRefill', 'totalUnit', 'totalPengeluaran', 'totalPemasukan', 'labaBersih');

        // Visitor stats
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

        // Detailed expenditures
        $pengeluarans = $this->pengeluaranQuery($filters)
            ->orderByDesc('tanggal')
            ->limit(50)
            ->get();

        // Detailed visitor records
        $visitorQuery = WebsiteVisit::query()
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('visited_at', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('visited_at', '<=', $s))
            ->orderByDesc('visited_at');

        $visitorRecords = $visitorQuery->take($visitorLimit)->get();

        return view('admin.laporan.index', compact(
            'filters', 'summary', 'revenueComposition', 'transactionStatus', 'unitStatus', 'combinedData', 'visitorStats', 'visitorRecords', 'visitorLimit', 'visitorLimitOptions', 'mostViewedProducts', 'mostSoldProducts', 'pengeluarans'
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

    public function keuangan(Request $request)
    {
        $filters = $this->filters($request);

        $services = Service::with(['unitApar.pelanggan', 'unitApar.produk'])
            ->where('jenis_service', '!=', 'Refill')
            ->when($filters['pelanggan_id'], function ($query, $pelangganId) {
                $query->whereHas('unitApar', fn ($unitQuery) => $unitQuery->where('pelanggan_id', $pelangganId));
            })
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_service', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_service', '<=', $tanggalSampai))
            ->get();

        $pesanans = Pesanan::whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->get();

        $pengeluarans = $this->pengeluaranQuery($filters)
            ->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $totalPengeluaran = $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count(),
        ];

        $pelanggans = Pelanggan::orderBy('nama')->get();

        // Monthly trend (last 6 months)
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $label = $month->format('M Y');

            $serviceIncome = Service::where('jenis_service', '!=', 'Refill')
                ->whereMonth('tgl_service', $month->month)
                ->whereYear('tgl_service', $month->year)
                ->sum('biaya');

            $productIncome = Pesanan::whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])
                ->whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->sum('total');

            $expense = $this->sumPengeluaranAmount(
                Pengeluaran::query()
                    ->whereMonth('tanggal', $month->month)
                    ->whereYear('tanggal', $month->year)
            );

            $trendData[] = [
                'label' => $label,
                'bulan' => $month->month,
                'tahun' => $month->year,
                'pemasukan_service' => (int) $serviceIncome,
                'pemasukan_produk' => (int) $productIncome,
                'total_pemasukan' => (int) ($serviceIncome + $productIncome),
                'pengeluaran' => (int) $expense,
                'laba' => (int) (($serviceIncome + $productIncome) - $expense),
            ];
        }

        return view('admin.laporan.keuangan', compact('services', 'pesanans', 'pengeluarans', 'totals', 'filters', 'pelanggans', 'trendData'));
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

    public function keuanganPdf(Request $request)
    {
        $filters = $this->filters($request);
        
        $services = Service::with(['unitApar.pelanggan', 'unitApar.produk'])
            ->where('jenis_service', '!=', 'Refill')
            ->when($filters['pelanggan_id'], function ($query, $pelangganId) {
                $query->whereHas('unitApar', fn ($unitQuery) => $unitQuery->where('pelanggan_id', $pelangganId));
            })
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tgl_service', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tgl_service', '<=', $tanggalSampai))
            ->get();

        $pesanans = Pesanan::whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])
            ->when($filters['pelanggan_id'], fn ($query, $pelangganId) => $query->where('pelanggan_id', $pelangganId))
            ->when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->with('pelanggan')->get();

        $pengeluarans = $this->pengeluaranQuery($filters)
            ->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $totalPengeluaran = $pengeluarans->sum('effective_amount');

        $totals = [
            'total_pemasukan' => $pemasukanService + $pemasukanProduk,
            'total_pengeluaran' => $totalPengeluaran,
            'laba_bersih' => ($pemasukanService + $pemasukanProduk) - $totalPengeluaran,
            'total_transaksi' => $services->count() + $pesanans->count(),
        ];

        return Pdf::loadView('admin.laporan.pdf.keuangan', [
            'services' => $services,
            'pesanans' => $pesanans,
            'pengeluarans' => $pengeluarans,
            'filters' => $filters,
            'totals' => $totals,
        ])->download('laporan-keuangan.pdf');
    }



    protected function filters(Request $request): array
    {
        return [
            'tanggal_dari'   => $request->string('tanggal_dari')->toString() ?: null,
            'tanggal_sampai' => $request->string('tanggal_sampai')->toString() ?: null,
            'pelanggan_id'   => $request->filled('pelanggan_id') ? (int) $request->pelanggan_id : null,
        ];
    }

    public function indexPdf(Request $request, ProductAnalyticsService $productAnalytics)
    {
        $filters = $this->filters($request);
        $now = now();

        // Base data with same logic as index
        $pesananQuery = Pesanan::query()->where('tipe', 'produk');
        $serviceQuery = Service::query()->where('jenis_service', '!=', 'Refill');
        $unitQuery = UnitApar::query();

        if ($filters['tanggal_dari']) {
            $pesananQuery->whereDate('tanggal', '>=', $filters['tanggal_dari']);
            $serviceQuery->whereDate('tgl_service', '>=', $filters['tanggal_dari']);
            $unitQuery->whereDate('tgl_produksi', '>=', $filters['tanggal_dari']);
        }
        if ($filters['tanggal_sampai']) {
            $pesananQuery->whereDate('tanggal', '<=', $filters['tanggal_sampai']);
            $serviceQuery->whereDate('tgl_service', '<=', $filters['tanggal_sampai']);
            $unitQuery->whereDate('tgl_produksi', '<=', $filters['tanggal_sampai']);
        }

        $totalPesanan = $pesananQuery->count();
        $totalNilaiPesanan = (float) $pesananQuery->sum('total');
        $totalService = $serviceQuery->count();
        $totalBiayaService = (float) $serviceQuery->sum('biaya');
        $totalRefill = Refill::when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_refill', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_refill', '<=', $s))->count();
        $totalBiayaRefill = (float) Refill::when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_refill', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_refill', '<=', $s))->sum('biaya');
        $totalUnit = $unitQuery->count();
        $totalPengeluaran = $this->sumPengeluaranAmount($this->pengeluaranQuery($filters));
        $totalPemasukan = $totalNilaiPesanan + $totalBiayaService + $totalBiayaRefill;
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        // Visitor stats
        $totalUnik = WebsiteVisit::getUniqueVisitors($filters['tanggal_dari'], $filters['tanggal_sampai']);
        $totalKunjungan = WebsiteVisit::getTotalPageViews($filters['tanggal_dari'], $filters['tanggal_sampai']);

        // Transaction status
        $pendingCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['pending', 'menunggu', 'menunggu persetujuan'])->count();
        $diprosesCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['diproses', 'ditugaskan ke teknisi', 'dikerjakan teknisi'])->count();
        $selesaiCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['selesai', 'dikonfirmasi admin', 'selesai final'])->count();
        $ditolakCount = Pesanan::where('tipe', 'produk')->whereIn('status', ['ditolak', 'batal'])->count();

        // Unit status
        $expiringLimit = $now->copy()->addDays(30);
        $unitAktif = UnitApar::whereDate('tgl_expired', '>', $expiringLimit)->count();
        $unitAkanExpired = UnitApar::whereBetween('tgl_expired', [$now, $expiringLimit])->count();
        $unitExpired = UnitApar::whereDate('tgl_expired', '<', $now)->count();

        // Combined table data
        $combinedData = collect();
        Pesanan::with('pelanggan')->where('tipe', 'produk')
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tanggal', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tanggal', '<=', $s))
            ->latest('tanggal')->take(50)->each(fn($p) => $combinedData->push([
                'tanggal' => $p->tanggal,
                'jenis' => 'Pesanan',
                'pelanggan' => $p->pelanggan?->nama ?? '-',
                'keterangan' => $p->transactionDisplayName() . ' • ' . $p->displayTransactionDateTime(),
                'status' => $p->status,
                'pemasukan' => (float) $p->total,
            ]));
        Service::with('unitApar.pelanggan')
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('tgl_service', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('tgl_service', '<=', $s))
            ->where('jenis_service', '!=', 'Refill')
            ->latest('tgl_service')->take(50)->each(fn($s) => $combinedData->push([
                'tanggal' => $s->tgl_service,
                'jenis' => 'Service',
                'pelanggan' => $s->unitApar?->pelanggan?->nama ?? $s->display_customer_name ?? '-',
                'keterangan' => $s->transactionDisplayName() . ' • ' . $s->displayTransactionDateTime(),
                'status' => $s->status_konfirmasi ?? '-',
                'pemasukan' => (float) $s->biaya,
            ]));

        $pelangganNama = $filters['pelanggan_id'] ? (Pelanggan::find($filters['pelanggan_id'])?->nama ?? null) : null;
        $periode = $this->buildPeriodeLabel($filters);

        // Visitor records for PDF
        $visitorRecordsPdf = WebsiteVisit::query()
            ->when($filters['tanggal_dari'], fn($q, $d) => $q->whereDate('visited_at', '>=', $d))
            ->when($filters['tanggal_sampai'], fn($q, $s) => $q->whereDate('visited_at', '<=', $s))
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

        // Detailed expenditures for PDF
        $pengeluaransPdf = $this->pengeluaranQuery($filters)
            ->orderByDesc('tanggal')
            ->limit(50)
            ->get();

        return Pdf::loadView('admin.laporan.pdf.index', [
            'filters' => $filters,
            'periode' => $periode,
            'pelangganNama' => $pelangganNama,
            'summary' => compact('totalPesanan', 'totalNilaiPesanan', 'totalService', 'totalBiayaService',
                'totalRefill', 'totalBiayaRefill', 'totalUnit', 'totalPengeluaran', 'totalPemasukan', 'labaBersih'),
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
        ])->download('laporan-apar-' . now()->format('Y-m-d') . '.pdf');
    }

    private function buildPeriodeLabel(array $filters): string
    {
        if ($filters['tanggal_dari'] && $filters['tanggal_sampai']) {
            return \Carbon\Carbon::parse($filters['tanggal_dari'])->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($filters['tanggal_sampai'])->translatedFormat('d M Y');
        }
        if ($filters['tanggal_dari']) {
            return 'Dari ' . \Carbon\Carbon::parse($filters['tanggal_dari'])->translatedFormat('d M Y');
        }
        if ($filters['tanggal_sampai']) {
            return 'Sampai ' . \Carbon\Carbon::parse($filters['tanggal_sampai'])->translatedFormat('d M Y');
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
            ->selectRaw('COALESCE(SUM(' . Pengeluaran::effectiveAmountSql() . '), 0) as total_pengeluaran')
            ->value('total_pengeluaran') ?? 0);
    }
}
