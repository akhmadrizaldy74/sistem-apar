<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\UnitApar;
use Illuminate\Http\Request;

class UnitAparController extends Controller
{
    protected function buildJenisAparCode(?string $jenisApar): string
    {
        $jenis = strtolower((string) $jenisApar);

        if (str_contains($jenis, 'co2') || str_contains($jenis, 'carbon')) {
            return 'CO2';
        }
        if (str_contains($jenis, 'powder')) {
            return 'PWD';
        }
        if (str_contains($jenis, 'foam')) {
            return 'FOM';
        }

        $fallback = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $jenisApar) ?: 'APR');
        return substr($fallback, 0, 3);
    }

    protected function buildUkuranCode(?string $kapasitas): string
    {
        $ukuran = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $kapasitas) ?: '');
        return $ukuran !== '' ? $ukuran : 'UNK';
    }

    protected function generateUnitSerial(Pelanggan $pelanggan, Produk $produk, string $tanggalBeli): string
    {
        return UnitApar::generateSerialNumber($pelanggan, $tanggalBeli);
    }

    public function index()
    {
        $units = UnitApar::with(['pelanggan', 'produk'])->latest('tgl_beli')->get();
        $pelanggans = Pelanggan::all();
        $produks = Produk::all();

        return view('admin.unit-apar.index', compact('units', 'pelanggans', 'produks'));
    }

    public function show(UnitApar $unitApar)
    {
        $unit = $unitApar->load(['pelanggan', 'produk']);

        $services = $unit->services()
            ->orderByDesc('tgl_service')
            ->get();

        $refills = $unit->refills()
            ->with('jenisRefill')
            ->orderByDesc('tgl_refill')
            ->get();

        return view('admin.unit-apar.show', compact('unit', 'services', 'refills'));
    }

    public function create()
    {
        return redirect()->route('admin.unit-apar.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'no_seri' => trim((string) $request->input('no_seri')) ?: null,
            'catatan_unit' => trim((string) $request->input('catatan_unit')) ?: null,
        ]);

        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'produk_id'    => 'required|exists:produks,id',
            'no_seri'      => 'nullable|string|max:255|unique:unit_apars,no_seri',
            'tgl_produksi' => 'required|date',
            'tgl_beli'     => 'required|date',
            'lokasi_unit'  => 'nullable|string|max:255',
            'kondisi_awal' => 'nullable|in:layak,perlu_servis,tidak_aktif',
            'catatan_unit' => 'nullable|string|max:1000',
        ]);

        $data = $request->only([
            'pelanggan_id',
            'produk_id',
            'no_seri',
            'tgl_beli',
            'tgl_produksi',
            'lokasi_unit',
            'kondisi_awal',
            'catatan_unit',
        ]);

        $produk    = Produk::with('jenisApar')->findOrFail($request->produk_id);
        
        if ($produk->stok_tersedia <= 0) {
            return back()->withInput()->with('error', 'Stok produk ini habis atau tidak mencukupi!');
        }

        $pelanggan = Pelanggan::findOrFail($request->pelanggan_id);

        $data['ukuran']      = $produk->kapasitas ?? '-';
        $data['bahan']       = $produk->jenisApar?->nama ?? '-';
        $data['kondisi_awal'] = $request->input('kondisi_awal', 'layak');
        $data['no_seri']     = $data['no_seri'] ?: $this->generateUnitSerial($pelanggan, $produk, (string) $request->tgl_produksi);
        $data['tgl_expired'] = UnitApar::calculateExpiry($request->tgl_produksi, $data['ukuran'], $data['bahan']);

        // FIFO: Ambil batch yang tidak expired & memiliki sisa stok
        $batch = \App\Models\StokBatch::where('produk_id', $produk->id)
            ->where('sisa_qty', '>', 0)
            ->where('tgl_expired', '>=', now()->toDateString())
            ->orderBy('tgl_expired', 'asc')
            ->first();

        if (!$batch) {
            return back()->withInput()->with('error', 'Tidak ada stok batch non-expired yang siap dipakai untuk produk ini.');
        }

        $batch->decrement('sisa_qty', 1);

        $produk->decrement('stok', 1);

        UnitApar::create($data);

        return redirect()->route('admin.unit-apar.index')->with('success', 'Unit APAR berhasil ditambahkan.');
    }

    public function edit(UnitApar $unitApar)
    {
        $pelanggans = Pelanggan::all();
        $produks    = Produk::all();

        return view('admin.unit-apar.edit', compact('unitApar', 'pelanggans', 'produks'));
    }

    public function update(Request $request, UnitApar $unitApar)
    {
        $request->merge([
            'catatan_unit' => trim((string) $request->input('catatan_unit')) ?: null,
        ]);

        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'produk_id'    => 'required|exists:produks,id',
            'no_seri'      => 'required|unique:unit_apars,no_seri,'.$unitApar->id,
            'tgl_produksi' => 'required|date',
            'tgl_beli'     => 'required|date',
            'lokasi_unit'  => 'nullable|string|max:255',
            'kondisi_awal' => 'nullable|in:layak,perlu_servis,tidak_aktif',
            'catatan_unit' => 'nullable|string|max:1000',
        ]);

        $data = $request->only([
            'pelanggan_id',
            'produk_id',
            'no_seri',
            'tgl_beli',
            'tgl_produksi',
            'lokasi_unit',
            'kondisi_awal',
            'catatan_unit',
        ]);

        $produk = Produk::with('jenisApar')->findOrFail($request->produk_id);

        if ($unitApar->produk_id != $request->produk_id) {
            if ($produk->stok_tersedia <= 0) {
                return back()->withInput()->with('error', 'Stok produk baru tidak mencukupi!');
            }
            
            // Refund stock to old product
            if ($unitApar->produk) {
                $unitApar->produk->increment('stok', 1);
            }
            
            // Deduct stock from new product (FIFO)
            $batch = \App\Models\StokBatch::where('produk_id', $produk->id)
                ->where('sisa_qty', '>', 0)
                ->where('tgl_expired', '>=', now()->toDateString())
                ->orderBy('tgl_expired', 'asc')
                ->first();

            if (!$batch) {
                return back()->withInput()->with('error', 'Tidak ada stok batch non-expired untuk produk baru.');
            }

            $batch->decrement('sisa_qty', 1);
            $produk->decrement('stok', 1);
        }

        $data['ukuran']      = $produk->kapasitas ?? '-';
        $data['bahan']       = $produk->jenisApar?->nama ?? '-';
        $data['kondisi_awal'] = $request->input('kondisi_awal', $unitApar->kondisi_awal ?: 'layak');
        $data['tgl_expired'] = UnitApar::calculateExpiry($request->tgl_produksi, $data['ukuran'], $data['bahan']);

        $unitApar->update($data);

        return redirect()->route('admin.unit-apar.index')->with('success', 'Unit APAR berhasil diperbarui.');
    }

    public function destroy(UnitApar $unitApar)
    {
        // Refund general stock
        if ($unitApar->produk) {
            $unitApar->produk->increment('stok', 1);
        }
        
        $unitApar->delete();

        return redirect()->route('admin.unit-apar.index')->with('success', 'Unit APAR berhasil dihapus.');
    }
}
