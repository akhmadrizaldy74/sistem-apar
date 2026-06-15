<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasePriceApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_purchase_price_request_and_use_final_price(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pesanan = $this->createProductOrderWithPurchaseRequest();

        $response = $this->actingAs($admin)->post(
            route('admin.pesanan.pengajuan-harga.acc', $pesanan),
            [
                'harga_final' => 'Rp 4.800.000',
                'catatan_admin' => 'Disetujui sesuai evaluasi admin.',
                'purchase_price_order_id' => $pesanan->id,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_DISETUJUI, $pesanan->status);
        $this->assertSame('deal', $pesanan->tipe_harga);
        $this->assertSame(Pesanan::PRICE_REQUEST_APPROVED, $pesanan->purchasePriceRequestStatus());
        $this->assertSame(4800000.0, (float) $pesanan->approvedPurchaseFinalPrice());
        $this->assertSame(4800000.0, (float) $pesanan->pricingSummary()['totalPembayaran']);
        $this->assertSame('Disetujui sesuai evaluasi admin.', $pesanan->catatan_admin);
    }

    public function test_admin_can_reject_purchase_price_request_and_order_returns_to_normal_total(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pesanan = $this->createProductOrderWithPurchaseRequest();

        $response = $this->actingAs($admin)->post(
            route('admin.pesanan.pengajuan-harga.tolak', $pesanan),
            [
                'catatan_admin' => 'Belum dapat disetujui.',
                'purchase_price_order_id' => $pesanan->id,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_PENDING, $pesanan->status);
        $this->assertSame('normal', $pesanan->tipe_harga);
        $this->assertNull($pesanan->approvedPurchaseFinalPrice());
        $this->assertSame(Pesanan::PRICE_REQUEST_REJECTED, $pesanan->purchasePriceRequestStatus());
        $this->assertSame(6000000.0, (float) $pesanan->pricingSummary()['totalPembayaran']);
        $this->assertSame('Belum dapat disetujui.', $pesanan->catatan_admin);
    }

    public function test_admin_index_includes_purchase_price_payload_for_detail_modal(): void
    {
        config(['broadcasting.default' => 'null']);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $pesanan = $this->createProductOrderWithPurchaseRequest();

        $response = $this->actingAs($admin)->get(route('admin.pesanan.index'));

        $response->assertOk();
        $response->assertSee(str_replace('/', '\\/', route('admin.pesanan.pengajuan-harga.acc', $pesanan)), false);
        $response->assertSee(str_replace('/', '\\/', route('admin.pesanan.pengajuan-harga.tolak', $pesanan)), false);
        $response->assertSee('5.500.000', false);
        $response->assertSee('Menunggu Persetujuan Harga');
    }

    private function createProductOrderWithPurchaseRequest(): Pesanan
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'PT Pelanggan Harga Khusus',
            'no_wa' => '081234567890',
            'alamat' => 'Jl. Industri No. 10',
            'status' => 'calon',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Powder 6 Kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 3000000,
            'deskripsi' => 'Produk uji pengajuan harga',
            'stok' => 10,
        ]);

        $pesanan = Pesanan::create([
            'pelanggan_id' => $pelanggan->id,
            'tipe' => 'produk',
            'sumber_pesanan' => 'website',
            'tanggal' => now()->toDateString(),
            'total' => 6000000,
            'total_harga' => 6000000,
            'status' => 'menunggu persetujuan',
            'tipe_harga' => 'normal',
            'metode_pengiriman' => 'pickup',
            'ongkir' => 0,
            'bank' => 'bca',
        ] + Pesanan::purchasePriceAttributes([
            'status' => Pesanan::PRICE_REQUEST_PENDING,
            'requested_price' => 5500000,
            'customer_note' => 'Mohon harga proyek untuk pembelian ini.',
            'final_price' => null,
            'used' => false,
        ]));

        PesananDetail::create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'merek' => $produk->merek,
            'kapasitas' => $produk->kapasitas,
            'jumlah' => 2,
            'harga' => 3000000,
            'subtotal' => 6000000,
        ]);

        return $pesanan->fresh('details.produk');
    }
}
