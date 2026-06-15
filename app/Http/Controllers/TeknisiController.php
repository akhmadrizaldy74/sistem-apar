<?php

namespace App\Http\Controllers;

use App\Events\TugasTeknisiDiperbarui;
use App\Models\Pesanan;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Services\InventoryService;
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

    public function dashboard()
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
        $teknisiId = \Illuminate\Support\Facades\Auth::id();
        $tugasRefill = \App\Models\TugasRefill::with(['produk.jenisApar', 'stokBatch'])
            ->where(function($query) use ($teknisiId) {
                $query->whereNull('teknisi_id')->orWhere('teknisi_id', $teknisiId);
            })
            ->whereIn('status', ['menunggu', 'diproses'])
            ->latest()
            ->get();

        return view('teknisi.refill.index', compact('tugasRefill'));
    }

    public function mulaiRefill(Request $request, \App\Models\TugasRefill $tugasRefill)
    {
        if ($tugasRefill->status !== 'menunggu') {
            return back()->with('error', 'Pekerjaan sudah dikerjakan atau selesai.');
        }

        $tugasRefill->update([
            'teknisi_id' => \Illuminate\Support\Facades\Auth::id(),
            'status' => 'diproses',
        ]);

        return back()->with('success', 'Pekerjaan refill mulai diproses.');
    }

    public function selesaiRefill(Request $request, \App\Models\TugasRefill $tugasRefill)
    {
        $request->validate([
            'tanggal_refill' => 'required|date',
            'catatan_teknisi' => 'nullable|string',
            'bukti_foto' => 'nullable|image|max:2048',
        ]);

        if ($tugasRefill->teknisi_id !== \Illuminate\Support\Facades\Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke pekerjaan ini.');
        }

        $path = null;
        if ($request->hasFile('bukti_foto')) {
            $path = $request->file('bukti_foto')->store('refill_proofs', 'public');
        }

        $produk = $tugasRefill->produk;
        $tgl_expired = UnitApar::calculateExpiry(
            $request->tanggal_refill,
            $produk->kapasitas ?? '-',
            $produk->jenisApar?->nama ?? '-',
        );

        DB::transaction(function() use ($tugasRefill, $request, $tgl_expired, $path, $produk) {
            $inventoryService = app(InventoryService::class);
            $stokSebelum = (float) $produk->fresh()->stok_tersedia;
            $stokBatch = $tugasRefill->stokBatch;
            $stokBatch->decrement('sisa_qty', $tugasRefill->jumlah_refill);

            \App\Models\StokBatch::create([
                'produk_id' => $produk->id,
                'jumlah_masuk' => $tugasRefill->jumlah_refill,
                'sisa_qty' => $tugasRefill->jumlah_refill,
                'tgl_produksi' => $request->tanggal_refill,
                'tgl_expired' => $tgl_expired,
                'keterangan' => 'Hasil Refill oleh teknisi (ID Tugas: '.$tugasRefill->id.')',
            ]);

            $tugasRefill->update([
                'tanggal_refill' => $request->tanggal_refill,
                'catatan_teknisi' => $request->catatan_teknisi,
                'bukti_foto' => $path,
                'status' => 'selesai',
            ]);

            $produk->increment('stok', $tugasRefill->jumlah_refill);

            $inventoryService->logProductMovement(
                produk: $produk->fresh(),
                qty: (float) $tugasRefill->jumlah_refill,
                movementType: StockMovement::MOVE_IN,
                sourceType: StockMovement::SOURCE_HASIL_REFILL_BATCH,
                stokSebelum: $stokSebelum,
                stokSesudah: (float) $produk->fresh('stokBatches')->stok_tersedia,
                reference: $tugasRefill,
                keterangan: 'Batch hasil refill teknisi untuk produk ' . $produk->nama,
                tanggal: $request->tanggal_refill,
            );
        });

        return back()->with('success', 'Refill selesai! Stok batch baru telah otomatis dibuat.');
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
