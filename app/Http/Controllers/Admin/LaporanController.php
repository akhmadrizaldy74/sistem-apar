<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Service;
use App\Models\UnitApar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        $serviceQuery = Service::query()->where('jenis_service', '!=', 'Refill');
        $pesananQuery = Pesanan::query()->where('tipe', 'produk');

        $summary = [
            'totalApar' => UnitApar::count(),
            'totalExpired' => UnitApar::whereDate('tgl_expired', '<=', now())->count(),
            'totalPesanan' => $pesananQuery->count(),
            'totalService' => $serviceQuery->count(),
            'totalNilaiPesanan' => $pesananQuery->sum('total'),
            'totalPemasukanService' => $serviceQuery->sum('biaya'),
        ];

        return view('admin.laporan.index', compact('summary'));
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

        $pengeluarans = \App\Models\Pengeluaran::when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $totalPengeluaran = $pengeluarans->sum('nominal');

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

            $expense = \App\Models\Pengeluaran::whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->sum('nominal');

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

        $pengeluarans = \App\Models\Pengeluaran::when($filters['tanggal_dari'], fn ($query, $tanggalDari) => $query->whereDate('tanggal', '>=', $tanggalDari))
            ->when($filters['tanggal_sampai'], fn ($query, $tanggalSampai) => $query->whereDate('tanggal', '<=', $tanggalSampai))
            ->get();

        $pemasukanService = $services->sum('biaya');
        $pemasukanProduk = $pesanans->sum('total');
        $totalPengeluaran = $pengeluarans->sum('nominal');

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
}
