<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Service;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_pelanggan_with_email_and_linked_customer_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111130',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pelanggan.store'), [
            'nama' => 'Pelanggan Email',
            'no_wa' => '+6281234567800',
            'email' => 'pelanggan-email@example.com',
            'alamat_maps' => 'Jl. Raya Contoh No. 1',
            'alamat_detail' => 'Ruko depan gerbang',
            'alamat_lat' => '-6.20000000',
            'alamat_lng' => '106.80000000',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bogor',
            'alamat_kecamatan' => 'Bogor Barat',
            'alamat_kode_pos' => '16111',
            'sumber_data' => 'manual',
            'kategori_pelanggan' => 'baru_manual',
        ]);

        $response->assertRedirect(route('admin.pelanggan.index'));

        $user = User::where('email', 'pelanggan-email@example.com')->first();
        $pelanggan = Pelanggan::where('no_wa', '081234567800')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($pelanggan);
        $this->assertSame('pelanggan', $user->role);
        $this->assertSame('081234567800', $user->no_telpon);
        $this->assertSame($user->id, $pelanggan->user_id);
    }

    public function test_admin_can_update_pelanggan_email_from_pelanggan_form_and_create_linked_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111131',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Lama',
            'no_wa' => '081234567801',
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pelanggan.update', $pelanggan), [
            'nama' => 'Pelanggan Lama Update',
            'no_wa' => '0812-3456-7801',
            'email' => 'pelanggan-lama@example.com',
            'alamat_maps' => 'Jl. Update No. 2',
            'alamat_detail' => 'Sebelah minimarket',
            'alamat_lat' => '-6.21000000',
            'alamat_lng' => '106.81000000',
            'alamat_provinsi' => 'Jawa Barat',
            'alamat_kota' => 'Bogor',
            'alamat_kecamatan' => 'Bogor Selatan',
            'alamat_kode_pos' => '16122',
        ]);

        $response->assertRedirect(route('admin.pelanggan.edit', $pelanggan));

        $pelanggan->refresh();
        $user = $pelanggan->user;

        $this->assertNotNull($user);
        $this->assertSame('pelanggan-lama@example.com', $user->email);
        $this->assertSame('081234567801', $user->no_telpon);
        $this->assertSame('Pelanggan Lama Update', $user->name);
    }

    public function test_admin_can_view_pelanggan_directory_without_admin_or_teknisi_entries(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111132',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Direktori',
            'email' => 'pelanggan-direktori@example.com',
            'no_telpon' => '081234567810',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Direktori',
            'no_wa' => '081234567810',
            'alamat' => 'Jl. Pelanggan No. 10',
            'status' => 'tetap',
        ]);

        $hiddenTeknisi = User::factory()->create([
            'role' => 'teknisi',
            'name' => 'Teknisi Tersembunyi',
            'email' => 'teknisi-hidden@example.com',
            'no_telpon' => '081111111133',
        ]);

        Pelanggan::create([
            'user_id' => $hiddenTeknisi->id,
            'nama' => 'Jangan Tampil',
            'no_wa' => '081222222222',
            'alamat' => 'Alamat Teknisi',
            'status' => 'tetap',
        ]);

        ['produk' => $produk] = $this->createProdukFixture($pelanggan);
        $this->createProductOrder($pelanggan, $produk, 250000, 1, 'selesai final');

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.index'));

        $response->assertOk();
        $response->assertSee('Daftar pelanggan yang terdaftar dan riwayat pembelian pelanggan.');
        $response->assertSee('Pelanggan Direktori');
        $response->assertSee('pelanggan-direktori@example.com');
        $response->assertSee('Jl. Pelanggan No. 10');
        $response->assertDontSee('Teknisi Tersembunyi');
        $response->assertDontSee('Jangan Tampil');

        $summary = $response->viewData('summary');
        $this->assertSame(1, $summary['totalPelanggan']);
        $this->assertSame(1, $summary['pelangganAktif']);
        $this->assertSame(1, $summary['totalTransaksiPelanggan']);

        $content = $response->getContent();
        $akunPos = strpos($content, route('admin.akun.index'));
        $pelangganPos = strpos($content, route('admin.pelanggan.index'));
        $produkPos = strpos($content, route('admin.produk.index'));

        $this->assertNotFalse($akunPos);
        $this->assertNotFalse($pelangganPos);
        $this->assertNotFalse($produkPos);
        $this->assertTrue($akunPos < $pelangganPos && $pelangganPos < $produkPos);
    }

    public function test_admin_can_view_customer_detail_with_product_history_only(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111134',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Detail',
            'email' => 'pelanggan-detail@example.com',
            'no_telpon' => '081234567811',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Detail',
            'no_wa' => '081234567811',
            'alamat' => 'Jl. Detail Pelanggan No. 5',
            'status' => 'tetap',
        ]);

        ['produk' => $produk, 'unit' => $unit] = $this->createProdukFixture($pelanggan, 'APAR 6 Kg');
        $this->createProductOrder($pelanggan, $produk, 750000, 2, 'selesai final');
        $this->createServiceOrder($pelanggan, $unit, 350000, 'selesai final');

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.show', $pelanggan));

        $response->assertOk();
        $response->assertSee('Detail Pelanggan');
        $response->assertSee('Pelanggan Detail');
        $response->assertSee('pelanggan-detail@example.com');
        $response->assertSee('APAR 6 Kg');
        $response->assertSee('Rp 750.000');
        $response->assertSee('Selesai Final');
        $response->assertDontSee('Hydrotest');
        $response->assertDontSee('Refill APAR');
    }

    public function test_customer_detail_shows_empty_state_when_no_product_history_exists(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111135',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Service',
            'email' => 'pelanggan-service@example.com',
            'no_telpon' => '081234567812',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Service',
            'no_wa' => '081234567812',
            'alamat' => 'Jl. Service No. 2',
            'status' => 'tetap',
        ]);

        ['unit' => $unit] = $this->createProdukFixture($pelanggan);
        $this->createServiceOrder($pelanggan, $unit, 450000, 'selesai final');

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.show', $pelanggan));

        $response->assertOk();
        $response->assertSee('Belum ada riwayat pembelian.');
        $response->assertDontSee('Hydrotest');
    }

    private function createProdukFixture(Pelanggan $pelanggan, string $produkNama = 'APAR 3 Kg'): array
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
        ]);

        $produk = Produk::create([
            'nama' => $produkNama,
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '3 Kg',
            'penggunaan' => 'Testing',
            'harga' => 250000,
            'stok' => 10,
        ]);

        $unit = UnitApar::create([
            'pelanggan_id' => $pelanggan->id,
            'produk_id' => $produk->id,
            'no_seri' => 'UT-PEL-0001',
            'tgl_beli' => now()->subMonths(2)->toDateString(),
            'tgl_produksi' => now()->subMonths(2)->toDateString(),
            'ukuran' => '3 Kg',
            'bahan' => 'Dry Chemical Powder',
            'kondisi_awal' => 'layak',
            'tgl_expired' => now()->addMonths(10)->toDateString(),
        ]);

        return compact('produk', 'unit');
    }

    private function createProductOrder(Pelanggan $pelanggan, Produk $produk, int $total, int $jumlah, string $status): Pesanan
    {
        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'produk',
            'sumber_pesanan' => 'input_admin',
            'total' => $total,
            'total_harga' => $total,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => $status,
            'tanggal' => now()->toDateString(),
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => $jumlah,
            'harga' => (int) round($total / max($jumlah, 1)),
            'subtotal' => $total,
        ]);

        return $pesanan;
    }

    private function createServiceOrder(Pelanggan $pelanggan, UnitApar $unit, int $biaya, string $status): Service
    {
        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'nama_penerima' => $pelanggan->nama,
            'nomor_wa_penerima' => $pelanggan->no_wa,
            'tipe' => 'service',
            'sumber_pesanan' => 'input_admin',
            'service_jenis_layanan' => 'service',
            'total' => $biaya,
            'total_harga' => $biaya,
            'metode_pembayaran' => 'cash',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'status' => $status,
            'tanggal' => now()->toDateString(),
        ]);

        return Service::create([
            'pesanan_id' => $pesanan->id,
            'unit_apar_id' => $unit->id,
            'jenis_service' => 'Hydrotest',
            'tgl_service' => now()->toDateString(),
            'biaya' => $biaya,
            'status_konfirmasi' => 'confirmed',
        ]);
    }
}
