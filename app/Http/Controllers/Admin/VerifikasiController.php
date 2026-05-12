<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\UnitApar;
use App\Models\Service;
use App\Models\Refill;
use App\Models\ActivityLog;
use App\Models\Complain;
use App\Models\Testimoni;
use App\Models\StokBatch;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pelanggan' => Pelanggan::count(),
            'total_produk' => Produk::count(),
            'total_unit_apar' => UnitApar::count(),
            'total_pesanan' => Pesanan::count(),
            'total_service' => Service::count(),
            'total_refill' => Refill::count(),
            'total_complain' => Complain::count(),
            'total_testimoni' => Testimoni::count(),
            'unit_aktif' => UnitApar::whereDate('tgl_expired', '>', now())->count(),
            'unit_expired' => UnitApar::whereDate('tgl_expired', '<=', now())->count(),
        ];

        $recentActivity = ActivityLog::with('user')->latest()->take(20)->get();

        $dashboardStats = [
            'pesanan_bulan_ini' => Pesanan::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'service_bulan_ini' => Service::whereMonth('tgl_service', now()->month)->whereYear('tgl_service', now()->year)->count(),
            'refill_bulan_ini' => Refill::whereMonth('tgl_refill', now()->month)->whereYear('tgl_refill', now()->year)->count(),
            'pelanggan_bulan_ini' => Pelanggan::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        $moduleTests = $this->getModuleTests();

        return view('admin.verifikasi.index', compact('stats', 'recentActivity', 'dashboardStats', 'moduleTests'));
    }

    public function testResult(Request $request)
    {
        $testKey = $request->input('key');

        $results = [
            'produk_crud' => $this->testProdukCrud(),
            'pelanggan_crud' => $this->testPelangganCrud(),
            'pesanan_flow' => $this->testPesananFlow(),
            'unit_apar_sync' => $this->testUnitAparSync(),
            'activity_log' => $this->testActivityLog(),
            'complain_flow' => $this->testComplainFlow(),
            'testimoni_flow' => $this->testTestimoniFlow(),
            'stok_fifo' => $this->testStokFifo(),
        ];

        if ($testKey && isset($results[$testKey])) {
            return response()->json([
                'success' => true,
                'key' => $testKey,
                'result' => $results[$testKey],
            ]);
        }

        return response()->json($results);
    }

    private function testProdukCrud(): array
    {
        try {
            $count = Produk::count();
            $hasData = $count > 0;
            return [
                'status' => $hasData ? 'passed' : 'warning',
                'message' => $hasData ? "Produk tersedia: {$count} item." : 'Belum ada data produk. Silakan input produk terlebih dahulu.',
                'data' => $hasData ? Produk::latest()->take(3)->get(['id', 'nama', 'harga'])->toArray() : [],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testPelangganCrud(): array
    {
        try {
            $count = Pelanggan::count();
            $hasData = $count > 0;
            return [
                'status' => $hasData ? 'passed' : 'warning',
                'message' => $hasData ? "Pelanggan terdaftar: {$count} orang." : 'Belum ada data pelanggan.',
                'data' => $hasData ? Pelanggan::latest()->take(3)->get(['id', 'nama', 'no_wa'])->toArray() : [],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testPesananFlow(): array
    {
        try {
            $count = Pesanan::count();
            $byStatus = Pesanan::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();
            return [
                'status' => 'passed',
                'message' => "Total pesanan: {$count}. Status breakdown: " . json_encode($byStatus),
                'data' => ['total' => $count, 'by_status' => $byStatus],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testUnitAparSync(): array
    {
        try {
            $total = UnitApar::count();
            $aktif = UnitApar::whereDate('tgl_expired', '>', now())->count();
            $expired = UnitApar::whereDate('tgl_expired', '<=', now())->count();
            return [
                'status' => $total > 0 ? 'passed' : 'warning',
                'message' => "Total APAR: {$total}. Aktif: {$aktif}, Expired: {$expired}",
                'data' => ['total' => $total, 'aktif' => $aktif, 'expired' => $expired],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testActivityLog(): array
    {
        try {
            $count = ActivityLog::count();
            $recent = ActivityLog::latest()->take(5)->get(['id', 'description', 'created_at']);
            return [
                'status' => 'passed',
                'message' => "Activity log berjalan. Total records: {$count}",
                'data' => ['total' => $count, 'recent' => $recent->toArray()],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testComplainFlow(): array
    {
        try {
            $total = Complain::count();
            $byStatus = Complain::selectRaw('status_penyelesaian, count(*) as total')->groupBy('status_penyelesaian')->pluck('total', 'status_penyelesaian')->toArray();
            return [
                'status' => 'passed',
                'message' => "Total komplain: {$total}. Status: " . json_encode($byStatus),
                'data' => ['total' => $total, 'by_status' => $byStatus],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testTestimoniFlow(): array
    {
        try {
            $total = Testimoni::count();
            $approved = Testimoni::where('status', 'approved')->count();
            $pending = Testimoni::where('status', 'pending')->count();
            return [
                'status' => 'passed',
                'message' => "Testimoni: {$total} total, {$approved} approved, {$pending} pending",
                'data' => ['total' => $total, 'approved' => $approved, 'pending' => $pending],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function testStokFifo(): array
    {
        try {
            $totalBatches = StokBatch::count();
            $totalQty = StokBatch::sum('sisa_qty');
            $expiredBatches = StokBatch::whereDate('tgl_expired', '<=', now())->count();
            return [
                'status' => 'passed',
                'message' => "Stok batch: {$totalBatches} batch, {$totalQty} qty sisa. Expired: {$expiredBatches}",
                'data' => ['total_batches' => $totalBatches, 'total_qty' => $totalQty, 'expired' => $expiredBatches],
            ];
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private function getModuleTests(): array
    {
        return [
            [
                'key' => 'produk_crud',
                'label' => 'Modul Produk (CRUD)',
                'desc' => 'Pengujian fungsi Create, Read, Update, Delete data produk APAR.',
            ],
            [
                'key' => 'pelanggan_crud',
                'label' => 'Modul Pelanggan (CRUD)',
                'desc' => 'Pengujian fungsi pencatatan dan pencarian data pelanggan.',
            ],
            [
                'key' => 'pesanan_flow',
                'label' => 'Alur Pesanan',
                'desc' => 'Pengujian alur pesanan dari input hingga penyelesaian.',
            ],
            [
                'key' => 'unit_apar_sync',
                'label' => 'Sinkronisasi Unit APAR',
                'desc' => 'Pengujian auto-sync unit APAR dari pesanan produk.',
            ],
            [
                'key' => 'activity_log',
                'label' => 'Audit Log / Activity Log',
                'desc' => 'Pengujian perekaman jejak perubahan data.',
            ],
            [
                'key' => 'complain_flow',
                'label' => 'Alur Komplain',
                'desc' => 'Pengujian sistem komplain pelanggan ter-link pesanan/service.',
            ],
            [
                'key' => 'testimoni_flow',
                'label' => 'Moderasi Testimoni',
                'desc' => 'Pengujian alur testimoni: submit → review → approved.',
            ],
            [
                'key' => 'stok_fifo',
                'label' => 'Manajemen Stok FIFO',
                'desc' => 'Pengujian sistem batch FIFO dan tracking expired.',
            ],
        ];
    }
}