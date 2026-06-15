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

    public function test_admin_can_create_customer_account_from_manajemen_akun_and_auto_create_pelanggan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111130',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.akun.store'), [
            'name' => 'Pelanggan Email',
            'email' => 'pelanggan-email@example.com',
            'no_telpon' => '+6281234567800',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'pelanggan',
            'alamat' => 'Jl. Raya Contoh No. 1',
        ]);

        $response->assertRedirect(route('admin.akun.index'));

        $user = User::where('email', 'pelanggan-email@example.com')->first();
        $pelanggan = Pelanggan::where('no_wa', '081234567800')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($pelanggan);
        $this->assertSame('pelanggan', $user->role);
        $this->assertSame('081234567800', $user->no_telpon);
        $this->assertSame($user->id, $pelanggan->user_id);
        $this->assertSame('Pelanggan Email', $pelanggan->nama);
    }

    public function test_admin_can_update_customer_address_without_changing_linked_account_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111131',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Lama',
            'email' => 'pelanggan-lama-awal@example.com',
            'no_telpon' => '081234567801',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Lama',
            'no_wa' => '081234567801',
            'alamat' => 'Alamat lama',
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pelanggan.update', $pelanggan), [
            'nama' => 'Nama Tidak Boleh Berubah',
            'no_wa' => '081200000000',
            'email' => 'tidak-boleh-berubah@example.com',
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
        $this->assertSame('Pelanggan Lama', $pelanggan->nama);
        $this->assertSame('081234567801', $pelanggan->no_wa);
        $this->assertSame('Jl. Update No. 2', $pelanggan->alamat_maps);
        $this->assertSame('Sebelah minimarket', $pelanggan->alamat_detail);
        $this->assertSame('pelanggan-lama-awal@example.com', $user->email);
        $this->assertSame('081234567801', $user->no_telpon);
        $this->assertSame('Pelanggan Lama', $user->name);
    }

    public function test_admin_can_update_customer_without_coordinates_and_old_map_point_is_cleared(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111230',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Koordinat',
            'email' => 'pelanggan-koordinat@example.com',
            'no_telpon' => '081234567830',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Koordinat',
            'no_wa' => '081234567830',
            'alamat' => 'Alamat lama',
            'alamat_maps' => 'Alamat maps lama',
            'alamat_lat' => -6.200001,
            'alamat_lng' => 106.800001,
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.pelanggan.update', $pelanggan), [
            'alamat_maps' => 'Jl. Baru Tanpa Titik No. 7',
            'alamat_detail' => 'Dekat pos satpam',
            'alamat_lat' => '',
            'alamat_lng' => '',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.pelanggan.edit', $pelanggan));

        $pelanggan->refresh();
        $user = $pelanggan->user;

        $this->assertSame('Pelanggan Koordinat', $pelanggan->nama);
        $this->assertSame('081234567830', $pelanggan->no_wa);
        $this->assertSame('Jl. Baru Tanpa Titik No. 7', $pelanggan->alamat_maps);
        $this->assertNull($pelanggan->alamat_lat);
        $this->assertNull($pelanggan->alamat_lng);
        $this->assertNotNull($user);
        $this->assertSame('pelanggan-koordinat@example.com', $user->email);
        $this->assertSame('Pelanggan Koordinat', $user->name);
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

        Pelanggan::create([
            'nama' => 'Pelanggan Lama Tanpa Akun',
            'no_wa' => '081333333333',
            'alamat' => 'Alamat Arsip Lama',
            'status' => 'tetap',
        ]);

        ['produk' => $produk] = $this->createProdukFixture($pelanggan);
        $this->createProductOrder($pelanggan, $produk, 250000, 1, 'selesai final');

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.index'));

        $response->assertOk();
        $response->assertSee('Daftar pelanggan yang berasal dari akun dengan role pelanggan.');
        $response->assertSee('Pelanggan Direktori');
        $response->assertSee('pelanggan-direktori@example.com');
        $response->assertSee('Jl. Pelanggan No. 10');
        $response->assertDontSee('Teknisi Tersembunyi');
        $response->assertDontSee('Jangan Tampil');
        $response->assertDontSee('Pelanggan Lama Tanpa Akun');

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
        $this->createProductOrder($pelanggan, $produk, 150000, 1, 'ditolak');

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.show', $pelanggan));

        $response->assertOk();
        $response->assertSee('Detail Pelanggan');
        $response->assertSee('Pelanggan Detail');
        $response->assertSee('pelanggan-detail@example.com');
        $response->assertSee('APAR 6 Kg');
        $response->assertSee('Rp 750.000');
        $response->assertSee('Selesai Final');
        $response->assertDontSee('Rp 150.000');
        $response->assertDontSee('Hydrotest');
        $response->assertDontSee('Refill APAR');
    }

    public function test_admin_customer_edit_page_only_shows_read_only_account_info_and_address_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111231',
        ]);

        $customerUser = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Map',
            'email' => 'pelanggan-map@example.com',
            'no_telpon' => '081234567831',
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $customerUser->id,
            'nama' => 'Pelanggan Map',
            'no_wa' => '081234567831',
            'alamat' => 'Jl. Peta No. 8',
            'status' => 'tetap',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pelanggan.edit', $pelanggan));

        $response->assertOk();
        $response->assertSee('Edit Alamat Pelanggan');
        $response->assertSee('Perbarui alamat dan titik lokasi pelanggan.');
        $response->assertSee('Lihat Landing Page');
        $response->assertSee('Nama Pelanggan');
        $response->assertSee('Email');
        $response->assertSee('WhatsApp / HP');
        $response->assertSee('Cari Alamat di Peta');
        $response->assertSee('Data akun dikelola dari');
        $response->assertSee('id="admin-address-map"', false);
        $response->assertSee('name="alamat_maps"', false);
        $response->assertSee('name="alamat_detail"', false);
        $response->assertSee('name="alamat_lat"', false);
        $response->assertSee('name="alamat_lng"', false);
        $response->assertDontSee('name="nama"', false);
        $response->assertDontSee('name="email"', false);
        $response->assertDontSee('name="no_wa"', false);
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
