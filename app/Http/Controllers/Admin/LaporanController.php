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

        $pesanans = Pesanan::whereIn('status', ['selesai', 'selesai final'])
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

            $productIncome = Pesanan::whereIn('status', ['selesai', 'selesai final'])
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
            ->latest('tgl_service')
            ->get();

        return Pdf::loadView('admin.laporan.pdf.keuangan', [
            'services' => $services,
            'filters' => $filters,
            'totals' => [
                'total_pemasukan' => $services->sum('biaya'),
                'total_transaksi' => $services->count(),
                'rata_rata' => $services->count() > 0 ? $services->sum('biaya') / $services->count() : 0,
            ],
        ])->download('laporan-keuangan.pdf');
    }

    public function aparCsv(Request $request)
    {
        $filters = $this->filters($request);
        $units = UnitApar::with(['pelanggan', 'produk.jenisApar'])
            ->when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tgl_produksi', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tgl_produksi', '<=', $v))
            ->latest()->get();

        $headers = ['No', 'No Seri', 'Pelanggan', 'Produk', 'Kapasitas', 'Tgl Produksi', 'Tgl Beli', 'Tgl Expired', 'Status'];
        $rows = $units->map(fn ($u, $i) => [
            $i + 1, $u->no_seri, $u->pelanggan->nama ?? '-', $u->produk->nama ?? '-',
            $u->ukuran ?? '-', 
            optional($u->tgl_produksi)->format('d-m-Y') ?? '-',
            optional($u->tgl_beli)->format('d-m-Y') ?? '-',
            optional($u->tgl_expired)->format('d-m-Y') ?? '-',
            ($u->tgl_expired && $u->tgl_expired->isFuture()) ? 'Aktif' : 'Expired',
        ]);
        return $this->downloadCsv('laporan-apar.csv', $headers, $rows);
    }

    public function pesananCsv(Request $request)
    {
        $filters = $this->filters($request);
        $pesanans = Pesanan::with(['pelanggan', 'details.produk'])
            ->where('tipe', 'produk')
            ->when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->latest('tanggal')->get();

        $headers = ['No', 'Tanggal', 'Pelanggan', 'Detail Produk', 'Total', 'Status'];
        $rows = $pesanans->map(fn ($p, $i) => [
            $i + 1, $p->tanggal->format('d-m-Y'), $p->pelanggan->nama ?? '-',
            $p->details->map(fn ($d) => ($d->produk->nama ?? '?').' x'.$d->jumlah)->implode('; '),
            'Rp '.number_format($p->total, 0, ',', '.'), $p->status,
        ]);
        return $this->downloadCsv('laporan-pesanan.csv', $headers, $rows);
    }

    public function serviceCsv(Request $request)
    {
        $filters = $this->filters($request);
        $services = Service::with(['unitApar.pelanggan', 'unitApar.produk'])
            ->where('jenis_service', '!=', 'Refill')
            ->when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tgl_service', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tgl_service', '<=', $v))
            ->latest('tgl_service')->get();

        $headers = ['No', 'Tanggal', 'No Seri', 'Pelanggan', 'Jenis Service', 'Biaya'];
        $rows = $services->map(fn ($s, $i) => [
            $i + 1, \Carbon\Carbon::parse($s->tgl_service)->format('d-m-Y'),
            $s->unitApar->no_seri ?? '-', $s->unitApar->pelanggan->nama ?? '-',
            $s->jenis_service, 'Rp '.number_format($s->biaya, 0, ',', '.'),
        ]);
        return $this->downloadCsv('laporan-service.csv', $headers, $rows);
    }

    public function keuanganCsv(Request $request)
    {
        $filters = $this->filters($request);
        $pesanans = Pesanan::whereIn('status', ['selesai', 'selesai final'])
            ->when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->with('pelanggan')->get();
        $services = Service::where('jenis_service', '!=', 'Refill')
            ->when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tgl_service', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tgl_service', '<=', $v))
            ->with('unitApar.pelanggan')->get();
        $pengeluarans = \App\Models\Pengeluaran::when($filters['tanggal_dari'], fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filters['tanggal_sampai'], fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))->get();

        $headers = ['Jenis', 'Tanggal', 'Keterangan', 'Nominal'];
        $rows = collect();
        foreach ($pesanans as $p) {
            $rows->push(['Penjualan', $p->tanggal->format('d-m-Y'), 'Pesanan #'.$p->id.' - '.($p->pelanggan->nama ?? '-'), 'Rp '.number_format($p->total, 0, ',', '.')]);
        }
        foreach ($services as $s) {
            $rows->push(['Service', \Carbon\Carbon::parse($s->tgl_service)->format('d-m-Y'), $s->jenis_service.' - '.($s->unitApar->pelanggan->nama ?? '-'), 'Rp '.number_format($s->biaya, 0, ',', '.')]);
        }
        foreach ($pengeluarans as $e) {
            $rows->push(['Pengeluaran', $e->tanggal->format('d-m-Y'), $e->keterangan, '-Rp '.number_format($e->nominal, 0, ',', '.')]);
        }
        $rows->push(['', '', 'LABA BERSIH', 'Rp '.number_format(($pesanans->sum('total') + $services->sum('biaya')) - $pengeluarans->sum('nominal'), 0, ',', '.')]);
        return $this->downloadCsv('laporan-keuangan.csv', $headers, $rows);
    }

    private function downloadCsv(string $filename, array $headers, $rows)
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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
