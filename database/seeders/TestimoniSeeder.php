<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use App\Models\Testimoni;
use Illuminate\Database\Seeder;

class TestimoniSeeder extends Seeder
{
    public function run(): void
    {
        $pelangganData = [
            ['nama' => 'Budi Santoso',       'no_wa' => '081234567801', 'alamat' => 'Jl. Pajajaran No. 12, Bogor'],
            ['nama' => 'Siti Rahayu',         'no_wa' => '081234567802', 'alamat' => 'Jl. Sudirman No. 45, Bogor'],
            ['nama' => 'Ahmad Fauzi',         'no_wa' => '081234567803', 'alamat' => 'Jl. Raya Tajur No. 7, Bogor'],
            ['nama' => 'Dewi Permata',        'no_wa' => '081234567804', 'alamat' => 'Jl. Gunung Batu No. 3, Bogor'],
            ['nama' => 'Rendra Wijaya',       'no_wa' => '081234567805', 'alamat' => 'Jl. Veteran No. 88, Bogor'],
            ['nama' => 'Hendra Kusuma',       'no_wa' => '081234567806', 'alamat' => 'Jl. Raya Dramaga No. 21, Bogor'],
        ];

        $testimoniData = [
            [
                'rating'  => 5,
                'review'  => 'Pelayanan sangat profesional dan cepat. APAR yang dipasang sudah sesuai standar SNI. Teknisi datang tepat waktu dan ramah. Sangat puas!',
                'tanggal' => '2026-03-10',
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'review'  => 'Sudah 2 tahun jadi pelanggan, tidak pernah kecewa. Refill APAR selalu beres dalam 1 hari. Harga juga kompetitif dibanding tempat lain.',
                'tanggal' => '2026-03-18',
                'status'  => 'approved',
            ],
            [
                'rating'  => 4,
                'review'  => 'Sistem monitoring online-nya memudahkan saya memantau masa berlaku APAR di kantor. Gak perlu cek manual lagi. Tim teknisi responsif.',
                'tanggal' => '2026-03-25',
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'review'  => 'Kami pesan 10 unit APAR untuk gedung baru dan semua terpasang dengan rapi. Service purnajual bagus, ada notifikasi kalau mau kadaluarsa.',
                'tanggal' => '2026-04-01',
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'review'  => 'Sangat merekomendasikan! Harga terjangkau, kualitas produk bagus, dan proses pesan lewat WhatsApp sangat mudah. Teknisi datang sesuai jadwal.',
                'tanggal' => '2026-04-08',
                'status'  => 'approved',
            ],
            [
                'rating'  => 4,
                'review'  => 'Service APAR di tempat ini cepat selesai. Yang paling saya suka adalah bisa cek status service lewat website langsung. Sangat modern!',
                'tanggal' => '2026-04-15',
                'status'  => 'approved',
            ],
        ];

        foreach ($pelangganData as $i => $data) {
            $pelanggan = Pelanggan::firstOrCreate(
                ['no_wa' => $data['no_wa']],
                ['nama' => $data['nama'], 'alamat' => $data['alamat']]
            );

            if (isset($testimoniData[$i])) {
                Testimoni::firstOrCreate(
                    ['pelanggan_id' => $pelanggan->id, 'tanggal' => $testimoniData[$i]['tanggal']],
                    array_merge($testimoniData[$i], ['pelanggan_id' => $pelanggan->id])
                );
            }
        }
    }
}
