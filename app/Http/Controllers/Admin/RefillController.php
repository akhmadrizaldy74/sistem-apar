<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Refill;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefillController extends Controller
{
    public function index()
    {
        $refills = Refill::with(['unitApar.pelanggan', 'jenisRefill', 'service.pesanan'])->latest('tgl_refill')->get();
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $refillPackages = $this->refillPackages();

        return view('admin.refill.index', compact('refills', 'units', 'jenisRefills', 'refillPackages'));
    }

    public function create()
    {
        return redirect()->route('admin.refill.index');
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'unit_apar_id'    => 'required|exists:unit_apars,id',
            'jenis_refill_id' => 'required|exists:jenis_refills,id',
            'tgl_refill'      => 'required|date',
            'biaya'           => 'required|numeric|min:0',
        ]);

        $unit = UnitApar::with('produk')->findOrFail((int) $request->unit_apar_id);
        $jenisRefill = JenisRefill::findOrFail($request->jenis_refill_id);
        $jumlahPakai = $this->extractUnitUsage($unit);
        $satuanLabel = $jenisRefill->satuan_label;

        if ($jenisRefill->stok < $jumlahPakai) {
            return back()
                ->withInput()
                ->withErrors([
                    'jenis_refill_id' => "Stok bahan {$jenisRefill->nama} tidak cukup. Dibutuhkan {$jumlahPakai} {$satuanLabel}, tersedia {$jenisRefill->stok} {$satuanLabel}.",
                ]);
        }

        DB::transaction(function () use ($request, $inventoryService, $jenisRefill, $jumlahPakai, $unit) {
            $refill = Refill::create($request->only('unit_apar_id', 'jenis_refill_id', 'tgl_refill', 'biaya'));

            $inventoryService->decreaseRefillStock(
                jenisRefill: $jenisRefill,
                qty: $jumlahPakai,
                sourceType: StockMovement::SOURCE_REFILL_PELANGGAN,
                reference: $refill,
                keterangan: 'Pemakaian refill untuk unit ' . $unit->no_seri,
                tanggal: $request->tgl_refill,
            );

            // Perbarui tanggal expired unit APAR (ditambah 1 tahun dari tanggal refill).
            $unit->tgl_expired = \Carbon\Carbon::parse($request->tgl_refill)->addYear();
            $unit->save();
        });

        $stokTerbaru = (float) $jenisRefill->fresh()->stok;

        return redirect()
            ->route('admin.refill.index')
            ->with('success', "Data refill berhasil ditambahkan. Pemakaian {$jumlahPakai} {$satuanLabel}. Stok {$jenisRefill->nama} sekarang {$stokTerbaru} {$satuanLabel}.");
    }

    public function show(Refill $refill)
    {
        return redirect()->route('admin.refill.edit', $refill);
    }

    public function edit(Refill $refill)
    {
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $refillPackages = $this->refillPackages();

        return view('admin.refill.edit', compact('refill', 'units', 'jenisRefills', 'refillPackages'));
    }

    public function update(Request $request, Refill $refill)
    {
        $request->validate([
            'unit_apar_id' => 'required|exists:unit_apars,id',
            'jenis_refill_id' => 'required|exists:jenis_refills,id',
            'tgl_refill' => 'required|date',
            'biaya' => 'required|numeric|min:0',
        ]);

        $refill->update($request->only('unit_apar_id', 'jenis_refill_id', 'tgl_refill', 'biaya'));

        return redirect()->route('admin.refill.index')->with('success', 'Data refill berhasil diperbarui.');
    }

    public function destroy(Refill $refill)
    {
        $refill->delete();

        return redirect()->route('admin.refill.index')->with('success', 'Data refill berhasil dihapus.');
    }

    protected function refillPackages(): array
    {
        return [
            'Powder' => 200000,
            'CO2' => 275000,
            'Foam' => 250000,
        ];
    }

    protected function extractUnitUsage(UnitApar $unit): float
    {
        $kapasitasRaw = (string) ($unit->ukuran ?: $unit->produk?->kapasitas ?: '');
        $angka = (float) filter_var($kapasitasRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        return $angka > 0 ? $angka : 1.0;
    }
}
