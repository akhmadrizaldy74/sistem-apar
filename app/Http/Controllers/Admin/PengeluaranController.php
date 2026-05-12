<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluarans = Pengeluaran::with(['jenisRefill', 'peralatan'])
            ->latest('tanggal')
            ->latest()
            ->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $peralatans = Peralatan::orderBy('nama')->get();

        return view('admin.pengeluaran.index', compact('pengeluarans', 'jenisRefills', 'peralatans'));
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $validated = $request->validate([
            'jenis_pengeluaran' => 'required|in:' . implode(',', [
                Pengeluaran::JENIS_PEMBELIAN_REFILL,
                Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            ]),
            'jenis_refill_id' => 'nullable|required_if:jenis_pengeluaran,' . Pengeluaran::JENIS_PEMBELIAN_REFILL . '|exists:jenis_refills,id',
            'peralatan_id' => 'nullable|required_if:jenis_pengeluaran,' . Pengeluaran::JENIS_PEMBELIAN_PERALATAN . '|exists:peralatans,id',
            'qty' => 'required|numeric|min:0.01',
            'harga_beli' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
        ]);

        if (
            $validated['jenis_pengeluaran'] === Pengeluaran::JENIS_PEMBELIAN_PERALATAN
            && floor((float) $validated['qty']) !== (float) $validated['qty']
        ) {
            return back()->withInput()->withErrors([
                'qty' => 'Qty peralatan/perlengkapan harus berupa bilangan bulat.',
            ]);
        }

        DB::transaction(function () use ($validated, $inventoryService) {
            $pengeluaran = Pengeluaran::create([
                'kategori' => $validated['jenis_pengeluaran'] === Pengeluaran::JENIS_PEMBELIAN_REFILL ? 'refill' : 'peralatan',
                'jenis_pengeluaran' => $validated['jenis_pengeluaran'],
                'jenis_refill_id' => $validated['jenis_refill_id'] ?? null,
                'peralatan_id' => $validated['peralatan_id'] ?? null,
                'qty' => $validated['qty'],
                'harga_beli' => $validated['harga_beli'],
                'total' => (float) $validated['qty'] * (float) $validated['harga_beli'],
                'nominal' => (float) $validated['qty'] * (float) $validated['harga_beli'],
                'keterangan' => $validated['keterangan'] ?? null,
                'tanggal' => $validated['tanggal'],
            ]);

            $inventoryService->applyPurchaseExpense($pengeluaran);
        });

        return redirect()->route('admin.pengeluaran.index')->with('success', 'Transaksi pembelian berhasil disimpan dan stok otomatis diperbarui.');
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        return redirect()
            ->route('admin.pengeluaran.index')
            ->with('error', 'Transaksi pembelian yang memengaruhi stok tidak dapat diedit dari menu ini.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        return redirect()
            ->route('admin.pengeluaran.index')
            ->with('error', 'Transaksi pembelian yang memengaruhi stok tidak dapat dihapus dari menu ini.');
    }
}
