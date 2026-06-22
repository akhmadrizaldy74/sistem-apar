<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Services\ProductExpiryAlertService;
use App\Services\ServiceMasterSyncService;
use App\Services\StockHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index(
        StockHistoryService $stockHistoryService,
        ServiceMasterSyncService $serviceMasterSyncService,
        ProductExpiryAlertService $productExpiryAlerts
    )
    {
        $activeTab = request()->query('tab', 'apar');
        if (! in_array($activeTab, ['apar', 'refill', 'peralatan'], true)) {
            $activeTab = 'apar';
        }
        $activeAparFilter = $productExpiryAlerts->normalizeFilter((string) request()->query('filter', ProductExpiryAlertService::FILTER_ALL));

        $produks = Produk::with(['jenisApar', 'stokBatches.tugasRefills'])->latest()->get();
        $jenisRefills = JenisRefill::latest()->get();
        $peralatans = $serviceMasterSyncService->visiblePeralatans();
        $stockHistories = $stockHistoryService->recent();
        $aparStockSummary = $productExpiryAlerts->stockPage($activeAparFilter);

        return view('admin.stok.index', compact(
            'produks',
            'jenisRefills',
            'peralatans',
            'activeTab',
            'activeAparFilter',
            'stockHistories',
            'aparStockSummary'
        ));
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
            'tanggal_refill' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $produk = $stokBatch->produk()->with(['jenisApar', 'stokBatches'])->firstOrFail();
        $today = now()->startOfDay();

        $positiveBatches = $produk->stokBatches
            ->filter(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0) > 0)
            ->values();

        if ($positiveBatches->isEmpty()) {
            return redirect()
                ->route('admin.stok.index', ['tab' => 'apar', 'filter' => ProductExpiryAlertService::FILTER_PROBLEM])
                ->with('error', 'Produk ini tidak memiliki stok aktif yang dapat diperbarui masa berlakunya.');
        }

        $tanggalRefill = Carbon::parse($request->input('tanggal_refill'))->startOfDay();
        $expiredBaru = UnitApar::calculateExpiry(
            $tanggalRefill->toDateString(),
            $produk->kapasitas,
            $produk->jenisApar?->nama,
        )->startOfDay();
        $catatanTambahan = trim((string) $request->input('keterangan'));
        $stokTerdampak = (int) $positiveBatches->sum(fn (StokBatch $batch) => (int) ($batch->sisa_qty ?? 0));

        // --- Resolve jenis refill yang cocok berdasarkan jenis APAR ---
        $jenisAparNama = strtolower(trim((string) ($produk->jenisApar?->nama ?? '')));
        $jenisRefills = JenisRefill::all();
        $matchedRefill = $jenisRefills->first(function (JenisRefill $jr) use ($jenisAparNama) {
            $refillNama = strtolower(trim((string) $jr->nama));
            if ((str_contains($jenisAparNama, 'co2') || str_contains($jenisAparNama, 'carbon'))
                && (str_contains($refillNama, 'co2') || str_contains($refillNama, 'carbon'))) {
                return true;
            }
            if ((str_contains($jenisAparNama, 'powder') || str_contains($jenisAparNama, 'dry chemical'))
                && (str_contains($refillNama, 'powder') || str_contains($refillNama, 'dry chemical'))) {
                return true;
            }
            if ((str_contains($jenisAparNama, 'foam') || str_contains($jenisAparNama, 'busa'))
                && (str_contains($refillNama, 'foam') || str_contains($refillNama, 'busa'))) {
                return true;
            }
            return false;
        });

        if (! $matchedRefill) {
            return redirect()
                ->route('admin.stok.index', ['tab' => 'apar', 'filter' => ProductExpiryAlertService::FILTER_PROBLEM])
                ->with('error', 'Jenis refill untuk produk ' . $produk->nama . ' belum tersedia. Tambahkan stok refill yang sesuai terlebih dahulu sebelum memperbarui masa berlaku.');
        }

        // --- Hitung kebutuhan refill (kg) = jumlah unit × ukuran APAR ---
        $ukuranKg = UnitApar::extractSizeKg($produk->kapasitas) ?? 1.0;
        $kebutuhanRefillKg = round($stokTerdampak * $ukuranKg, 2);

        // --- Validasi stok refill cukup ---
        $stokRefillSekarang = (float) ($matchedRefill->stok ?? 0);
        if ($stokRefillSekarang < $kebutuhanRefillKg) {
            return redirect()
                ->route('admin.stok.index', ['tab' => 'apar', 'filter' => ProductExpiryAlertService::FILTER_PROBLEM])
                ->with('error', 'Stok refill ' . $matchedRefill->nama . ' tidak mencukupi untuk memperbarui masa berlaku '
                    . $stokTerdampak . ' unit ' . $produk->nama . '. Dibutuhkan '
                    . number_format($kebutuhanRefillKg, 2, ',', '.') . ' Kg, tersedia '
                    . number_format($stokRefillSekarang, 2, ',', '.') . ' Kg.');
        }

        $inventoryService = app(\App\Services\InventoryService::class);
        $refillDeductedLabel = '';

        DB::transaction(function () use ($positiveBatches, $tanggalRefill, $expiredBaru, $catatanTambahan, $matchedRefill, $kebutuhanRefillKg, $inventoryService, $produk, $stokTerdampak, &$refillDeductedLabel) {
            // Update masa berlaku semua batch aktif
            foreach ($positiveBatches as $batch) {
                $keterangan = collect([
                    trim((string) $batch->keterangan),
                    $catatanTambahan,
                    'Masa berlaku diperbarui admin pada ' . $tanggalRefill->copy()->locale('id')->isoFormat('D MMMM YYYY'),
                ])->filter()->implode(' | ');

                $batch->update([
                    'tgl_produksi' => $tanggalRefill->toDateString(),
                    'tgl_expired' => $expiredBaru->toDateString(),
                    'keterangan' => $keterangan,
                ]);
            }

            // Kurangi stok refill
            if ($kebutuhanRefillKg > 0) {
                $inventoryService->decreaseRefillStock(
                    $matchedRefill,
                    $kebutuhanRefillKg,
                    \App\Models\StockMovement::SOURCE_HASIL_REFILL_BATCH,
                    $produk,
                    'Refill batch ' . $produk->nama . ' (' . $stokTerdampak . ' unit × ' . number_format($kebutuhanRefillKg / max($stokTerdampak, 1), 1, ',', '.') . ' Kg)',
                    $tanggalRefill,
                );
                $refillDeductedLabel = ' Stok refill ' . $matchedRefill->nama . ' berkurang ' . number_format($kebutuhanRefillKg, 2, ',', '.') . ' Kg.';
            }
        });

        $produk->refresh();
        $produk->syncStoredStockFromBatches();

        return redirect()
            ->route('admin.stok.index', ['tab' => 'apar', 'filter' => ProductExpiryAlertService::FILTER_PROBLEM])
            ->with('success', 'Masa berlaku ' . $stokTerdampak . ' unit stok APAR berhasil diperbarui, dengan masa berlaku baru sampai ' . $expiredBaru->locale('id')->isoFormat('D MMMM YYYY') . '.' . $refillDeductedLabel);
    }
}
