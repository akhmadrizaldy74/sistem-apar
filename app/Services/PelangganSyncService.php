<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\User;
use App\Support\PhoneNumber;

class PelangganSyncService
{
    public function syncFromCustomerUser(User $user, array $attributes = []): ?Pelanggan
    {
        if (! $user->isPelanggan()) {
            return null;
        }

        $phone = $this->normalizePhone((string) ($attributes['no_wa'] ?? $user->no_telpon));
        if ($phone === '') {
            return $user->pelanggan;
        }

        $pelanggan = $this->resolveForUser($user, $phone);

        $payload = [
            'user_id' => $user->id,
            'nama' => $this->nullableTrim($attributes['nama'] ?? $user->name) ?? $user->name,
            'no_wa' => $phone,
            'status' => $this->nullableTrim($attributes['status'] ?? null) ?: ($pelanggan->status ?: 'tetap'),
            'sumber_data' => $this->nullableTrim($attributes['sumber_data'] ?? null) ?: ($pelanggan->sumber_data ?: 'manual'),
            'kategori_pelanggan' => $this->nullableTrim($attributes['kategori_pelanggan'] ?? null) ?: ($pelanggan->kategori_pelanggan ?: 'baru_manual'),
        ];

        foreach ([
            'alamat',
            'alamat_maps',
            'alamat_detail',
            'alamat_provinsi',
            'alamat_kota',
            'alamat_kecamatan',
            'alamat_kode_pos',
            'rajaongkir_destination_id',
            'rajaongkir_destination_label',
            'perusahaan',
            'catatan_internal',
        ] as $field) {
            if (array_key_exists($field, $attributes)) {
                $payload[$field] = $this->nullableTrim($attributes[$field]);
            }
        }

        if (array_key_exists('alamat_lat', $attributes)) {
            $payload['alamat_lat'] = $this->nullableFloat($attributes['alamat_lat']);
        }

        if (array_key_exists('alamat_lng', $attributes)) {
            $payload['alamat_lng'] = $this->nullableFloat($attributes['alamat_lng']);
        }

        $pelanggan->fill($payload);
        $pelanggan->save();

        return $pelanggan;
    }

    public function detachUser(User $user): void
    {
        $user->loadMissing('pelanggan');

        if ($user->pelanggan) {
            $user->pelanggan->update([
                'user_id' => null,
            ]);
        }
    }

    private function resolveForUser(User $user, string $phone): Pelanggan
    {
        $user->loadMissing('pelanggan');

        if ($user->pelanggan) {
            return $user->pelanggan;
        }

        return Pelanggan::query()
            ->where('no_wa', $phone)
            ->where(function ($query) use ($user) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->first()
            ?? new Pelanggan();
    }

    private function normalizePhone(string $value): string
    {
        return PhoneNumber::normalize($value) ?? trim($value);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
