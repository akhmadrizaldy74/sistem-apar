<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\StockMovement;
use App\Models\StokBatch;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index()
    {
        $activeTab = request()->query('tab', 'apar');
        if (! in_array($activeTab, ['apar', 'refill', 'peralatan'], true)) {
            $activeTab = 'apar';
        }

        $produks = Produk::with(['jenisApar', 'stokBatches.tugasRefills'])->latest()->get();
        $jenisRefills = JenisRefill::latest()->get();
        $peralatans = Peralatan::latest()->get();
        $stockMovements = StockMovement::latest('tanggal')->latest()->take(30)->get();

        return view('admin.stok.index', compact('produks', 'jenisRefills', 'peralatans', 'activeTab', 'stockMovements'));
    }

    // ─── CRUD Peralatan ───
    public function storePeralatan(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'harga_standar' => 'required|numeric|min:0',
            'stok_minimum'  => 'nullable|integer|min:0',
        ]);

        Peralatan::create([
            'nama'          => $validated['nama'],
            'stok'          => 0,
            'harga_standar' => $validated['harga_standar'],
            'stok_minimum'  => $validated['stok_minimum'] ?? 3,
        ]);

        return redirect()->route('admin.stok.index', ['tab' => 'peralatan'])
            ->with('success', 'Master peralatan berhasil ditambahkan. Penambahan stok dilakukan dari menu Pengeluaran.');
    }

    public function updatePeralatan(Request $request, Peralatan $peralatan)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:255',
            'harga_standar' => 'required|numeric|min:0',
            'stok_minimum'  => 'nullable|integer|min:0',
        ]);

        $peralatan->update([
            'nama'          => $validated['nama'],
            'harga_standar' => $validated['harga_standar'],
            'stok_minimum'  => $validated['stok_minimum'] ?? $peralatan->stok_minimum,
        ]);

        return redirect()->route('admin.stok.index', ['tab' => 'peralatan'])
            ->with('success', 'Master peralatan berhasil diperbarui.');
    }

    public function destroyPeralatan(Peralatan $peralatan)
    {
        $peralatan->delete();
        return redirect()->route('admin.stok.index', ['tab' => 'peralatan'])
            ->with('success', 'Data peralatan berhasil dihapus.');
    }

    public function storeBatch(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'jumlah_masuk' => 'required|integer|min:1',
            'tgl_produksi' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $produk = Produk::findOrFail($request->produk_id);

        // Aturan expired:
        // APAR ukuran 1 kg = tgl_produksi + 6 bulan
        // APAR selain 1 kg = tgl_produksi + 1 tahun
        $date = Carbon::parse($request->tgl_produksi);
        
        $ukuranAngka = (float) filter_var($produk->kapasitas, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        if ($ukuranAngka == 1.0) {
            $tgl_expired = $date->addMonths(6);
        } else {
            $tgl_expired = $date->addYear();
        }

        DB::transaction(function () use ($request, $produk, $tgl_expired, $inventoryService) {
            $stokSebelum = (float) $produk->fresh()->stok_tersedia;

            StokBatch::create([
                'produk_id' => $request->produk_id,
                'jumlah_masuk' => $request->jumlah_masuk,
                'sisa_qty' => $request->jumlah_masuk,
                'tgl_produksi' => $request->tgl_produksi,
                'tgl_expired' => $tgl_expired,
                'keterangan' => $request->keterangan,
            ]);

            // Sinkronkan stok agregat agar kompatibel dengan proses lama.
            $produk->stok = ($produk->stok ?? 0) + $request->jumlah_masuk;
            $produk->save();

            $stokSesudah = (float) $produk->fresh('stokBatches')->stok_tersedia;

            $inventoryService->logProductMovement(
                produk: $produk->fresh(),
                qty: (float) $request->jumlah_masuk,
                movementType: StockMovement::MOVE_IN,
                sourceType: StockMovement::SOURCE_BATCH_APAR,
                stokSebelum: $stokSebelum,
                stokSesudah: $stokSesudah,
                reference: null,
                keterangan: 'Tambah batch APAR: ' . $produk->nama,
                tanggal: $request->tgl_produksi,
            );
        });

        return redirect()->route('admin.stok.index', ['tab' => 'apar'])->with('success', 'Batch stok berhasil ditambahkan.');
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
