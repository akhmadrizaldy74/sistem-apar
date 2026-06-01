<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    private function combineAddress(string $mapsAddress, string $detailAddress): string
    {
        $maps = trim($mapsAddress);
        $detail = trim($detailAddress);

        if ($maps === '' && $detail === '') {
            return '-';
        }

        if ($maps === '') {
            return $detail;
        }

        if ($detail === '') {
            return $maps;
        }

        return $maps . ' | Detail: ' . $detail;
    }

    public function index(Request $request)
    {
        $query = Pelanggan::withCount(['pesanan' => function ($query) {
            $query->whereIn('status', ['diproses', 'selesai', 'selesai final', 'ditugaskan ke teknisi', 'dikerjakan teknisi', 'selesai oleh teknisi', 'dikonfirmasi admin']);
        }]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('no_wa', 'like', '%' . $request->search . '%')
                    ->orWhere('perusahaan', 'like', '%' . $request->search . '%');
            });
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();

        return view('admin.pelanggan.index', compact('pelanggans'));
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $normalizedNoWa = preg_replace('/\D+/', '', (string) $request->no_wa);
        if (str_starts_with($normalizedNoWa, '62')) {
            $normalizedNoWa = '0' . substr($normalizedNoWa, 2);
        } elseif (str_starts_with($normalizedNoWa, '8')) {
            $normalizedNoWa = '0' . $normalizedNoWa;
        }

        $request->merge([
            'no_wa' => $normalizedNoWa,
        ]);

        $request->validate([
            'nama'               => 'required|string|max:255',
            'no_wa'              => 'required|string|max:20',
            'alamat'             => 'nullable|string|max:1000',
            'alamat_maps'        => 'required|string|max:1000',
            'alamat_detail'      => 'required|string|max:1000',
            'alamat_lat'         => 'required|numeric|between:-90,90',
            'alamat_lng'         => 'required|numeric|between:-180,180',
            'alamat_provinsi'    => 'nullable|string|max:255',
            'alamat_kota'        => 'nullable|string|max:255',
            'alamat_kecamatan'   => 'nullable|string|max:255',
            'alamat_kode_pos'    => 'nullable|string|max:20',
            'sumber_data'        => 'nullable|in:manual,whatsapp,telepon,arsip_lama',
            'kategori_pelanggan' => 'nullable|in:lama,baru_manual',
            'catatan_internal'   => 'nullable|string|max:1000',
        ], [
            'alamat_maps.required' => 'Alamat via OpenStreetMap wajib dipilih dari saran alamat.',
            'alamat_detail.required' => 'Detail alamat wajib diisi agar tim operasional lebih mudah menemukan lokasi.',
            'alamat_lat.required' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.',
            'alamat_lng.required' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.',
        ]);

        $kategoriPelanggan = (string) $request->input('kategori_pelanggan', 'lama');
        $alamat = $this->combineAddress((string) $request->alamat_maps, (string) $request->alamat_detail);

        Pelanggan::updateOrCreate(
            ['no_wa' => $request->no_wa],
            [
                'nama'               => $request->nama,
                'alamat'             => $alamat,
                'alamat_maps'        => $request->alamat_maps,
                'alamat_detail'      => $request->alamat_detail,
                'alamat_lat'         => (float) $request->alamat_lat,
                'alamat_lng'         => (float) $request->alamat_lng,
                'alamat_provinsi'    => $request->alamat_provinsi,
                'alamat_kota'        => $request->alamat_kota,
                'alamat_kecamatan'   => $request->alamat_kecamatan,
                'alamat_kode_pos'    => $request->alamat_kode_pos,
                'status'             => 'tetap',
                'sumber_data'        => $request->input('sumber_data', 'manual'),
                'kategori_pelanggan' => $kategoriPelanggan,
                'catatan_internal'   => $request->input('catatan_internal'),
            ]
        );

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        $pelanggan->load(['pesanan' => function ($query) {
            $query->orderByDesc('tanggal')->orderByDesc('created_at');
        }]);

        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $normalizedNoWa = preg_replace('/\D+/', '', (string) $request->no_wa);
        if (str_starts_with($normalizedNoWa, '62')) {
            $normalizedNoWa = '0' . substr($normalizedNoWa, 2);
        } elseif (str_starts_with($normalizedNoWa, '8')) {
            $normalizedNoWa = '0' . $normalizedNoWa;
        }

        $request->validate([
            'nama'               => 'required|string|max:255',
            'no_wa'              => 'required|unique:pelanggans,no_wa,' . $pelanggan->id,
            'kategori_pelanggan' => 'nullable|in:lama,baru_manual',
            'alamat_maps'       => 'nullable|string|max:1000',
            'alamat_detail'     => 'nullable|string|max:1000',
            'alamat_lat'        => 'nullable|numeric|between:-90,90',
            'alamat_lng'        => 'nullable|numeric|between:-180,180',
            'alamat_provinsi'   => 'nullable|string|max:255',
            'alamat_kota'       => 'nullable|string|max:255',
            'alamat_kecamatan'  => 'nullable|string|max:255',
            'alamat_kode_pos'   => 'nullable|string|max:20',
        ]);

        $alamatMaps = trim((string) $request->alamat_maps);
        $alamatDetail = trim((string) $request->alamat_detail);

        $updateData = [
            'nama'               => $request->nama,
            'no_wa'              => $normalizedNoWa,
            'status'             => 'tetap',
            'kategori_pelanggan' => $request->input('kategori_pelanggan', $pelanggan->kategori_pelanggan ?: 'lama'),
            'alamat_maps'        => $alamatMaps ?: null,
            'alamat_detail'      => $alamatDetail ?: null,
            'alamat_provinsi'   => trim((string) $request->alamat_provinsi) ?: null,
            'alamat_kota'        => trim((string) $request->alamat_kota) ?: null,
            'alamat_kecamatan'  => trim((string) $request->alamat_kecamatan) ?: null,
            'alamat_kode_pos'   => trim((string) $request->alamat_kode_pos) ?: null,
        ];

        if (filled($alamatMaps) && filled($alamatDetail)) {
            $updateData['alamat'] = $alamatMaps . ' | Detail: ' . $alamatDetail;
        } elseif (filled($alamatMaps)) {
            $updateData['alamat'] = $alamatMaps;
        } elseif (filled($alamatDetail)) {
            $updateData['alamat'] = $alamatDetail;
        }

        if (filled($request->alamat_lat)) {
            $updateData['alamat_lat'] = (float) $request->alamat_lat;
        }
        if (filled($request->alamat_lng)) {
            $updateData['alamat_lng'] = (float) $request->alamat_lng;
        }

        $pelanggan->update($updateData);

        return redirect()->route('admin.pelanggan.edit', $pelanggan)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
