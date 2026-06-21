<?php

namespace Tests\Feature\Teknisi;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PekerjaanAktifUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teknisi_active_page_shows_filters_tasks_and_actions(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $pelangganProduk = Pelanggan::create([
            'nama' => 'PT Produk Aman',
            'no_wa' => '628111000001',
            'alamat' => 'Jl Produk No. 1',
        ]);

        $pelangganService = Pelanggan::create([
            'nama' => 'CV Service Aman',
            'no_wa' => '628111000002',
            'alamat' => 'Jl Service No. 2',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 KG',
            'merek' => 'SAFETY',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 450000,
            'deskripsi' => 'Produk test',
            'stok' => 10,
        ]);

        $pesananProduk = Pesanan::create([
            'pelanggan_id' => $pelangganProduk->id,
            'teknisi_id' => $teknisi->id,
            'no_pesanan' => 'INV-00001',
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 900000,
            'total_harga' => 900000,
            'metode_pengiriman' => 'pickup',
        ]);

        $pesananProduk->details()->create([
            'produk_id' => $produk->id,
            'merek' => 'SAFETY',
            'kapasitas' => '6 kg',
            'jumlah' => 2,
            'harga' => 450000,
            'subtotal' => 900000,
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelangganService->id,
            'teknisi_id' => $teknisi->id,
            'no_pesanan' => 'INV-00002',
            'tipe' => 'service',
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 250000,
            'total_harga' => 250000,
            'service_jenis_layanan' => 'service',
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 1,
            'service_metode_penanganan' => 'antar sendiri',
            'service_keluhan' => 'Catatan Pelanggan: Tabung perlu dicek ulang.',
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.pekerjaan-aktif'));

        $response->assertOk();
        $response->assertSeeText('Pekerjaan Aktif');
        $response->assertSeeText('Semua');
        $response->assertSeeText('Pesanan Produk');
        $response->assertSeeText('Service / Refill');
        $response->assertSeeText('PT Produk Aman');
        $response->assertSeeText('CV Service Aman');
        $response->assertSeeText('Kerjakan');
        $response->assertSeeText('Selesai');
        $response->assertDontSeeText('Pembayaran');
        $response->assertDontSeeText('Bank');
        $response->assertDontSeeText('INV-00001');
        $response->assertDontSeeText('INV-00002');
        $response->assertDontSeeText('Nomor Pesanan');
        $response->assertDontSeeText('Nomor Layanan');
    }

    public function test_teknisi_active_page_can_filter_service_and_refill_tasks(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $pelangganProduk = Pelanggan::create([
            'nama' => 'PT Produk Filter',
            'no_wa' => '628111000003',
            'alamat' => 'Jl Produk Filter',
        ]);

        $pelangganRefill = Pelanggan::create([
            'nama' => 'PT Refill Filter',
            'no_wa' => '628111000004',
            'alamat' => 'Jl Refill Filter',
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelangganProduk->id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 100000,
        ]);

        Pesanan::create([
            'pelanggan_id' => $pelangganRefill->id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'service',
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 150000,
            'service_jenis_layanan' => 'refill',
            'service_jenis_apar' => 'CO2',
            'service_ukuran_apar' => '5 kg',
            'service_jumlah_unit' => 2,
            'service_metode_penanganan' => 'dijemput',
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.pekerjaan-aktif', [
            'filter' => 'service-refill',
        ]));

        $response->assertOk();
        $response->assertSeeText('PT Refill Filter');
        $response->assertSeeText('APAR CO2 5 kg');
        $response->assertSeeText('Jumlah Unit: 2 unit');
        $response->assertDontSeeText('PT Produk Filter');
    }

    public function test_teknisi_active_page_shows_clear_empty_state(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.pekerjaan-aktif'));

        $response->assertOk();
        $response->assertSeeText('Belum ada pekerjaan aktif.');
        $response->assertSeeText('Pekerjaan dari admin akan muncul di halaman ini.');
    }

    public function test_teknisi_dashboard_shows_stock_warnings_for_refill_and_equipment_only(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Admin Only 2 Kg',
            'merek' => 'HIDDEN',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 Kg',
            'penggunaan' => 'Testing',
            'harga' => 500000,
            'stok' => 2,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 2,
            'sisa_qty' => 2,
            'tgl_produksi' => now()->subWeek()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Produk hanya untuk admin',
        ]);

        JenisRefill::create([
            'nama' => 'Dry Powder',
            'stok' => 3,
            'satuan' => 'kg',
            'harga' => 95000,
            'stok_minimum' => 5,
        ]);

        JenisRefill::create([
            'nama' => 'Foam',
            'stok' => 0,
            'satuan' => 'kg',
            'harga' => 105000,
            'stok_minimum' => 5,
        ]);

        Peralatan::create([
            'nama' => 'Valve APAR',
            'stok' => 1,
            'stok_minimum' => 3,
            'harga_standar' => 50000,
        ]);

        Peralatan::create([
            'nama' => 'Safety Pin APAR',
            'stok' => 0,
            'stok_minimum' => 3,
            'harga_standar' => 10000,
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Peringatan Stok');
        $response->assertSeeText('Powder');
        $response->assertSeeText('Foam');
        $response->assertSeeText('Valve APAR');
        $response->assertSeeText('Safety Pin APAR');
        $response->assertDontSeeText('APAR Admin Only 2 Kg');

        $stockAlerts = $response->viewData('stockAlerts');

        $this->assertTrue((bool) $stockAlerts['hasIssues']);
        $this->assertSame(4, (int) $stockAlerts['totalIssueCount']);
        $this->assertCount(2, $stockAlerts['groups']);
    }

    public function test_teknisi_dashboard_shows_safe_message_when_stock_is_okay(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $response = $this->actingAs($teknisi)->get(route('teknisi.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Peringatan Stok');
        $response->assertSeeText('Semua stok dalam kondisi aman');
        $this->assertFalse((bool) $response->viewData('stockAlerts')['hasIssues']);
    }

    public function test_teknisi_can_start_task_and_status_moves_to_dikerjakan(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Start Flow',
            'no_wa' => '628111000005',
            'alamat' => 'Jl Start Flow',
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DITUGASKAN_KE_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 100000,
            'stok_dikurangi' => false,
        ]);

        $response = $this->actingAs($teknisi)->post(route('teknisi.tugas.mulai', $pesanan));

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            'stok_dikurangi' => 0,
        ]);
    }

    public function test_teknisi_can_finish_task_without_finalizing_stock(): void
    {
        $teknisi = User::factory()->create([
            'role' => 'teknisi',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'CO2',
        ]);

        $pelanggan = Pelanggan::create([
            'nama' => 'PT Finish Flow',
            'no_wa' => '628111000006',
            'alamat' => 'Jl Finish Flow',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR CO2 5 KG',
            'merek' => 'GUARD',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '5 kg',
            'penggunaan' => 'Server room',
            'harga' => 750000,
            'deskripsi' => 'Produk flow selesai',
            'stok' => 5,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'teknisi_id' => $teknisi->id,
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DIKERJAKAN_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 750000,
            'total_harga' => 750000,
            'stok_dikurangi' => false,
        ]);

        $pesanan->details()->create([
            'produk_id' => $produk->id,
            'merek' => 'GUARD',
            'kapasitas' => '5 kg',
            'jumlah' => 1,
            'harga' => 750000,
            'subtotal' => 750000,
        ]);

        $response = $this->actingAs($teknisi)->post(route('teknisi.tugas.selesai', $pesanan), [
            'catatan' => 'Pekerjaan selesai dan siap dicek admin.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pesanans', [
            'id' => $pesanan->id,
            'status' => Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            'teknisi_catatan' => 'Pekerjaan selesai dan siap dicek admin.',
            'stok_dikurangi' => 0,
        ]);
    }
}
