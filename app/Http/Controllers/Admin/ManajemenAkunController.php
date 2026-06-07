<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'no_telpon' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:admin,teknisi,pelanggan',
        ];

        // Alamat wajib untuk pelanggan
        if ($request->role === 'pelanggan') {
            $rules['alamat'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
            'email.unique'       => 'Email sudah digunakan akun lain.',
        ]);

        // Normalize phone number
        $noTelpon = $this->normalizePhone($request->no_telpon);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'] ?? null,
            'no_telpon' => $noTelpon,
            'password'  => $validated['password'], // auto-hashed via cast
            'role'      => $validated['role'],
        ]);

        // If pelanggan, create/link pelanggan record
        if ($validated['role'] === 'pelanggan' && $noTelpon) {
            Pelanggan::updateOrCreate(
                ['no_wa' => $noTelpon],
                [
                    'user_id' => $user->id,
                    'nama'    => $validated['name'],
                    'alamat'  => $request->input('alamat', '-'),
                    'status'  => 'tetap',
                    'sumber_data'        => 'manual',
                    'kategori_pelanggan' => 'baru_manual',
                ]
            );
        }

        return redirect()->route('admin.akun.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_telpon' => 'nullable|string|max:20',
            'password'  => 'nullable|string|min:6|confirmed',
            'role'      => 'required|in:admin,teknisi,pelanggan',
        ];

        $validated = $request->validate($rules, [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
            'email.unique'       => 'Email sudah digunakan akun lain.',
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

        $noTelpon = $this->normalizePhone($request->no_telpon);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'] ?? null,
            'no_telpon' => $noTelpon,
            'role'      => $validated['role'],
        ];

        // Password opsional
        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password']; // auto-hashed via cast
        }

        $user->update($updateData);

        // If role changed to pelanggan, ensure pelanggan record exists
        if ($validated['role'] === 'pelanggan' && $noTelpon) {
            Pelanggan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama'  => $validated['name'],
                    'no_wa' => $noTelpon,
                    'status' => 'tetap',
                ]
            );
        }

        // If pelanggan has linked data, update nama there too
        if ($user->pelanggan) {
            $user->pelanggan->update([
                'nama'  => $validated['name'],
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
        if (!$pelanggan) {
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
     * Normalize phone number to local format.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $clean = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($clean, '62')) {
            $clean = '0' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '0' . $clean;
        }

        return $clean ?: null;
    }
}
