<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRefill;
use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\Produk;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluarans = Pengeluaran::with(['produk.jenisApar', 'jenisRefill', 'peralatan'])
            ->latest('tanggal')
            ->latest()
            ->get();
        $produks = Produk::with('jenisApar')->orderBy('nama')->get();
        $jenisRefills = JenisRefill::orderBy('nama')->get();
        $peralatans = Peralatan::orderBy('nama')->get();

        return view('admin.pengeluaran.index', compact('pengeluarans', 'produks', 'jenisRefills', 'peralatans'));
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $jenisPengeluaran = $request->input('jenis_pengeluaran');
        $qty = $request->input('qty') ?: match ($jenisPengeluaran) {
            Pengeluaran::JENIS_PEMBELIAN_APAR => $request->input('qty_apar'),
            Pengeluaran::JENIS_PEMBELIAN_REFILL => $request->input('qty_refill'),
            Pengeluaran::JENIS_PEMBELIAN_PERALATAN => $request->input('qty_peralatan'),
            default => null,
        };
        $hargaBeli = $request->input('harga_beli') ?: $request->input('harga_beli_display');

        $request->merge([
            'qty' => $qty,
            'harga_beli' => $this->sanitizeCurrencyInput($hargaBeli),
        ]);

        $validated = $request->validate([
            'jenis_pengeluaran' => 'required|in:' . implode(',', [
                Pengeluaran::JENIS_PEMBELIAN_APAR,
                Pengeluaran::JENIS_PEMBELIAN_REFILL,
                Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            ]),
            'produk_id' => 'nullable|required_if:jenis_pengeluaran,' . Pengeluaran::JENIS_PEMBELIAN_APAR . '|exists:produks,id',
            'jenis_refill_id' => 'nullable|required_if:jenis_pengeluaran,' . Pengeluaran::JENIS_PEMBELIAN_REFILL . '|exists:jenis_refills,id',
            'peralatan_id' => 'nullable|required_if:jenis_pengeluaran,' . Pengeluaran::JENIS_PEMBELIAN_PERALATAN . '|exists:peralatans,id',
            'qty' => 'required|numeric|min:0.01',
            'harga_beli' => 'nullable|numeric|min:0.01',
            'keterangan' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
        ]);

        if (
            in_array($validated['jenis_pengeluaran'], [
                Pengeluaran::JENIS_PEMBELIAN_APAR,
                Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            ], true)
            && floor((float) $validated['qty']) !== (float) $validated['qty']
        ) {
            return back()->withInput()->withErrors([
                'qty' => 'Qty untuk pembelian APAR atau peralatan harus berupa bilangan bulat.',
            ]);
        }

        $pengeluaranData = match ($validated['jenis_pengeluaran']) {
            Pengeluaran::JENIS_PEMBELIAN_APAR => $this->resolveAparPurchaseData($validated),
            Pengeluaran::JENIS_PEMBELIAN_REFILL => $this->resolveRefillPurchaseData($validated),
            Pengeluaran::JENIS_PEMBELIAN_PERALATAN => $this->resolvePeralatanPurchaseData($validated),
            default => throw ValidationException::withMessages([
                'jenis_pengeluaran' => 'Jenis pembelian tidak valid.',
            ]),
        };

        DB::transaction(function () use ($inventoryService, $pengeluaranData) {
            $pengeluaran = Pengeluaran::create([
                'kategori' => $pengeluaranData['kategori'],
                'jenis_pengeluaran' => $pengeluaranData['jenis_pengeluaran'],
                'produk_id' => $pengeluaranData['produk_id'],
                'jenis_refill_id' => $pengeluaranData['jenis_refill_id'],
                'peralatan_id' => $pengeluaranData['peralatan_id'],
                'nama_item' => $pengeluaranData['nama_item'],
                'qty' => $pengeluaranData['qty'],
                'satuan' => $pengeluaranData['satuan'],
                'harga_beli' => $pengeluaranData['harga_beli'],
                'total' => $pengeluaranData['total'],
                'nominal' => $pengeluaranData['total'],
                'keterangan' => $pengeluaranData['keterangan'],
                'tanggal' => $pengeluaranData['tanggal'],
            ]);

            $inventoryService->applyPurchaseExpense($pengeluaran);
        });

        $message = match ($pengeluaranData['jenis_pengeluaran']) {
            Pengeluaran::JENIS_PEMBELIAN_APAR => 'Data pengeluaran pembelian APAR berhasil disimpan dan stok otomatis diperbarui.',
            Pengeluaran::JENIS_PEMBELIAN_REFILL => 'Data pengeluaran pembelian refil berhasil disimpan dan stok otomatis diperbarui.',
            Pengeluaran::JENIS_PEMBELIAN_PERALATAN => 'Data pengeluaran pembelian peralatan berhasil disimpan dan stok otomatis diperbarui.',
            default => 'Data pengeluaran berhasil disimpan.',
        };

        return redirect()->route('admin.pengeluaran.index')->with('success', $message);
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $request->merge([
            'harga_beli' => $this->sanitizeCurrencyInput($request->input('harga_beli')),
        ]);

        if ($pengeluaran->isStockAffecting()) {
            return redirect()
                ->route('admin.pengeluaran.index')
                ->with('error', 'Data pengeluaran pembelian yang sudah memperbarui stok tidak dapat diedit.');
        }

        $validated = $request->validate([
            'keterangan' => 'nullable|string|max:1000',
            'tanggal' => 'required|date',
            'harga_beli' => 'required|numeric|min:0',
        ]);

        $pengeluaran->update([
            'keterangan' => $validated['keterangan'] ?? null,
            'tanggal' => $validated['tanggal'],
            'harga_beli' => $validated['harga_beli'],
            'total' => (float) ($pengeluaran->qty ?? 1) * (float) $validated['harga_beli'],
            'nominal' => (float) ($pengeluaran->qty ?? 1) * (float) $validated['harga_beli'],
        ]);

        return redirect()
            ->route('admin.pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        if ($pengeluaran->isStockAffecting()) {
            return redirect()
                ->route('admin.pengeluaran.index')
                ->with('error', 'Data pengeluaran pembelian yang sudah memperbarui stok tidak dapat dihapus.');
        }

        $pengeluaran->delete();

        return redirect()
            ->route('admin.pengeluaran.index')
            ->with('success', 'Data pengeluaran berhasil dihapus.');
    }

    private function resolveAparPurchaseData(array $validated): array
    {
        $produk = Produk::with('jenisApar')->findOrFail($validated['produk_id']);
        $qty = (int) $validated['qty'];
        $hargaBeli = round((float) ($validated['harga_beli'] ?? 0), 2);

        if ($hargaBeli <= 0) {
            throw ValidationException::withMessages([
                'harga_beli' => 'Harga beli per unit APAR wajib diisi dan harus lebih dari 0.',
            ]);
        }

        return [
            'kategori' => 'lainnya',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_APAR,
            'produk_id' => $produk->id,
            'jenis_refill_id' => null,
            'peralatan_id' => null,
            'nama_item' => $produk->nama,
            'qty' => $qty,
            'satuan' => 'Unit',
            'harga_beli' => $hargaBeli,
            'total' => round($qty * $hargaBeli, 2),
            'keterangan' => $validated['keterangan'] ?? null,
            'tanggal' => $validated['tanggal'],
        ];
    }

    private function resolveRefillPurchaseData(array $validated): array
    {
        $jenisRefill = JenisRefill::findOrFail($validated['jenis_refill_id']);
        $hargaStandar = (float) ($jenisRefill->harga ?? 0);

        if ($hargaStandar <= 0) {
            throw ValidationException::withMessages([
                'jenis_refill_id' => 'Harga standar untuk jenis refil "' . $jenisRefill->nama . '" belum tersedia. Isi harga standar terlebih dahulu di Master Data Jenis Refil.',
            ]);
        }

        $qty = round((float) $validated['qty'], 2);

        return [
            'kategori' => 'refill',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_REFILL,
            'produk_id' => null,
            'jenis_refill_id' => $jenisRefill->id,
            'peralatan_id' => null,
            'nama_item' => $jenisRefill->nama,
            'qty' => $qty,
            'satuan' => $jenisRefill->satuan_label,
            'harga_beli' => $hargaStandar,
            'total' => round($qty * $hargaStandar, 2),
            'keterangan' => $validated['keterangan'] ?? null,
            'tanggal' => $validated['tanggal'],
        ];
    }

    private function resolvePeralatanPurchaseData(array $validated): array
    {
        $peralatan = Peralatan::findOrFail($validated['peralatan_id']);
        $hargaStandar = (float) ($peralatan->harga_standar ?? 0);

        if ($hargaStandar <= 0) {
            throw ValidationException::withMessages([
                'peralatan_id' => 'Harga standar untuk peralatan "' . $peralatan->nama . '" belum tersedia. Isi harga standar terlebih dahulu di Master Data Peralatan.',
            ]);
        }

        $qty = (int) $validated['qty'];

        return [
            'kategori' => 'peralatan',
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'produk_id' => null,
            'jenis_refill_id' => null,
            'peralatan_id' => $peralatan->id,
            'nama_item' => $peralatan->nama,
            'qty' => $qty,
            'satuan' => 'Unit',
            'harga_beli' => $hargaStandar,
            'total' => round($qty * $hargaStandar, 2),
            'keterangan' => $validated['keterangan'] ?? null,
            'tanggal' => $validated['tanggal'],
        ];
    }

    private function sanitizeCurrencyInput(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (is_numeric($value)) {
            return $value;
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', (string) $value) ?? '';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return $normalized === '' ? null : $normalized;
    }
}
