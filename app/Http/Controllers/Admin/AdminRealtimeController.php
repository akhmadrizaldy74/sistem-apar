<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Refill;
use App\Models\Service;
use App\Models\Testimoni;
use App\Models\UnitApar;
use App\Models\WebsiteVisit;
use App\Services\AdminAnalyticsService;
use App\Services\FinalRevenueService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRealtimeController extends Controller
{
    public function dashboard(FinalRevenueService $finalRevenue, AdminAnalyticsService $analytics): JsonResponse
    {
        $today = Carbon::today();
        $now = now();
        $expiringLimit = $today->copy()->addDays(30);
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $monthlyRevenue = $finalRevenue->breakdown($monthStart, $monthEnd);
        $overallRevenue = $analytics->revenueComposition();

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

        $unitStatusChart = [
            'Akan Expired' => UnitApar::query()->whereBetween('tgl_expired', [$today, $expiringLimit])->count(),
            'Expired' => UnitApar::query()->whereDate('tgl_expired', '<', $today)->count(),
        ];

        $kpis = [
            'totalProduk' => Produk::count(),
            'totalPelanggan' => Pelanggan::count(),
            'pendapatanKeseluruhan' => array_sum(array_map('floatval', $overallRevenue['series'] ?? [])),
            'totalPesanan' => Pesanan::count(),
            'totalKomplain' => Complain::count(),
            'totalUnitApar' => UnitApar::count(),
            'pendapatanBulanIni' => $monthlyRevenue['total'],
            'unitAkanExpired' => $unitStatusChart['Akan Expired'],
            'unitExpired' => $unitStatusChart['Expired'],
        ];

        $notifications = [
            'urgentOrdersCount' => Pesanan::query()->whereIn('status', $waitingStatuses)->count(),
        ];

        $visitorStats = [
            'hariIni' => WebsiteVisit::getTodayVisitors(),
        ];

        $monitoringPrioritas = $kpis['unitAkanExpired'] + $kpis['unitExpired'] + $notifications['urgentOrdersCount'];

        return response()->json([
            'success' => true,
            'kpi_html' => view('dashboard.partials.kpi-cards', compact('kpis', 'notifications', 'visitorStats', 'monitoringPrioritas'))->render(),
            'priority_html' => view('dashboard.partials.priority-panel', compact('kpis', 'notifications', 'monitoringPrioritas'))->render(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function pesanan(Request $request): JsonResponse
    {
        $pesanans = Pesanan::with(['pelanggan', 'details.produk.jenisApar', 'unitApars.produk'])
            ->where('tipe', 'produk')
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $nilaiPesanan = $pesanans->sum('total');
        $totalItem = $pesanans->sum(fn (Pesanan $pesanan) => $pesanan->details->sum('jumlah'));
        $pesananOnline = $pesanans->filter(fn (Pesanan $pesanan) => $pesanan->sumber_pesanan === 'website')->count();
        $pesananOffline = $pesanans->filter(fn (Pesanan $pesanan) => in_array((string) $pesanan->sumber_pesanan, ['datang_langsung', 'offline', 'input_admin'], true))->count();
        $finishedStatuses = ['selesai', 'selesai final', 'ditolak'];
        $pesananRiwayat = $pesanans->getCollection()
            ->filter(fn (Pesanan $pesanan) => in_array((string) $pesanan->status, $finishedStatuses, true))
            ->values();
        $pesananAktif = $pesanans->getCollection()
            ->reject(fn (Pesanan $pesanan) => in_array((string) $pesanan->status, $finishedStatuses, true))
            ->values();

        $summary = [
            'totalPesanan' => $pesanans->count(),
            'totalItem' => $totalItem,
            'nilaiPesanan' => $nilaiPesanan,
            'pesananOnline' => $pesananOnline,
            'pesananOffline' => $pesananOffline,
        ];

        return response()->json([
            'success' => true,
            'summary_html' => view('admin.pesanan.partials.summary-cards', compact('summary'))->render(),
            'active_rows_html' => view('admin.pesanan.partials.active-rows', ['pesananAktif' => $pesananAktif])->render(),
            'history_rows_html' => view('admin.pesanan.partials.history-rows', ['pesananRiwayat' => $pesananRiwayat])->render(),
            'detail_data' => $this->pesananDetailData($pesanans->getCollection()),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function pelanggan(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search'));
        $summaryQuery = Pelanggan::query()->visibleInDirectory();
        $query = Pelanggan::query()
            ->visibleInDirectory()
            ->with('user')
            ->withCount('productOrders');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('no_wa', 'like', '%'.$search.'%')
                    ->orWhere('alamat', 'like', '%'.$search.'%')
                    ->orWhere('alamat_maps', 'like', '%'.$search.'%')
                    ->orWhere('alamat_detail', 'like', '%'.$search.'%')
                    ->orWhere('alamat_provinsi', 'like', '%'.$search.'%')
                    ->orWhere('alamat_kota', 'like', '%'.$search.'%')
                    ->orWhere('alamat_kecamatan', 'like', '%'.$search.'%')
                    ->orWhere('alamat_kode_pos', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'totalPelanggan' => (clone $summaryQuery)->count(),
            'pelangganAktif' => (clone $summaryQuery)->whereHas('productOrders')->count(),
            'totalTransaksiPelanggan' => Pesanan::query()
                ->where('tipe', 'produk')
                ->whereNotIn('status', Pelanggan::excludedPurchaseStatuses())
                ->whereIn('pelanggan_id', (clone $summaryQuery)->select('pelanggans.id'))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'summary_html' => view('admin.pelanggan.partials.summary-cards', compact('summary'))->render(),
            'desktop_rows_html' => view('admin.pelanggan.partials.desktop-rows', ['pelanggans' => $pelanggans])->render(),
            'mobile_rows_html' => view('admin.pelanggan.partials.mobile-rows', ['pelanggans' => $pelanggans])->render(),
            'pagination_html' => $pelanggans->hasPages() ? $pelanggans->links()->render() : '',
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function complain(Request $request): JsonResponse
    {
        $query = Complain::with([
            'pelanggan',
            'pesanan.service.refill',
            'service.pesanan',
            'service.refill',
        ]);

        if ($request->filled('status')) {
            $query->where('status_penyelesaian', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function (Builder $builder) use ($request) {
                $builder->where('isi_complain', 'like', '%'.$request->search.'%')
                    ->orWhereHas('pelanggan', fn (Builder $pelangganQuery) => $pelangganQuery->where('nama', 'like', '%'.$request->search.'%'));
            });
        }

        $complains = $query->latest()->paginate(15)->withQueryString();
        $counts = [
            'total' => Complain::count(),
            'menunggu' => Complain::where('status_penyelesaian', 'menunggu')->count(),
            'diproses' => Complain::where('status_penyelesaian', 'diproses')->count(),
            'selesai' => Complain::where('status_penyelesaian', 'selesai')->count(),
        ];

        return response()->json([
            'success' => true,
            'counts_html' => view('admin.complain.partials.counts', compact('counts'))->render(),
            'rows_html' => view('admin.complain.partials.rows', ['complains' => $complains])->render(),
            'pagination_html' => $complains->hasPages() ? $complains->links()->render() : '',
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function testimoni(Request $request): JsonResponse
    {
        $query = Testimoni::with('pelanggan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $testimonis = $query->latest('tanggal')->paginate(15)->withQueryString();
        $counts = [
            'total' => Testimoni::count(),
            'pending' => Testimoni::where('status', 'pending')->count(),
            'approved' => Testimoni::where('status', 'approved')->count(),
            'rejected' => Testimoni::where('status', 'rejected')->count(),
        ];
        $currentStatus = trim((string) $request->input('status'));

        return response()->json([
            'success' => true,
            'counts_html' => view('admin.testimoni.partials.counts', compact('counts', 'currentStatus'))->render(),
            'rows_html' => view('admin.testimoni.partials.rows', ['testimonis' => $testimonis])->render(),
            'pagination_html' => $testimonis->hasPages() ? $testimonis->links()->render() : '',
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @param \Illuminate\Support\Collection<int, Pesanan> $pesanans
     * @return array<int, array<string, mixed>>
     */
    private function pesananDetailData($pesanans): array
    {
        return $pesanans->map(function (Pesanan $pesanan) {
            $pricingSummary = $pesanan->pricingSummary();
            $purchasePriceLabel = $pesanan->purchasePriceStatusLabel();
            $requestedPurchasePrice = $pesanan->requestedPurchasePrice();
            $approvedPurchasePrice = $pesanan->approvedPurchaseFinalPrice();
            $purchasePriceAdminNote = trim((string) ($pesanan->catatan_admin_harga ?? $pesanan->catatan_admin ?? ''));
            $sumber = match ((string) $pesanan->sumber_pesanan) {
                'website' => 'Online',
                'whatsapp' => 'WhatsApp',
                'telepon' => 'Telepon',
                'datang_langsung', 'offline' => 'Offline',
                'data_lama' => 'Data lama',
                default => 'Input',
            };

            return [
                'id' => $pesanan->id,
                'label' => $pesanan->transactionDisplayName(),
                'pelanggan' => $pesanan->pelanggan?->nama ?? '-',
                'no_wa' => $pesanan->pelanggan?->no_wa ?? '-',
                'alamat' => $pesanan->pelanggan?->alamat ?? '-',
                'tanggal' => $pesanan->displayTransactionDateTime(),
                'sumber' => $sumber,
                'status' => $pesanan->status,
                'status_label' => $pesanan->publicStatusLabel(),
                'hide_payment_badge' => $pesanan->shouldHidePaymentStatusBadge(),
                'payment_status_label' => $pesanan->isPaymentConfirmed() ? 'Lunas' : 'Belum Bayar',
                'metode' => $pesanan->metode_pengiriman === 'diantar_internal' ? 'Diantar' : 'Ambil Sendiri',
                'bank' => strtoupper($pesanan->bank ?? '-'),
                'total_unit' => $pesanan->details->sum('jumlah'),
                'subtotal' => number_format((float) $pricingSummary['subtotalProduk'], 0, ',', '.'),
                'diskon' => (float) $pricingSummary['nominalDiskon'] > 0 ? number_format((float) $pricingSummary['nominalDiskon'], 0, ',', '.') : null,
                'diskon_persen' => (int) $pricingSummary['diskonPersen'],
                'ongkir' => number_format((float) $pricingSummary['ongkir'], 0, ',', '.'),
                'total' => number_format((float) $pricingSummary['totalPembayaran'], 0, ',', '.'),
                'bukti_pembayaran' => $pesanan->bukti_pembayaran,
                'teknisi' => $pesanan->teknisi?->name,
                'is_paid' => $pesanan->isPaymentConfirmed(),
                'purchase_price' => [
                    'has_request' => $pesanan->hasPurchasePriceRequest(),
                    'is_pending' => $pesanan->hasPendingPurchasePriceRequest(),
                    'is_approved' => $pesanan->hasApprovedPurchasePriceRequest(),
                    'is_rejected' => $pesanan->hasRejectedPurchasePriceRequest(),
                    'label' => $purchasePriceLabel,
                    'badge_classes' => $pesanan->purchasePriceStatusClasses(),
                    'requested_price' => ! is_null($requestedPurchasePrice) ? number_format((float) $requestedPurchasePrice, 0, ',', '.') : null,
                    'final_price' => ! is_null($approvedPurchasePrice) ? number_format((float) $approvedPurchasePrice, 0, ',', '.') : null,
                    'normal_total' => number_format((float) ($pricingSummary['normalTotalPembayaran'] ?? $pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
                    'current_total' => number_format((float) ($pricingSummary['totalPembayaran'] ?? 0), 0, ',', '.'),
                    'customer_note' => $pesanan->purchasePriceCustomerNote(),
                    'admin_note' => $purchasePriceAdminNote !== '' ? $purchasePriceAdminNote : null,
                    'acc_url' => route('admin.pesanan.pengajuan-harga.acc', $pesanan),
                    'reject_url' => route('admin.pesanan.pengajuan-harga.tolak', $pesanan),
                ],
                'items' => $pesanan->details->map(function ($detail) {
                    return [
                        'nama' => $detail->produk?->nama ?? 'Produk Terhapus',
                        'jenis' => $detail->produk?->jenisApar?->nama ?? '-',
                        'kapasitas' => $detail->kapasitas ?? '-',
                        'merek' => $detail->merek ?? '-',
                        'jumlah' => (int) $detail->jumlah,
                        'harga' => number_format((float) $detail->harga, 0, ',', '.'),
                        'subtotal' => number_format((float) $detail->subtotal, 0, ',', '.'),
                    ];
                })->all(),
            ];
        })->values()->all();
    }
}
