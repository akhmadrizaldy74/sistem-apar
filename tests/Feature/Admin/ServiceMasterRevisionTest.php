<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ServicePaket;
use App\Models\User;
use App\Services\ServiceMasterSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceMasterRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_pages_show_only_final_equipment_and_service_types(): void
    {
        $admin = $this->createAdmin();

        Peralatan::create([
            'nama' => 'Safety Pin (Pin Pengaman)',
            'stok' => 3,
            'stok_minimum' => 1,
            'harga_standar' => 1000,
        ]);
        Peralatan::create([
            'nama' => 'Selang APAR',
            'stok' => 2,
            'stok_minimum' => 1,
            'harga_standar' => 15000,
        ]);
        Peralatan::create([
            'nama' => 'Nozzle/Corong CO2',
            'stok' => 4,
            'stok_minimum' => 1,
            'harga_standar' => 50000,
        ]);

        $peralatanResponse = $this->actingAs($admin)->get(route('admin.peralatan.index'));

        $peralatanResponse->assertOk();
        $peralatanResponse->assertSeeText('Safety Pin APAR');
        $peralatanResponse->assertSeeText('Selang APAR Powder/Foam');
        $peralatanResponse->assertSeeText('Baut Bracket APAR');
        $peralatanResponse->assertDontSeeText('Safety Pin (Pin Pengaman)');
        $peralatanResponse->assertDontSeeText('Nozzle/Corong CO2');

        $this->assertDatabaseHas('peralatans', [
            'nama' => 'Safety Pin APAR',
            'harga_standar' => 10000,
        ]);

        $serviceResponse = $this->actingAs($admin)->get(route('admin.service-paket.index'));

        $serviceResponse->assertOk();
        $serviceResponse->assertSeeText('Service Ringan');
        $serviceResponse->assertSeeText('Ganti Selang Powder/Foam');
        $serviceResponse->assertSeeText('Ganti Selang CO2');
        $serviceResponse->assertSeeText('Ganti Valve APAR');
        $serviceResponse->assertSeeText('Ganti Pressure Gauge');
        $serviceResponse->assertSeeText('Pasang/Ganti Bracket');
        $serviceResponse->assertDontSeeText('Nozzle');
    }

    public function test_service_finalization_reduces_final_master_stock_once(): void
    {
        $admin = $this->createAdmin();
        $pelanggan = $this->createLinkedCustomer('PT Final Sekali', '628111110301', 'Jl Final Sekali');
        $this->seedPowderProduct();
        app(ServiceMasterSyncService::class)->sync();

        Peralatan::where('nama', 'Valve APAR')->update(['stok' => 5]);
        Peralatan::where('nama', 'O-Ring/Karet Seal')->update(['stok' => 5]);
        Peralatan::where('nama', 'Segel Pengaman Plastik')->update(['stok' => 5]);

        $paket = ServicePaket::query()->where('nama', 'Ganti Valve APAR')->firstOrFail();

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => $paket->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 2,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => now()->toDateString(),
            'total' => 200000,
            'total_harga' => 200000,
            'service_estimasi_biaya' => 200000,
            'sumber_pesanan' => 'website',
        ]);

        $firstResponse = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $firstResponse->assertRedirect();
        $this->assertSame(Pesanan::STATUS_SELESAI_FINAL, $pesanan->fresh()->status);
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'Valve APAR')->value('stok'));
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'O-Ring/Karet Seal')->value('stok'));
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'Segel Pengaman Plastik')->value('stok'));

        $secondResponse = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan->fresh()), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $secondResponse->assertRedirect();
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'Valve APAR')->value('stok'));
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'O-Ring/Karet Seal')->value('stok'));
        $this->assertSame(3, (int) Peralatan::query()->where('nama', 'Segel Pengaman Plastik')->value('stok'));
    }

    public function test_service_finalization_is_rejected_when_equipment_stock_is_insufficient(): void
    {
        $admin = $this->createAdmin();
        $pelanggan = $this->createLinkedCustomer('PT Stok Kurang', '628111110302', 'Jl Stok Kurang');
        $this->seedPowderProduct();
        app(ServiceMasterSyncService::class)->sync();

        Peralatan::where('nama', 'Bracket/Gantungan APAR')->update(['stok' => 1]);
        Peralatan::where('nama', 'Baut Bracket APAR')->update(['stok' => 10]);

        $paket = ServicePaket::query()->where('nama', 'Pasang/Ganti Bracket')->firstOrFail();

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $pelanggan->user_id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => $paket->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 2,
            'status' => Pesanan::STATUS_DIKONFIRMASI_ADMIN,
            'tanggal' => now()->toDateString(),
            'total' => 120000,
            'total_harga' => 120000,
            'service_estimasi_biaya' => 120000,
            'sumber_pesanan' => 'website',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.service.request.status', $pesanan), [
            'status' => Pesanan::STATUS_SELESAI_FINAL,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(Pesanan::STATUS_DIKONFIRMASI_ADMIN, $pesanan->fresh()->status);
        $this->assertSame(1, (int) Peralatan::query()->where('nama', 'Bracket/Gantungan APAR')->value('stok'));
        $this->assertSame(10, (int) Peralatan::query()->where('nama', 'Baut Bracket APAR')->value('stok'));
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111119999',
        ]);
    }

    private function createLinkedCustomer(string $name, string $phone, string $address): Pelanggan
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: (string) random_int(1000, 9999);

        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => $name,
            'email' => 'service-master-'.$digits.'@example.com',
            'no_telpon' => $phone,
        ]);

        return Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $name,
            'no_wa' => $phone,
            'alamat' => $address,
            'status' => 'tetap',
        ]);
    }

    private function seedPowderProduct(): void
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 650000,
            'deskripsi' => 'Produk test powder 6 kg',
            'stok' => 0,
        ]);
    }
}
