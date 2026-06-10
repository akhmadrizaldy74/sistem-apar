<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

        return $maps.' | Detail: '.$detail;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $summaryQuery = $this->pelangganDirectoryQuery();
        $query = $this->pelangganDirectoryQuery()
            ->with('user')
            ->withCount([
                'pesanan as product_orders_count' => fn (Builder $query) => $query->where('tipe', 'produk'),
            ]);

        if ($search !== '') {
            $this->applyDirectorySearch($query, $search);
        }

        $pelanggans = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'totalPelanggan' => (clone $summaryQuery)->count(),
            'pelangganAktif' => (clone $summaryQuery)
                ->whereHas('pesanan', fn (Builder $query) => $query->where('tipe', 'produk'))
                ->count(),
            'totalTransaksiPelanggan' => Pesanan::query()
                ->where('tipe', 'produk')
                ->whereIn('pelanggan_id', $this->pelangganDirectoryQuery()->select('pelanggans.id'))
                ->count(),
        ];

        return view('admin.pelanggan.index', compact('pelanggans', 'summary', 'search'));
    }

    public function show(Pelanggan $pelanggan)
    {
        abort_unless($this->isDirectoryPelanggan($pelanggan), 404);

        $pelanggan->load('user');

        $riwayatPembelian = $pelanggan->pesanan()
            ->where('tipe', 'produk')
            ->with(['details.produk'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.pelanggan.show', compact('pelanggan', 'riwayatPembelian'));
    }

    public function create()
    {
        return view('admin.pelanggan.create');
    }

    public function store(Request $request)
    {
        $normalizedNoWa = PhoneNumber::normalize((string) $request->no_wa) ?? '';
        $email = trim((string) $request->input('email')) ?: null;
        $linkedUser = $this->resolvePelangganUserByIdentity(null, $normalizedNoWa, $email);

        $request->merge([
            'no_wa' => $normalizedNoWa,
            'email' => $email,
        ]);

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_wa' => 'required|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($linkedUser?->id)],
            'alamat' => 'nullable|string|max:1000',
            'alamat_maps' => 'required|string|max:1000',
            'alamat_detail' => 'required|string|max:1000',
            'alamat_lat' => 'required|numeric|between:-90,90',
            'alamat_lng' => 'required|numeric|between:-180,180',
            'alamat_provinsi' => 'nullable|string|max:255',
            'alamat_kota' => 'nullable|string|max:255',
            'alamat_kecamatan' => 'nullable|string|max:255',
            'alamat_kode_pos' => 'nullable|string|max:20',
            'sumber_data' => 'nullable|in:manual,whatsapp,telepon,arsip_lama',
            'kategori_pelanggan' => 'nullable|in:lama,baru_manual',
            'catatan_internal' => 'nullable|string|max:1000',
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
            'alamat_maps.required' => 'Alamat via OpenStreetMap wajib dipilih dari saran alamat.',
            'alamat_detail.required' => 'Detail alamat wajib diisi agar tim operasional lebih mudah menemukan lokasi.',
            'alamat_lat.required' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.',
            'alamat_lng.required' => 'Titik koordinat belum tersimpan. Pilih alamat dari saran OpenStreetMap.',
        ]);

        if ($this->phoneBelongsToDifferentUser($normalizedNoWa, $linkedUser)) {
            return back()->withInput()->withErrors([
                'no_wa' => 'Nomor WhatsApp ini sudah digunakan akun lain.',
            ]);
        }

        $kategoriPelanggan = (string) $request->input('kategori_pelanggan', 'lama');
        $alamat = $this->combineAddress((string) $request->alamat_maps, (string) $request->alamat_detail);
        $linkedUser = $this->ensurePelangganUser(
            pelanggan: null,
            name: (string) $request->nama,
            phone: (string) $request->no_wa,
            email: $email,
        );

        $pelanggan = Pelanggan::updateOrCreate(
            ['no_wa' => $request->no_wa],
            [
                'user_id' => $linkedUser?->id,
                'nama' => $request->nama,
                'alamat' => $alamat,
                'alamat_maps' => $request->alamat_maps,
                'alamat_detail' => $request->alamat_detail,
                'alamat_lat' => (float) $request->alamat_lat,
                'alamat_lng' => (float) $request->alamat_lng,
                'alamat_provinsi' => $request->alamat_provinsi,
                'alamat_kota' => $request->alamat_kota,
                'alamat_kecamatan' => $request->alamat_kecamatan,
                'alamat_kode_pos' => $request->alamat_kode_pos,
                'status' => 'tetap',
                'sumber_data' => $request->input('sumber_data', 'manual'),
                'kategori_pelanggan' => $kategoriPelanggan,
                'catatan_internal' => $request->input('catatan_internal'),
            ]
        );

        if ($linkedUser) {
            $pelanggan->user()->associate($linkedUser);
            $pelanggan->save();
        }

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
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

        $normalizedNoWa = PhoneNumber::normalize((string) $request->no_wa) ?? '';
        $email = trim((string) $request->input('email')) ?: null;
        $linkedUser = $this->resolvePelangganUserByIdentity($pelanggan, $normalizedNoWa, $email);

        $request->merge([
            'no_wa' => $normalizedNoWa,
            'email' => $email,
        ]);

        if ($this->phoneBelongsToDifferentUser($normalizedNoWa, $linkedUser)) {
            return back()->withInput()->withErrors([
                'no_wa' => 'Nomor WhatsApp ini sudah digunakan akun pelanggan lain.',
            ]);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'no_wa' => 'required|unique:pelanggans,no_wa,'.$pelanggan->id,
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($linkedUser?->id)],
            'kategori_pelanggan' => 'nullable|in:lama,baru_manual',
            'alamat_maps' => 'nullable|string|max:1000',
            'alamat_detail' => 'nullable|string|max:1000',
            'alamat_lat' => 'nullable|numeric|between:-90,90',
            'alamat_lng' => 'nullable|numeric|between:-180,180',
            'alamat_provinsi' => 'nullable|string|max:255',
            'alamat_kota' => 'nullable|string|max:255',
            'alamat_kecamatan' => 'nullable|string|max:255',
            'alamat_kode_pos' => 'nullable|string|max:20',
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $alamatMaps = trim((string) $request->alamat_maps);
        $alamatDetail = trim((string) $request->alamat_detail);

        $updateData = [
            'nama' => $request->nama,
            'no_wa' => $normalizedNoWa,
            'status' => 'tetap',
            'kategori_pelanggan' => $request->input('kategori_pelanggan', $pelanggan->kategori_pelanggan ?: 'lama'),
            'alamat_maps' => $alamatMaps ?: null,
            'alamat_detail' => $alamatDetail ?: null,
            'alamat_provinsi' => trim((string) $request->alamat_provinsi) ?: null,
            'alamat_kota' => trim((string) $request->alamat_kota) ?: null,
            'alamat_kecamatan' => trim((string) $request->alamat_kecamatan) ?: null,
            'alamat_kode_pos' => trim((string) $request->alamat_kode_pos) ?: null,
        ];

        if (filled($alamatMaps) && filled($alamatDetail)) {
            $updateData['alamat'] = $alamatMaps.' | Detail: '.$alamatDetail;
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

        $linkedUser = $this->ensurePelangganUser(
            pelanggan: $pelanggan,
            name: (string) $request->nama,
            phone: $normalizedNoWa,
            email: $email,
        );

        if ($linkedUser) {
            if ((int) $pelanggan->user_id !== (int) $linkedUser->id) {
                $pelanggan->user()->associate($linkedUser);
                $pelanggan->save();
            }
        }

        return redirect()->route('admin.pelanggan.edit', $pelanggan)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        abort_unless($this->isDirectoryPelanggan($pelanggan), 404);

        $pelanggan->delete();

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function pelangganDirectoryQuery(): Builder
    {
        return Pelanggan::query()
            ->where(function (Builder $query) {
                $query->whereNull('user_id')
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('role', 'pelanggan'));
            });
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
                ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('email', 'like', '%'.$search.'%'));
        });
    }

    private function isDirectoryPelanggan(Pelanggan $pelanggan): bool
    {
        $pelanggan->loadMissing('user');

        return ! $pelanggan->user || $pelanggan->user->role === 'pelanggan';
    }

    private function resolvePelangganUserByIdentity(?Pelanggan $pelanggan, ?string $phone, ?string $email): ?User
    {
        $pelanggan?->loadMissing('user');

        if ($pelanggan?->user) {
            return $pelanggan->user;
        }

        return $this->findPelangganUserByPhone($phone)
            ?: $this->findPelangganUserByEmail($email);
    }

    private function findPelangganUserByPhone(?string $phone): ?User
    {
        if (! $phone) {
            return null;
        }

        return User::query()
            ->where('role', 'pelanggan')
            ->where('no_telpon', $phone)
            ->first();
    }

    private function findPelangganUserByEmail(?string $email): ?User
    {
        if (! $email) {
            return null;
        }

        return User::query()
            ->where('role', 'pelanggan')
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();
    }

    private function phoneBelongsToDifferentUser(?string $phone, ?User $ignoreUser = null): bool
    {
        if (! $phone) {
            return false;
        }

        return User::query()
            ->where('no_telpon', $phone)
            ->when($ignoreUser, fn ($query) => $query->whereKeyNot($ignoreUser->id))
            ->exists();
    }

    private function ensurePelangganUser(?Pelanggan $pelanggan, string $name, string $phone, ?string $email): ?User
    {
        $user = $this->resolvePelangganUserByIdentity($pelanggan, $phone, $email);

        if ($user) {
            $this->syncLinkedUser($user, $name, $phone, $email);

            return $user;
        }

        if (! $email) {
            return null;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'no_telpon' => $phone,
            'password' => Hash::make(Str::password(24)),
            'role' => 'pelanggan',
        ]);
    }

    private function syncLinkedUser(User $user, string $name, string $phone, ?string $email): void
    {
        $user->fill([
            'name' => $name,
            'no_telpon' => $phone,
            'email' => $email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
    }
}
