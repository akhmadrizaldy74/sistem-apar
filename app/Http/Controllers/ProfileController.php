<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\PelangganSyncService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
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
    public function update(ProfileUpdateRequest $request, PelangganSyncService $pelangganSyncService): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $normalizedPhone = PhoneNumber::normalize((string) $validated['no_telpon']) ?? (string) $validated['no_telpon'];
        $email = array_key_exists('email', $validated)
            ? (trim((string) $validated['email']) ?: null)
            : $user->email;

        $user->fill([
            'name' => $validated['name'],
            'email' => $email,
            'no_telpon' => $normalizedPhone,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->isPelanggan()) {
            $user->loadMissing('pelanggan');
            $alamatMaps = trim((string) ($validated['alamat_maps'] ?? '')) ?: null;
            $alamatDetail = trim((string) ($validated['alamat_detail'] ?? '')) ?: null;

            $pelangganSyncService->syncFromCustomerUser($user, [
                'alamat' => $this->combineAddress($alamatMaps, $alamatDetail),
                'alamat_maps' => $alamatMaps,
                'alamat_detail' => $alamatDetail,
                'alamat_lat' => filled($validated['alamat_lat'] ?? null) ? (float) $validated['alamat_lat'] : null,
                'alamat_lng' => filled($validated['alamat_lng'] ?? null) ? (float) $validated['alamat_lng'] : null,
                'alamat_provinsi' => trim((string) ($validated['alamat_provinsi'] ?? '')) ?: null,
                'alamat_kota' => trim((string) ($validated['alamat_kota'] ?? '')) ?: null,
                'alamat_kecamatan' => trim((string) ($validated['alamat_kecamatan'] ?? '')) ?: null,
                'alamat_kode_pos' => trim((string) ($validated['alamat_kode_pos'] ?? '')) ?: null,
                'rajaongkir_destination_id' => trim((string) ($validated['rajaongkir_destination_id'] ?? '')) ?: null,
                'rajaongkir_destination_label' => trim((string) ($validated['rajaongkir_destination_label'] ?? '')) ?: null,
                'status' => $user->pelanggan?->status ?: 'calon',
                'sumber_data' => $user->pelanggan?->sumber_data ?: 'manual',
            ]);
        }

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
