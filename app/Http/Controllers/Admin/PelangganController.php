<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    private function combineAddress(string $mainAddress, string $detailAddress): ?string
    {
        $main = trim($mainAddress);
        $detail = trim($detailAddress);

        if ($main === '' && $detail === '') {
            return null;
        }

        if ($main === '') {
            return $detail;
        }

        if ($detail === '') {
            return $main;
        }

        return $main.' | Detail: '.$detail;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $summaryQuery = $this->pelangganDirectoryQuery();
        $query = $this->pelangganDirectoryQuery()
            ->with('user')
            ->withCount('productOrders');

        if ($search !== '') {
            $this->applyDirectorySearch($query, $search);
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'totalPelanggan' => (clone $summaryQuery)->count(),
            'pelangganAktif' => (clone $summaryQuery)
                ->whereHas('productOrders')
                ->count(),
            'totalTransaksiPelanggan' => Pesanan::query()
                ->where('tipe', 'produk')
                ->whereNotIn('status', Pelanggan::excludedPurchaseStatuses())
                ->whereIn('pelanggan_id', (clone $summaryQuery)->select('pelanggans.id'))
                ->count(),
        ];

        return view('admin.pelanggan.index', compact('pelanggans', 'summary', 'search'));
    }

    public function show(Pelanggan $pelanggan)
    {
        abort_unless($this->isDirectoryPelanggan($pelanggan), 404);

        $pelanggan->load('user');

        $riwayatPembelian = $pelanggan->productOrders()
            ->with(['details.produk'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.pelanggan.show', compact('pelanggan', 'riwayatPembelian'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        abort_unless($this->isDirectoryPelanggan($pelanggan), 404);

        $pelanggan->load('user');

        return view('admin.pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        abort_unless($this->isDirectoryPelanggan($pelanggan), 404);

        $request->validate([
            'alamat_maps' => 'required|string|max:1000',
            'alamat_detail' => 'nullable|string|max:1000',
            'alamat_lat' => 'nullable|numeric|between:-90,90',
            'alamat_lng' => 'nullable|numeric|between:-180,180',
            'alamat_provinsi' => 'nullable|string|max:255',
            'alamat_kota' => 'nullable|string|max:255',
            'alamat_kecamatan' => 'nullable|string|max:255',
            'alamat_kode_pos' => 'nullable|string|max:20',
            'rajaongkir_destination_id' => 'nullable|string|max:50',
            'rajaongkir_destination_label' => 'nullable|string|max:255',
        ], [
            'alamat_maps.required' => 'Alamat pelanggan wajib diisi.',
        ]);

        $alamatMaps = trim((string) $request->input('alamat_maps'));
        $alamatDetail = trim((string) $request->input('alamat_detail'));

        $pelanggan->update([
            'alamat' => $this->combineAddress($alamatMaps, $alamatDetail),
            'alamat_maps' => $alamatMaps,
            'alamat_detail' => $alamatDetail !== '' ? $alamatDetail : null,
            'alamat_lat' => filled($request->input('alamat_lat')) ? (float) $request->input('alamat_lat') : null,
            'alamat_lng' => filled($request->input('alamat_lng')) ? (float) $request->input('alamat_lng') : null,
            'alamat_provinsi' => trim((string) $request->input('alamat_provinsi')) ?: null,
            'alamat_kota' => trim((string) $request->input('alamat_kota')) ?: null,
            'alamat_kecamatan' => trim((string) $request->input('alamat_kecamatan')) ?: null,
            'alamat_kode_pos' => trim((string) $request->input('alamat_kode_pos')) ?: null,
            'rajaongkir_destination_id' => trim((string) $request->input('rajaongkir_destination_id')) ?: null,
            'rajaongkir_destination_label' => trim((string) $request->input('rajaongkir_destination_label')) ?: null,
            'status' => 'tetap',
        ]);

        return redirect()->route('admin.pelanggan.edit', $pelanggan)
            ->with('success', 'Alamat pelanggan berhasil diperbarui.');
    }

    private function pelangganDirectoryQuery(): Builder
    {
        return Pelanggan::query()->visibleInDirectory();
    }

    private function applyDirectorySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search) {
            $builder->where('nama', 'like', '%'.$search.'%')
                ->orWhere('no_wa', 'like', '%'.$search.'%')
                ->orWhere('alamat', 'like', '%'.$search.'%')
                ->orWhere('alamat_maps', 'like', '%'.$search.'%')
                ->orWhere('alamat_detail', 'like', '%'.$search.'%')
                ->orWhere('alamat_provinsi', 'like', '%'.$search.'%')
                ->orWhere('alamat_kota', 'like', '%'.$search.'%')
                ->orWhere('alamat_kecamatan', 'like', '%'.$search.'%')
                ->orWhere('alamat_kode_pos', 'like', '%'.$search.'%')
                ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
        });
    }

    private function isDirectoryPelanggan(Pelanggan $pelanggan): bool
    {
        $pelanggan->loadMissing('user');

        return (bool) $pelanggan->user && $pelanggan->user->role === 'pelanggan';
    }
}
