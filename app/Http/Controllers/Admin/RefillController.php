<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pesanan;
use App\Models\Refill;
use App\Models\StockMovement;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefillController extends Controller
{
    public function index()
    {
        $refills = Refill::with(['unitApar.pelanggan', 'unitApar.produk', 'jenisRefill', 'service.pesanan'])->latest('tgl_refill')->get();
        $units = UnitApar::with(['pelanggan', 'produk'])->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $refillPackages = $this->refillPackages();

        $requestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereNotIn('status', ['selesai final', 'ditolak'])
            ->latest()
            ->get();

        $completedRequestRefills = Pesanan::with(['pelanggan', 'teknisi', 'serviceJenisRefill'])
            ->where('tipe', 'service')
            ->where('service_jenis_layanan', 'refill')
            ->whereIn('status', ['selesai final'])
            ->latest()
            ->get();

        $teknisis = User::where('role', 'teknisi')->orderBy('name')->get();

        $refillStatusFlow = [
            Pesanan::STATUS_PENDING,
            Pesanan::STATUS_PERMINTAAN_MASUK,
            Pesanan::STATUS_DIREVIEW_ADMIN,
            Pesanan::STATUS_MENUNGGU_PENJADWALAN,
            Pesanan::STATUS_MENUNGGU_PERSETUJUAN_BIAYA,
            Pesanan::STATUS_DISETUJUI,
            Pesanan::STATUS_MENUNGGU_PENGAMBILAN,
            Pesanan::STATUS_MENUNGGU_KEDATANGAN_UNIT,
            Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            Pesanan::STATUS_SELESAI_FINAL,
            Pesanan::STATUS_DITOLAK,
        ];

        return view('admin.refill.index', compact(
            'refills',
            'units',
            'jenisRefills',
            'refillPackages',
            'requestRefills',
            'completedRequestRefills',
            'teknisis',
            'refillStatusFlow'
        ));
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

            $unit->tgl_expired = \Carbon\Carbon::parse($request->tgl_refill)->addYear();
            $unit->save();
        });

        $stokTerbaru = (float) $jenisRefill->fresh()->stok;

        return redirect()
            ->route('admin.refill.index')
            ->with('success', "Data refil APAR berhasil disimpan. Pemakaian {$jumlahPakai} {$satuanLabel}. Stok {$jenisRefill->nama} sekarang {$stokTerbaru} {$satuanLabel}.");
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

        return redirect()->route('admin.refill.index')->with('success', 'Data refil APAR berhasil diperbarui.');
    }

    public function destroy(Refill $refill)
    {
        $refill->delete();

        return redirect()->route('admin.refill.index')->with('success', 'Data refil APAR berhasil dihapus.');
    }

    public function assignTeknisi(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->service_jenis_layanan !== 'refill') {
            return back()->with('error', 'Data ini bukan refil APAR.');
        }

        $request->validate([
            'teknisi_id' => 'required|exists:users,id',
        ]);

        $teknisi = User::findOrFail($request->teknisi_id);
        if ($teknisi->role !== 'teknisi') {
            return back()->with('error', 'User yang dipilih bukan teknisi.');
        }

        $pesanan->update([
            'teknisi_id' => $request->teknisi_id,
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan ke data refil.');
    }

    public function updateStatus(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->service_jenis_layanan !== 'refill') {
            return back()->with('error', 'Data ini bukan refil APAR.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,permintaan masuk,direview admin,menunggu penjadwalan,menunggu persetujuan biaya,disetujui,menunggu pengambilan,menunggu kedatangan unit,ditugaskan ke teknisi,dikerjakan teknisi,selesai oleh teknisi,dikonfirmasi admin,selesai final,ditolak',
            'service_estimasi_biaya' => 'nullable|string|max:30',
            'service_admin_catatan' => 'nullable|string|max:1000',
        ]);

        $estimasiRaw = preg_replace('/[^\d]/', '', (string) ($validated['service_estimasi_biaya'] ?? ''));
        $estimasiBiaya = $estimasiRaw !== '' ? (float) $estimasiRaw : null;

        $payload = [
            'status' => $validated['status'],
            'service_admin_catatan' => $validated['service_admin_catatan'] ?? $pesanan->service_admin_catatan,
        ];

        if (!is_null($estimasiBiaya)) {
            $payload['service_estimasi_biaya'] = $estimasiBiaya;
        }

        $pesanan->update($payload);

        return back()->with('success', 'Status refil APAR berhasil diperbarui.');
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
