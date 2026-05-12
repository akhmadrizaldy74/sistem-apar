<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0' . $digits;
        }

        return $digits;
    }

    private function combineAddress(?string $mapsAddress, ?string $detailAddress): ?string
    {
        $parts = array_filter([
            trim((string) $mapsAddress),
            trim((string) $detailAddress),
        ], fn (string $value) => $value !== '');

        if (empty($parts)) {
            return null;
        }

        return implode(' | Detail: ', $parts);
    }

    private function resolvePelangganForUser(int $userId, string $normalizedPhone): Pelanggan
    {
        $pelanggan = Pelanggan::query()
            ->where('user_id', $userId)
            ->first();

        if ($pelanggan) {
            return $pelanggan;
        }

        $pelanggan = Pelanggan::query()
            ->where('no_wa', $normalizedPhone)
            ->where(function ($query) use ($userId) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $userId);
            })
            ->first();

        return $pelanggan ?: new Pelanggan();
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('pelanggan');
        $view = ($user->isAdmin() || $user->isTeknisi())
            ? 'profile.edit'
            : 'profile.customer-edit';

        return view($view, [
            'user' => $user,
            'pelanggan' => $user->pelanggan,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $normalizedPhone = $this->normalizePhone((string) $validated['no_telpon']);

        $user->fill([
            'name' => $validated['name'],
            'no_telpon' => $normalizedPhone,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $pelanggan = $this->resolvePelangganForUser($user->id, $normalizedPhone);
        $alamatMaps = trim((string) ($validated['alamat_maps'] ?? '')) ?: null;
        $alamatDetail = trim((string) ($validated['alamat_detail'] ?? '')) ?: null;

        $pelanggan->fill([
            'user_id' => $user->id,
            'nama' => $user->name,
            'no_wa' => $normalizedPhone,
            'alamat' => $this->combineAddress($alamatMaps, $alamatDetail),
            'alamat_maps' => $alamatMaps,
            'alamat_detail' => $alamatDetail,
            'alamat_lat' => filled($validated['alamat_lat'] ?? null) ? (float) $validated['alamat_lat'] : null,
            'alamat_lng' => filled($validated['alamat_lng'] ?? null) ? (float) $validated['alamat_lng'] : null,
            'alamat_provinsi' => trim((string) ($validated['alamat_provinsi'] ?? '')) ?: null,
            'alamat_kota' => trim((string) ($validated['alamat_kota'] ?? '')) ?: null,
            'alamat_kecamatan' => trim((string) ($validated['alamat_kecamatan'] ?? '')) ?: null,
            'alamat_kode_pos' => trim((string) ($validated['alamat_kode_pos'] ?? '')) ?: null,
            'status' => $pelanggan->status ?: 'calon',
            'sumber_data' => $pelanggan->sumber_data ?: 'manual',
        ]);
        $pelanggan->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
