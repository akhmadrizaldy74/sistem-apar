<?php

namespace App\Http\Controllers;

use App\Events\TugasTeknisiDiperbarui;
use App\Models\Pesanan;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Services\InventoryService;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeknisiController extends Controller
{
    private function activeTaskStatuses(): array
    {
        return [
            Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            Pesanan::STATUS_DIKERJAKAN_TEKNISI,
        ];
    }

    private function historyTaskStatuses(): array
    {
        return [
            Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            Pesanan::STATUS_SELESAI_FINAL,
            Pesanan::STATUS_SELESAI,
        ];
    }

    private function taskBaseQuery(int $teknisiId)
    {
        return Pesanan::query()
            ->where('teknisi_id', $teknisiId)
            ->with(['pelanggan', 'details.produk', 'servicePaket', 'serviceJenisRefill', 'service.unitApar.produk.jenisApar']);
    }

    private function activeTasks(int $teknisiId)
    {
        return $this->taskBaseQuery($teknisiId)
            ->whereIn('status', $this->activeTaskStatuses())
            ->whereNull('teknisi_selesai_at')
            ->orderByDesc('created_at')
            ->get();
    }

    private function filterActiveTasks($tasks, string $filter)
    {
        return match ($filter) {
            'produk' => $tasks->filter(fn (Pesanan $task) => $task->isProductOrder())->values(),
            'service-refill' => $tasks->filter(fn (Pesanan $task) => !$task->isProductOrder())->values(),
            default => $tasks,
        };
    }

    private function historyTasks(int $teknisiId)
    {
        return $this->taskBaseQuery($teknisiId)
            ->where(function ($query) {
                $query->whereIn('status', $this->historyTaskStatuses())
                    ->orWhereNotNull('teknisi_selesai_at');
            })
            ->orderByDesc('created_at')
            ->get();
    }

    private function broadcastTaskUpdate(Pesanan $pesanan): void
    {
        try {
            $broadcast = broadcast(new TugasTeknisiDiperbarui($pesanan->fresh()))->toOthers();
            unset($broadcast);
        } catch (\Throwable) {
            // Abaikan kegagalan broadcast realtime agar teknisi tetap bisa menyelesaikan tugas.
        }
    }

    public function dashboard(StockAlertService $stockAlerts)
    {
        $teknisiId = (int) Auth::id();
        $activeTasks = $this->activeTasks($teknisiId);
        $activeCount = $activeTasks->count();

        $selesaiBulanIni = $this->taskBaseQuery($teknisiId)
            ->whereIn('status', $this->historyTaskStatuses())
            ->whereMonth('teknisi_selesai_at', now()->month)
            ->whereYear('teknisi_selesai_at', now()->year)
            ->count();

        return view('teknisi.dashboard', [
            'summary' => [
                'pekerjaan_aktif' => $activeCount,
                'sedang_dikerjakan' => $activeTasks->where('status', Pesanan::STATUS_DIKERJAKAN_TEKNISI)->count(),
                'selesai_bulan_ini' => $selesaiBulanIni,
            ],
            'stockAlerts' => $stockAlerts->teknisiDashboard(),
        ]);
    }

    public function pekerjaanAktif(Request $request)
    {
        $teknisiId = (int) Auth::id();
        $filter = (string) $request->query('filter', 'semua');
        if (!in_array($filter, ['semua', 'produk', 'service-refill'], true)) {
            $filter = 'semua';
        }

        $allTasks = $this->activeTasks($teknisiId);
        $tasks = $this->filterActiveTasks($allTasks, $filter);

        return view('teknisi.pekerjaan-aktif', [
            'tasks' => $tasks,
            'activeFilter' => $filter,
            'tabCounts' => [
                'semua' => $allTasks->count(),
                'produk' => $allTasks->filter(fn (Pesanan $task) => $task->isProductOrder())->count(),
                'service-refill' => $allTasks->filter(fn (Pesanan $task) => !$task->isProductOrder())->count(),
            ],
        ]);
    }

    public function riwayatPekerjaan()
    {
        $teknisiId = (int) Auth::id();
        $tasks = $this->historyTasks($teknisiId);

        return view('teknisi.riwayat-pekerjaan', compact('tasks'));
    }

    public function tugasServiceRefill(Request $request)
    {
        $request->merge(['filter' => 'service-refill']);

        return $this->pekerjaanAktif($request);
    }

    public function tugasProduk()
    {
        return redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'produk']);
    }

    public function riwayatTugas()
    {
        return $this->riwayatPekerjaan();
    }

    public function tugasMulai(Pesanan $pesanan)
    {
        if ($pesanan->teknisi_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke pekerjaan ini.');
        }

        if ((string) $pesanan->status !== Pesanan::STATUS_DITUGASKAN_KE_TEKNISI) {
            return back()->with('error', 'Pekerjaan ini tidak bisa mulai diproses dari status saat ini.');
        }

        $pesanan->update([
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
        ]);

        $this->broadcastTaskUpdate($pesanan);

        return back()->with('success', 'Pekerjaan masuk ke status dikerjakan teknisi.');
    }

    public function ajukanTambahan(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'service_tambahan_detail' => 'required|string|max:1000',
            'service_tambahan_biaya' => 'required|numeric|min:0',
        ]);

        if ($pesanan->teknisi_id !== Auth::id() || $pesanan->tipe !== 'service') {
            return back()->with('error', 'Anda tidak memiliki akses ke pekerjaan ini.');
        }

        $pesanan->update([
            'status' => 'menunggu persetujuan biaya',
            'service_tambahan_detail' => $request->service_tambahan_detail,
            'service_tambahan_biaya' => $request->service_tambahan_biaya,
        ]);

        $this->broadcastTaskUpdate($pesanan);

        return back()->with('success', 'Pengajuan tambahan biaya berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function tugasSelesai(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($pesanan->teknisi_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke pekerjaan ini.');
        }

        if ((string) $pesanan->status !== Pesanan::STATUS_DIKERJAKAN_TEKNISI) {
            return back()->with('error', 'Pekerjaan ini belum berada pada tahap dikerjakan teknisi.');
        }

        $pesanan->update([
            'status' => Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            'teknisi_selesai_at' => now(),
            'teknisi_catatan' => trim((string) $request->input('catatan')) ?: null,
        ]);

        $this->broadcastTaskUpdate($pesanan);

        return back()->with('success', 'Pekerjaan ditandai selesai oleh teknisi dan menunggu tindak lanjut admin.');
    }

    public function refillStock()
    {
        return redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']);
    }

    public function mulaiRefill(Request $request, $id = null)
    {
        return redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']);
    }

    public function selesaiRefill(Request $request, $id = null)
    {
        return redirect()->route('teknisi.pekerjaan-aktif', ['filter' => 'service-refill']);
    }

    public function serviceLog()
    {
        $pendingServices = \App\Models\Service::with(['unitApar.pelanggan', 'pesanan.pelanggan', 'servicePaket'])
            ->where('status_konfirmasi', 'pending')
            ->latest()
            ->get();

        $reportedServices = \App\Models\Service::with(['unitApar.pelanggan', 'pesanan.pelanggan', 'servicePaket'])
            ->where('status_konfirmasi', 'reported')
            ->latest()
            ->get();

        $completedServices = \App\Models\Service::with(['unitApar.pelanggan', 'pesanan.pelanggan', 'servicePaket'])
            ->where('status_konfirmasi', 'confirmed')
            ->latest('tgl_selesai_admin')
            ->get();

        return view('teknisi.service-log', compact('pendingServices', 'reportedServices', 'completedServices'));
    }

    public function submitServiceReport(Request $request, \App\Models\Service $service)
    {
        $request->validate([
            'catatan_teknisi' => 'nullable|string|max:1000',
            'laporan_foto' => 'nullable|image|max:2048',
            'peralatan_used' => 'nullable|array',
            'peralatan_used.*.id' => 'required|exists:peralatans,id',
            'peralatan_used.*.jumlah' => 'required|integer|min:0',
        ]);

        if ($service->status_konfirmasi !== 'pending') {
            return back()->with('error', 'Service ini tidak bisa dilaporkan.');
        }

        $path = null;
        if ($request->hasFile('laporan_foto')) {
            $path = $request->file('laporan_foto')->store('service_reports', 'public');
        }

        $peralatanUsed = $request->peralatan_used ?? [];
        $actualPeralatan = [];

        foreach ($peralatanUsed as $item) {
            if (($item['jumlah'] ?? 0) > 0) {
                $peralatan = \App\Models\Peralatan::find($item['id']);
                $actualPeralatan[] = [
                    'id' => $peralatan->id,
                    'peralatan_id' => $peralatan->id,
                    'nama' => $peralatan->nama,
                    'jumlah' => (int) $item['jumlah'],
                ];
            }
        }

        $service->update([
            'catatan_teknisi' => $request->catatan_teknisi,
            'laporan_foto' => $path,
            'actual_peralatan_json' => json_encode($actualPeralatan),
            'status_konfirmasi' => 'reported',
        ]);

        return back()->with('success', 'Laporan service berhasil diajukan. Menunggu konfirmasi admin.');
    }
}
