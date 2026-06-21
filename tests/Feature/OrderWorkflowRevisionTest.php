<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Pelanggan;
use App\Models\Peralatan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\Service;
use App\Models\ServicePaket;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use App\Services\PaidOrderStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderWorkflowRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_active_paid_order_and_restore_stock_and_units(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create(['role' => 'admin']);
        [$customerUser, $pelanggan] = $this->createCustomer('081234560001');
        $produk = $this->createProduct('APAR Active Delete');

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_DIPROSES,
            'tanggal' => now()->toDateString(),
            'total' => 750000,
            'total_harga' => 750000,
            'metode_pengiriman' => 'pickup',
            'pembayaran_terkonfirmasi_at' => now(),
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 750000,
            'subtotal' => 750000,
        ]);

        app(PaidOrderStockService::class)->apply($pesanan->fresh([
            'details.produk.jenisApar',
            'pelanggan',
            'unitApars.produk',
        ]));

        $this->assertTrue((bool) $pesanan->fresh()->stok_dikurangi);
        $this->assertSame(4, (int) $produk->fresh()->stok);
        $this->assertSame(4, (int) StokBatch::query()->where('produk_id', $produk->id)->value('sisa_qty'));
        $this->assertDatabaseCount('unit_apars', 1);

        $response = $this->actingAs($admin)
            ->from(route('admin.pesanan.index'))
            ->delete(route('admin.pesanan.destroy-typed', [
                'jenis' => $pesanan->adminDestroyTypeSlug(),
                'pesanan' => $pesanan,
            ]));

        $response->assertRedirect(route('admin.pesanan.index'));
        $response->assertSessionHas('success', 'Pesanan aktif berhasil dibatalkan dan dihapus.');
        $this->assertDatabaseMissing('pesanans', ['id' => $pesanan->id]);
        $this->assertDatabaseCount('unit_apars', 0);
        $this->assertSame(5, (int) $produk->fresh()->stok);
        $this->assertSame(5, (int) StokBatch::query()->where('produk_id', $produk->id)->value('sisa_qty'));
    }

    public function test_invoice_shows_formal_total_breakdown_for_approved_price_adjustment(): void
    {
        [$customerUser, $pelanggan] = $this->createCustomer('081234560002');
        $produk = $this->createProduct('APAR Invoice Breakdown');

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_DISETUJUI,
            'tanggal' => now()->toDateString(),
            'total' => 1400000,
            'total_harga' => 1400000,
            'metode_pengiriman' => 'diantar_internal',
            'ongkir' => 100000,
        ] + Pesanan::purchasePriceAttributes([
            'status' => Pesanan::PRICE_REQUEST_APPROVED,
            'requested_price' => 1200000,
            'final_price' => 1200000,
            'discounted_total' => 1350000,
            'initial_total' => 1450000,
            'used' => true,
        ]));

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 2,
            'harga' => 750000,
            'subtotal' => 1500000,
        ]);

        $response = $this->actingAs($customerUser)->get(route('invoice.show', $pesanan));

        $response->assertOk();
        $response->assertSeeText('Subtotal Produk / Layanan');
        $response->assertSeeText('Diskon');
        $response->assertSeeText('Biaya Pengiriman');
        $response->assertSeeText('Penyesuaian Harga Disetujui');
        $response->assertSeeText('Total Pembayaran');
    }

    public function test_stock_is_deducted_once_after_payment_for_product_refill_and_service_orders(): void
    {
        Storage::fake('public');

        [$customerUser, $pelanggan] = $this->createCustomer('081234560003');
        $produk = $this->createProduct('APAR Payment Stock');

        $productOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_PENDING,
            'tanggal' => now()->toDateString(),
            'total' => 750000,
            'total_harga' => 750000,
            'metode_pengiriman' => 'pickup',
        ]);

        PesananDetail::create([
            'pesanan_id' => $productOrder->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 750000,
            'subtotal' => 750000,
        ]);

        $payResponse = $this->actingAs($customerUser)->post(route('order.payment.store', $productOrder), [
            'metode_pembayaran' => 'transfer',
            'bank' => 'bca',
            'bukti_pembayaran' => UploadedFile::fake()->image('bukti-stock.jpg'),
        ]);

        $payResponse->assertRedirect(route('home'));
        $this->assertSame(Pesanan::STATUS_DIPROSES, $productOrder->fresh()->status);
        $this->assertTrue((bool) $productOrder->fresh()->stok_dikurangi);
        $this->assertSame(4, (int) $produk->fresh()->stok);
        $this->assertSame(4, (int) StokBatch::query()->where('produk_id', $produk->id)->value('sisa_qty'));

        $jenisRefill = JenisRefill::create([
            'nama' => 'Powder',
            'stok' => 10,
            'satuan' => 'kg',
            'harga' => 100000,
        ]);

        $refillOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'refill',
            'service_jenis_refill_id' => $jenisRefill->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '2 kg',
            'service_jumlah_unit' => 1,
            'service_total_kg' => 2,
            'service_metode_penanganan' => 'antar sendiri',
            'status' => Pesanan::STATUS_DIPROSES,
            'tanggal' => now()->toDateString(),
            'total' => 200000,
            'total_harga' => 200000,
            'service_estimasi_biaya' => 200000,
            'pembayaran_terkonfirmasi_at' => now(),
        ]);

        Service::create([
            'pesanan_id' => $refillOrder->id,
            'jenis_service' => 'Refill APAR',
            'tgl_service' => now()->toDateString(),
            'biaya' => 200000,
            'status_konfirmasi' => 'pending',
        ]);

        app(PaidOrderStockService::class)->apply($refillOrder->fresh(['service', 'serviceJenisRefill', 'pelanggan']));
        app(PaidOrderStockService::class)->apply($refillOrder->fresh(['service', 'serviceJenisRefill', 'pelanggan']));

        $this->assertTrue((bool) $refillOrder->fresh()->stok_dikurangi);
        $this->assertSame(8.0, (float) $jenisRefill->fresh()->stok);

        $paket = ServicePaket::create([
            'nama' => 'Service Ringan',
            'label' => 'Paket Ringan',
            'harga' => 250000,
        ]);

        $peralatan = Peralatan::create([
            'nama' => 'Valve APAR',
            'stok' => 6,
            'harga_standar' => 50000,
            'stok_minimum' => 1,
        ]);

        $serviceOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_paket_id' => $paket->id,
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '6 kg',
            'service_jumlah_unit' => 1,
            'service_metode_penanganan' => 'antar sendiri',
            'status' => Pesanan::STATUS_DIPROSES,
            'tanggal' => now()->toDateString(),
            'total' => 250000,
            'total_harga' => 250000,
            'service_estimasi_biaya' => 250000,
            'pembayaran_terkonfirmasi_at' => now(),
        ]);

        Service::create([
            'pesanan_id' => $serviceOrder->id,
            'service_paket_id' => $paket->id,
            'jenis_service' => $paket->nama,
            'tgl_service' => now()->toDateString(),
            'biaya' => 250000,
            'actual_peralatan_json' => json_encode([
                [
                    'peralatan_id' => $peralatan->id,
                    'nama' => $peralatan->nama,
                    'jumlah' => 2,
                ],
            ]),
            'status_konfirmasi' => 'pending',
        ]);

        app(PaidOrderStockService::class)->apply($serviceOrder->fresh(['service', 'servicePaket.peralatans', 'pelanggan']));
        app(PaidOrderStockService::class)->apply($serviceOrder->fresh(['service', 'servicePaket.peralatans', 'pelanggan']));

        $this->assertTrue((bool) $serviceOrder->fresh()->stok_dikurangi);
        $this->assertSame(4, (int) $peralatan->fresh()->stok);

        $hose = Peralatan::create([
            'nama' => 'Hose APAR',
            'stok' => 5,
            'harga_standar' => 35000,
            'stok_minimum' => 1,
        ]);

        $manualServiceOrder = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'service',
            'service_jenis_layanan' => 'service',
            'service_jenis_apar' => 'Powder',
            'service_ukuran_apar' => '3 kg',
            'service_jumlah_unit' => 1,
            'service_metode_penanganan' => 'antar sendiri',
            'service_keluhan' => implode("\n", [
                'Rincian Service Manual',
                '1. Hydrotest | 3 kg | 1 unit - Rp175.000',
                'Peralatan Paket:',
                '- Valve APAR x1',
                '- Hose APAR x2',
                'Total Service: Rp175.000',
                'Metode Penanganan: Antar Sendiri',
                'Catatan Pelanggan: -',
            ]),
            'status' => Pesanan::STATUS_DIPROSES,
            'tanggal' => now()->toDateString(),
            'total' => 175000,
            'total_harga' => 175000,
            'service_estimasi_biaya' => 175000,
            'pembayaran_terkonfirmasi_at' => now(),
        ]);

        app(PaidOrderStockService::class)->apply($manualServiceOrder->fresh(['service', 'pelanggan']));
        app(PaidOrderStockService::class)->apply($manualServiceOrder->fresh(['service', 'pelanggan']));

        $manualServiceLog = Service::query()->where('pesanan_id', $manualServiceOrder->id)->first();

        $this->assertTrue((bool) $manualServiceOrder->fresh()->stok_dikurangi);
        $this->assertNotNull($manualServiceLog);
        $this->assertNotEmpty($manualServiceLog?->stok_kurang_history);
        $this->assertSame(3, (int) $peralatan->fresh()->stok);
        $this->assertSame(3, (int) $hose->fresh()->stok);
    }

    public function test_ready_to_ship_flow_requires_customer_confirmation_before_final(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create(['role' => 'admin']);
        [$customerUser, $pelanggan] = $this->createCustomer('081234560004');
        $produk = $this->createProduct('APAR Ready Ship');

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'user_id' => $customerUser->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'status' => Pesanan::STATUS_SELESAI_OLEH_TEKNISI,
            'tanggal' => now()->toDateString(),
            'total' => 750000,
            'total_harga' => 750000,
            'metode_pengiriman' => 'diantar_internal',
            'pembayaran_terkonfirmasi_at' => now(),
            'teknisi_selesai_at' => now(),
        ]);

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 1,
            'harga' => 750000,
            'subtotal' => 750000,
        ]);

        app(PaidOrderStockService::class)->apply($pesanan->fresh([
            'details.produk.jenisApar',
            'pelanggan',
            'unitApars.produk',
        ]));

        $hiddenUnit = UnitApar::query()->where('pesanan_id', $pesanan->id)->firstOrFail();
        $this->assertNotNull($hiddenUnit->hidden_at);

        $readyResponse = $this->actingAs($admin)->post(route('admin.pesanan.konfirmasi-pelanggan', $pesanan));
        $readyResponse->assertRedirect();
        $this->assertSame(Pesanan::STATUS_SIAP_DIKIRIM, $pesanan->fresh()->status);

        $finalResponse = $this->actingAs($customerUser)->postJson(route('riwayat-apar.confirm-received', $pesanan));
        $finalResponse->assertOk()
            ->assertJson([
                'success' => true,
                'open_review' => true,
            ]);

        $pesanan->refresh();
        $this->assertSame(Pesanan::STATUS_SELESAI_FINAL, $pesanan->status);
        $this->assertNotNull($pesanan->customer_confirmed_at);
        $this->assertNull($hiddenUnit->fresh()->hidden_at);
    }

    private function createCustomer(string $phone): array
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan ' . substr($phone, -3),
            'no_telpon' => $phone,
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'no_wa' => $phone,
            'alamat' => 'Jl. Pelanggan No. 1',
            'alamat_maps' => 'Jl. Pelanggan No. 1',
            'alamat_detail' => 'Gudang belakang',
            'status' => 'tetap',
        ]);

        return [$user, $pelanggan];
    }

    private function createProduct(string $nama): Produk
    {
        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Serbaguna',
        ]);

        $produk = Produk::create([
            'nama' => $nama,
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 750000,
            'deskripsi' => 'Produk revisi workflow.',
            'stok' => 5,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 5,
            'sisa_qty' => 5,
            'tgl_produksi' => now()->subMonth()->toDateString(),
            'tgl_expired' => now()->addYear()->toDateString(),
            'keterangan' => 'Batch workflow test',
        ]);

        return $produk;
    }
}
