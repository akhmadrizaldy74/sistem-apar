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
        Pelanggan::updateOrCreate(
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

        $this->command->info('✅ Default users seeded (admin, teknisi, pelanggan).');
    }
}
