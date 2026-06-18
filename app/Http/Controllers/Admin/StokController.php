<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Services\ServiceMasterSyncService;
use App\Services\StockHistoryService;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index(StockHistoryService $stockHistoryService, ServiceMasterSyncService $serviceMasterSyncService)
    {
        $activeTab = request()->query('tab', 'apar');
        if (! in_array($activeTab, ['apar', 'refill', 'peralatan'], true)) {
            $activeTab = 'apar';
        }

        $produks = Produk::with(['jenisApar', 'stokBatches.tugasRefills'])->latest()->get();
        $jenisRefills = JenisRefill::latest()->get();
        $peralatans = $serviceMasterSyncService->visiblePeralatans();
        $stockHistories = $stockHistoryService->recent();

        return view('admin.stok.index', compact('produks', 'jenisRefills', 'peralatans', 'activeTab', 'stockHistories'));
    }

    // ─── CRUD Peralatan ───
    public function storePeralatan(Request $request)
    {
        return redirect()->route('admin.peralatan.create')
            ->with('error', 'Data peralatan dikelola dari menu Data Layanan > Service & Peralatan, bukan dari menu Stok.');
    }

    public function updatePeralatan(Request $request, Peralatan $peralatan)
    {
        return redirect()->route('admin.peralatan.edit', $peralatan)
            ->with('error', 'Perubahan data peralatan dilakukan dari menu Data Layanan > Service & Peralatan.');
    }

    public function destroyPeralatan(Peralatan $peralatan)
    {
        return redirect()->route('admin.peralatan.index')
            ->with('error', 'Penghapusan data peralatan dilakukan dari menu Data Layanan > Service & Peralatan.');
    }

    public function storeBatch(Request $request)
    {
        return redirect()
            ->route('admin.pengeluaran.index', [
                'open' => 1,
                'jenis_pengeluaran' => \App\Models\Pengeluaran::JENIS_PEMBELIAN_APAR,
                'produk_id' => $request->input('produk_id'),
            ])
            ->with('error', 'Tambah stok APAR sekarang dilakukan melalui menu Pengeluaran dengan jenis pembelian APAR.');
    }

    public function refillBatch(Request $request, StokBatch $stokBatch)
    {
        $request->validate([
            'jumlah_masuk' => 'required|integer|min:1|max:'.$stokBatch->sisa_qty,
            'keterangan' => 'nullable|string',
        ]);

        \App\Models\TugasRefill::create([
            'stok_batch_id' => $stokBatch->id,
            'produk_id' => $stokBatch->produk_id,
            'jumlah_refill' => $request->jumlah_masuk,
            'catatan_admin' => $request->keterangan,
            'status' => 'menunggu',
        ]);

        return redirect()->route('admin.stok.index', ['tab' => 'apar'])->with('success', 'Permintaan tugas refill berhasil dibuat. Silakan tunggu teknisi memprosesnya.');
    }
}
