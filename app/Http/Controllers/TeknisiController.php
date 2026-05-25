<?php

namespace App\Http\Controllers;

use App\Events\TugasTeknisiDiperbarui;
use App\Models\Pesanan;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeknisiController extends Controller
{
    private function activeTaskStatuses(): array
    {
        return ['ditugaskan ke teknisi', 'dikerjakan teknisi'];
    }

    private function historyTaskStatuses(): array
    {
        return ['selesai oleh teknisi', 'dikonfirmasi admin', 'selesai final', 'selesai'];
    }

    private function taskBaseQuery(int $teknisiId)
    {
        return Pesanan::query()
            ->where('teknisi_id', $teknisiId);
    }

    private function activeTasksByType(int $teknisiId, string $tipe)
    {
        return $this->taskBaseQuery($teknisiId)
            ->with(['pelanggan', 'details.produk'])
            ->where('tipe', $tipe)
            ->whereIn('status', $this->activeTaskStatuses())
            ->whereNull('teknisi_selesai_at')
            ->latest('tanggal')
            ->get();
    }

    private function historyTasks(int $teknisiId)
    {
        return $this->taskBaseQuery($teknisiId)
            ->with(['pelanggan', 'details.produk'])
            ->where(function ($query) {
                $query->whereIn('status', $this->historyTaskStatuses())
                    ->orWhereNotNull('teknisi_selesai_at');
            })
            ->latest('teknisi_selesai_at')
            ->latest('updated_at')
            ->get();
    }

    private function broadcastTaskUpdate(Pesanan $pesanan): void
    {
        try {
            $broadcast = broadcast(new TugasTeknisiDiperbarui($pesanan->fresh()))->toOthers();
            unset($broadcast);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function dashboard()
    {
        $teknisiId = (int) Auth::id();
        $tasks = $this->taskBaseQuery($teknisiId)
            ->with(['pelanggan', 'details.produk'])
            ->whereIn('status', $this->activeTaskStatuses())
            ->whereNull('teknisi_selesai_at')
            ->latest('tanggal')
            ->get();

        $aktifService = $tasks->count();

        $selesaiBulanIni = $this->taskBaseQuery($teknisiId)
            ->whereIn('status', $this->historyTaskStatuses())
            ->whereMonth('teknisi_selesai_at', now()->month)
            ->whereYear('teknisi_selesai_at', now()->year)
            ->count();

        $historyTasks = $this->historyTasks($teknisiId);

        return view('teknisi.dashboard', [
            'tasks' => $tasks,
            'historyTasks' => $historyTasks,
            'summary' => [
                'aktif_service' => $aktifService,
                'total' => $aktifService,
                'selesai_bulan_ini' => $selesaiBulanIni,
            ],
        ]);
    }

    public function tugasProduk()
    {
        $teknisiId = (int) Auth::id();
        $tasks = $this->taskBaseQuery($teknisiId)
            ->with(['pelanggan', 'details.produk'])
            ->where('tipe', 'produk')
            ->whereIn('status', $this->activeTaskStatuses())
            ->whereNull('teknisi_selesai_at')
            ->latest('tanggal')
            ->get();

        return view('teknisi.tugas-produk', compact('tasks'));
    }

    public function tugasServiceRefill()
    {
        $teknisiId = (int) Auth::id();
        $tasks = $this->taskBaseQuery($teknisiId)
            ->with(['pelanggan', 'details.produk'])
            ->whereIn('status', $this->activeTaskStatuses())
            ->whereNull('teknisi_selesai_at')
            ->latest('tanggal')
            ->get();

        return view('teknisi.tugas-service-refill', compact('tasks'));
    }

    public function riwayatTugas()
    {
        $teknisiId = (int) Auth::id();
        $tasks = $this->historyTasks($teknisiId);

        return view('teknisi.riwayat-tugas', compact('tasks'));
    }

    public function tugasMulai(Pesanan $pesanan)
    {
        if ($pesanan->teknisi_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke tugas ini.');
        }

        $pesanan->update([
            'status' => 'dikerjakan teknisi',
        ]);

        $this->broadcastTaskUpdate($pesanan);

        return back()->with('success', 'Tugas berhasil diterima dan status diperbarui menjadi dikerjakan teknisi.');
    }

    public function ajukanTambahan(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'service_tambahan_detail' => 'required|string|max:1000',
            'service_tambahan_biaya' => 'required|numeric|min:0',
        ]);

        if ($pesanan->teknisi_id !== Auth::id() || $pesanan->tipe !== 'service') {
            return back()->with('error', 'Anda tidak memiliki akses ke tugas ini.');
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
            return back()->with('error', 'Anda tidak memiliki akses ke tugas ini.');
        }

        $baseBiaya = (float)($pesanan->service_estimasi_biaya ?? $pesanan->total_harga ?? 0);
        $tambahanBiaya = (float)($pesanan->service_tambahan_biaya ?? 0);
        $grandTotal = $baseBiaya + $tambahanBiaya;

        $snapshot = [
            'tanggal_snapshot' => now()->toDateTimeString(),
            'biaya_awal' => $baseBiaya,
            'tambahan_detail' => $pesanan->service_tambahan_detail,
            'tambahan_biaya' => $tambahanBiaya,
            'grand_total' => $grandTotal,
            'pelanggan_nama' => $pesanan->pelanggan?->nama,
            'tipe_layanan' => $pesanan->service_jenis_layanan,
        ];

        $pesanan->update([
            'status' => 'selesai oleh teknisi',
            'teknisi_selesai_at' => now(),
            'teknisi_catatan' => $request->input('catatan'),
            'total' => $grandTotal,
            'total_harga' => $grandTotal,
            'invoice_snapshot' => json_encode($snapshot),
        ]);

        $this->broadcastTaskUpdate($pesanan);

        return back()->with('success', 'Laporan pekerjaan berhasil disimpan dan sudah diteruskan ke admin untuk konfirmasi.');
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
            return back()->with('error', 'Tugas sudah dikerjakan atau selesai.');
        }

        $tugasRefill->update([
            'teknisi_id' => \Illuminate\Support\Facades\Auth::id(),
            'status' => 'diproses',
        ]);

        return back()->with('success', 'Tugas refill mulai diproses.');
    }

    public function selesaiRefill(Request $request, \App\Models\TugasRefill $tugasRefill)
    {
        $request->validate([
            'tanggal_refill' => 'required|date',
            'catatan_teknisi' => 'nullable|string',
            'bukti_foto' => 'nullable|image|max:2048',
        ]);

        if ($tugasRefill->teknisi_id !== \Illuminate\Support\Facades\Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses ke tugas ini.');
        }

        $path = null;
        if ($request->hasFile('bukti_foto')) {
            $path = $request->file('bukti_foto')->store('refill_proofs', 'public');
        }

        $produk = $tugasRefill->produk;
        $isOneKg = trim(strtolower($produk->kapasitas)) === '1 kg';
        $date = \Carbon\Carbon::parse($request->tanggal_refill);
        $tgl_expired = $isOneKg ? $date->addMonths(6) : $date->addYear();

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
