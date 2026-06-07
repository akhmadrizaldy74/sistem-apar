<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManajemenAkunController extends Controller
{
    public function index(Request $request)
    {
        $totalAdmin = User::where('role', 'admin')->count();
        $totalTeknisi = User::where('role', 'teknisi')->count();
        $totalPelanggan = User::where('role', 'pelanggan')->count();

        $query = User::with('pelanggan');

        // Filter role
        if ($request->filled('role') && in_array($request->role, ['admin', 'teknisi', 'pelanggan'])) {
            $query->where('role', $request->role);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telpon', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($sub) use ($search) {
                        $sub->where('nama', 'like', "%{$search}%")
                            ->orWhere('no_wa', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.akun.index', compact(
            'users',
            'totalAdmin',
            'totalTeknisi',
            'totalPelanggan'
        ));
    }

    public function store(Request $request)
    {
        $normalizedPhone = PhoneNumber::normalize((string) $request->input('no_telpon'));

        $request->merge([
            'no_telpon' => $normalizedPhone ?? $request->input('no_telpon'),
            'email' => trim((string) $request->input('email')) ?: null,
        ]);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'no_telpon' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,teknisi,pelanggan',
        ];

        // Alamat wajib untuk pelanggan
        if ($request->role === 'pelanggan') {
            $rules['alamat'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $noTelpon = $normalizedPhone;

        if ($phoneConflict = $this->phoneConflictMessage($noTelpon)) {
            return back()->withInput()->withErrors(['no_telpon' => $phoneConflict]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'no_telpon' => $noTelpon,
            'password' => $validated['password'], // auto-hashed via cast
            'role' => $validated['role'],
        ]);

        // If pelanggan, create/link pelanggan record
        if ($validated['role'] === 'pelanggan' && $noTelpon) {
            $pelanggan = $this->resolvePelangganForUser($user, $noTelpon);
            $pelanggan->fill([
                'user_id' => $user->id,
                'nama' => $validated['name'],
                'no_wa' => $noTelpon,
                'alamat' => $request->input('alamat', '-'),
                'status' => 'tetap',
                'sumber_data' => $pelanggan->sumber_data ?: 'manual',
                'kategori_pelanggan' => $pelanggan->kategori_pelanggan ?: 'baru_manual',
            ]);
            $pelanggan->save();
        }

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $normalizedPhone = PhoneNumber::normalize((string) $request->input('no_telpon'));

        $request->merge([
            'no_telpon' => $normalizedPhone ?? $request->input('no_telpon'),
            'email' => trim((string) $request->input('email')) ?: null,
        ]);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_telpon' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,teknisi,pelanggan',
        ];

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        // Cek perubahan role dari pelanggan ke admin/teknisi
        if ($user->role === 'pelanggan' && $validated['role'] !== 'pelanggan') {
            if ($this->pelangganHasTransactions($user)) {
                return back()->with('error', 'Role tidak dapat diubah karena akun ini memiliki data transaksi sebagai pelanggan.');
            }
        }

        // Cek: jangan ubah role admin terakhir
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat mengubah role admin terakhir dalam sistem.');
            }
        }

        $noTelpon = $normalizedPhone;

        if ($phoneConflict = $this->phoneConflictMessage($noTelpon, $user)) {
            return back()->withInput()->withErrors(['no_telpon' => $phoneConflict]);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'no_telpon' => $noTelpon,
            'role' => $validated['role'],
        ];

        // Password opsional
        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password']; // auto-hashed via cast
        }

        $user->update($updateData);

        // If role changed to pelanggan, ensure pelanggan record exists
        if ($validated['role'] === 'pelanggan' && $noTelpon) {
            $pelanggan = $this->resolvePelangganForUser($user->fresh(), $noTelpon);
            $pelanggan->fill([
                'user_id' => $user->id,
                'nama' => $validated['name'],
                'no_wa' => $noTelpon,
                'status' => $pelanggan->status ?: 'tetap',
                'sumber_data' => $pelanggan->sumber_data ?: 'manual',
            ]);
            $pelanggan->save();
        }

        // If pelanggan has linked data, update nama there too
        $user->load('pelanggan');

        if ($user->pelanggan) {
            $user->pelanggan->update([
                'nama' => $validated['name'],
                'no_wa' => $noTelpon ?: $user->pelanggan->no_wa,
            ]);
        }

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        // Cannot delete self
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang login.');
        }

        // Cannot delete last admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus admin terakhir dalam sistem.');
            }
        }

        // Check transactions for pelanggan
        if ($user->role === 'pelanggan' && $this->pelangganHasTransactions($user)) {
            return back()->with('error', 'Akun tidak dapat dihapus karena memiliki data transaksi.');
        }

        // If pelanggan, delete pelanggan record too (only if no transactions)
        if ($user->pelanggan) {
            $user->pelanggan->delete();
        }

        $user->delete();

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Check if user's pelanggan record has any related transactions.
     */
    private function pelangganHasTransactions(User $user): bool
    {
        $pelanggan = $user->pelanggan;
        if (! $pelanggan) {
            return false;
        }

        // Check pesanan
        if ($pelanggan->pesanan()->exists()) {
            return true;
        }

        // Check unit APAR
        if ($pelanggan->units()->exists()) {
            return true;
        }

        // Check complain
        if ($pelanggan->complains()->exists()) {
            return true;
        }

        // Check testimoni
        if ($pelanggan->testimonis()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Reuse pelanggan data by phone when it is not yet linked to an account.
     */
    private function resolvePelangganForUser(User $user, ?string $phone): Pelanggan
    {
        $user->loadMissing('pelanggan');

        if ($user->pelanggan) {
            return $user->pelanggan;
        }

        if ($phone) {
            $pelanggan = Pelanggan::query()
                ->where('no_wa', $phone)
                ->where(function ($query) use ($user) {
                    $query->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                })
                ->first();

            if ($pelanggan) {
                return $pelanggan;
            }
        }

        return new Pelanggan;
    }

    private function phoneConflictMessage(?string $phone, ?User $ignoreUser = null): ?string
    {
        if (! $phone) {
            return null;
        }

        $userConflict = User::query()
            ->where('no_telpon', $phone)
            ->when($ignoreUser, fn ($query) => $query->whereKeyNot($ignoreUser->id))
            ->exists();

        if ($userConflict) {
            return 'Nomor WhatsApp/HP sudah digunakan akun lain.';
        }

        $pelangganConflict = Pelanggan::query()
            ->where('no_wa', $phone)
            ->whereNotNull('user_id')
            ->when($ignoreUser, fn ($query) => $query->where('user_id', '!=', $ignoreUser->id))
            ->exists();

        if ($pelangganConflict) {
            return 'Nomor WhatsApp/HP sudah terhubung ke akun pelanggan lain.';
        }

        return null;
    }
}
