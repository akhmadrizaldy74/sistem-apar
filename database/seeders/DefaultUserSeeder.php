<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Seed default user accounts.
     * Safe to run multiple times — uses updateOrCreate to avoid duplicates.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Sistem',
                'no_telpon' => '081111111111',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        // Teknisi
        User::updateOrCreate(
            ['email' => 'teknisi@gmail.com'],
            [
                'name' => 'Teknisi',
                'no_telpon' => '082222222222',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
            ],
        );

        // Pelanggan
        $pelangganUser = User::updateOrCreate(
            ['email' => 'akhmadrizaldy69@gmail.com'],
            [
                'name' => 'Akhmad Rizaldy',
                'no_telpon' => '087830665027',
                'password' => Hash::make('password'),
                'role' => 'pelanggan',
            ],
        );

        // Create pelanggan profile linked to user
        $pelanggan = Pelanggan::updateOrCreate(
            ['user_id' => $pelangganUser->id],
            [
                'nama' => 'Akhmad Rizaldy',
                'no_wa' => '087830665027',
                'alamat' => 'Bogor, Jawa Barat',
                'status' => 'tetap',
                'sumber_data' => 'manual',
                'kategori_pelanggan' => 'lama',
            ],
        );

        $foamProduct = \App\Models\Produk::where('nama', 'like', '%GuardALL%Foam%6%')->first()
            ?? \App\Models\Produk::first();

        if ($foamProduct) {
            \App\Models\UnitApar::updateOrCreate(
                ['no_seri' => 'AKHMAD-21072026-02'],
                [
                    'pelanggan_id' => $pelanggan->id,
                    'produk_id' => $foamProduct->id,
                    'ukuran' => '6 kg',
                    'bahan' => 'Liquid Foam (Busa)',
                    'tgl_beli' => now()->subMonths(3)->toDateString(),
                    'tgl_produksi' => now()->subMonths(3)->toDateString(),
                    'tgl_expired' => now()->addDays(13)->toDateString(),
                    'kondisi_awal' => 'layak',
                ]
            );
        }

        $this->command->info('✅ Default users & sample units seeded (admin, teknisi, pelanggan).');
    }
}
