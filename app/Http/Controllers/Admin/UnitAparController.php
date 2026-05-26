<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\UnitApar;
use Carbon\Carbon;
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

    protected function normalizeDateInput(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        foreach (['Y-m-d', 'm/d/Y', 'd/m/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return $raw;
        }
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
        $produkId = $request->input('produk_id') ?: $request->input('model_produk_id');
        $noSeri = $request->input('no_seri') ?: $request->input('kode_unit') ?: $request->input('nomor_seri_unit');
        $tglBeli = $request->input('tgl_beli') ?: $request->input('tanggal_beli');
        $kondisiAwal = $request->input('kondisi_awal') ?: $request->input('kondisi_awal_unit');

        $request->merge([
            'produk_id' => $produkId,
            'no_seri' => trim((string) $noSeri) ?: null,
            'catatan_unit' => trim((string) $request->input('catatan_unit')) ?: null,
            'tgl_beli' => $this->normalizeDateInput($tglBeli),
            'tgl_produksi' => $this->normalizeDateInput($request->input('tgl_produksi') ?: $tglBeli),
            'kondisi_awal' => $kondisiAwal,
        ]);

        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'produk_id'    => 'required|exists:produks,id',
            'no_seri'      => 'nullable|string|max:255|unique:unit_apars,no_seri',
            'tgl_beli'     => 'required|date',
            'tgl_produksi' => 'nullable|date',
            'lokasi_unit'  => 'nullable|string|max:255',
            'kondisi_awal' => 'required|in:layak,perlu_servis,tidak_aktif',
            'catatan_unit' => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_id.exists' => 'Pelanggan yang dipilih tidak ditemukan.',
            'produk_id.required' => 'Model produk wajib dipilih.',
            'produk_id.exists' => 'Model produk yang dipilih tidak ditemukan.',
            'tgl_beli.required' => 'Tanggal beli wajib diisi.',
            'tgl_beli.date' => 'Format tanggal beli tidak valid.',
            'tgl_produksi.date' => 'Format tanggal produksi tidak valid.',
            'kondisi_awal.required' => 'Kondisi awal wajib dipilih.',
            'kondisi_awal.in' => 'Kondisi awal unit tidak valid.',
            'no_seri.unique' => 'Nomor seri unit sudah digunakan.',
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
        $pelanggan = Pelanggan::findOrFail($request->pelanggan_id);
        $effectiveDate = (string) ($data['tgl_produksi'] ?: $data['tgl_beli']);

        $data['ukuran']      = $produk->kapasitas ?? '-';
        $data['bahan']       = $produk->jenisApar?->nama ?? '-';
        $data['kondisi_awal'] = $request->input('kondisi_awal', 'layak');
        $data['no_seri']     = $data['no_seri'] ?: $this->generateUnitSerial($pelanggan, $produk, $effectiveDate);
        $data['tgl_expired'] = UnitApar::calculateExpiry($effectiveDate, $data['ukuran'], $data['bahan']);

        UnitApar::create($data);

        return redirect()->route('admin.unit-apar.index')->with('success', 'Unit APAR berhasil diregistrasikan.');
    }

    public function edit(UnitApar $unitApar)
    {
        $pelanggans = Pelanggan::all();
        $produks    = Produk::all();

        return view('admin.unit-apar.edit', compact('unitApar', 'pelanggans', 'produks'));
    }

    public function update(Request $request, UnitApar $unitApar)
    {
        $produkId = $request->input('produk_id') ?: $request->input('model_produk_id');
        $noSeri = $request->input('no_seri') ?: $request->input('kode_unit') ?: $request->input('nomor_seri_unit');
        $tglBeli = $request->input('tgl_beli') ?: $request->input('tanggal_beli');
        $kondisiAwal = $request->input('kondisi_awal') ?: $request->input('kondisi_awal_unit');

        $request->merge([
            'produk_id' => $produkId,
            'no_seri' => trim((string) $noSeri) ?: null,
            'catatan_unit' => trim((string) $request->input('catatan_unit')) ?: null,
            'tgl_beli' => $this->normalizeDateInput($tglBeli),
            'tgl_produksi' => $this->normalizeDateInput($request->input('tgl_produksi') ?: $tglBeli),
            'kondisi_awal' => $kondisiAwal,
        ]);

        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggans,id',
            'produk_id'    => 'required|exists:produks,id',
            'no_seri'      => 'nullable|unique:unit_apars,no_seri,'.$unitApar->id,
            'tgl_beli'     => 'required|date',
            'tgl_produksi' => 'nullable|date',
            'lokasi_unit'  => 'nullable|string|max:255',
            'kondisi_awal' => 'required|in:layak,perlu_servis,tidak_aktif',
            'catatan_unit' => 'nullable|string|max:1000',
        ], [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'produk_id.required' => 'Model produk wajib dipilih.',
            'tgl_beli.required' => 'Tanggal beli wajib diisi.',
            'tgl_beli.date' => 'Format tanggal beli tidak valid.',
            'tgl_produksi.date' => 'Format tanggal produksi tidak valid.',
            'kondisi_awal.required' => 'Kondisi awal wajib dipilih.',
            'no_seri.unique' => 'Nomor seri unit sudah digunakan.',
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
        $pelanggan = Pelanggan::findOrFail($request->pelanggan_id);
        $effectiveDate = (string) ($data['tgl_produksi'] ?: $data['tgl_beli'] ?: optional($unitApar->tgl_produksi ?? $unitApar->tgl_beli)->format('Y-m-d'));

        $data['ukuran']      = $produk->kapasitas ?? '-';
        $data['bahan']       = $produk->jenisApar?->nama ?? '-';
        $data['kondisi_awal'] = $request->input('kondisi_awal', $unitApar->kondisi_awal ?: 'layak');
        $data['no_seri']     = trim((string) ($data['no_seri'] ?? '')) !== ''
            ? $data['no_seri']
            : ($unitApar->no_seri ?: $this->generateUnitSerial($pelanggan, $produk, $effectiveDate));
        $data['tgl_expired'] = UnitApar::calculateExpiry($effectiveDate, $data['ukuran'], $data['bahan']);

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
