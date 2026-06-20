<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\Pelanggan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurchasePriceNegotiationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_special_price_request_must_wait_for_admin_before_payment_and_invoice_then_can_pay_after_approval(): void
    {
        config(['broadcasting.default' => 'null']);
        Storage::fake('public');

        [$customerUser] = $this->createCustomerAccount('081234567811');
        $produk = $this->createNegotiationProduct();

        $pesanan = $this->submitSpecialPriceOrder($customerUser, $produk, 'Rp 5.000.000');

        $this->assertSame(Pesanan::STATUS_MENUNGGU_PERSETUJUAN_HARGA, $pesanan->status);
        $this->assertSame(Pesanan::PRICE_REQUEST_PENDING, $pesanan->purchasePriceRequestStatus());
        $this->assertSame(5000000.0, (float) $pesanan->requestedPurchasePrice());
        $this->assertFalse($pesanan->canPay());
        $this->assertTrue($pesanan->canViewInvoice());
        $this->assertSame(6000000.0, (float) $pesanan->purchasePriceInitialTotal());

        $paymentPage = $this->actingAs($customerUser)->get(route('order.payment', $pesanan));
        $paymentPage->assertRedirect(route('riwayat-apar'));
        $paymentPage->assertSessionHas('warning');

        $invoicePage = $this->actingAs($customerUser)->get(route('invoice.show', $pesanan));
        $invoicePage->assertOk();
        $invoicePage->assertSeeText('Menunggu Persetujuan Harga');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $adminDetail = $this->actingAs($admin)->get(route('admin.pesanan.show', $pesanan));
        $adminDetail->assertOk();
        $adminDetail->assertSeeText('Pengajuan Harga Pembelian');
        $adminDetail->assertSeeText('Total Awal');
        $adminDetail->assertSeeText('Harga Pengajuan Pelanggan');
        $adminDetail->assertSeeText('ACC Pengajuan');
        $adminDetail->assertSeeText('Tolak Pengajuan');

        $approveResponse = $this->actingAs($admin)->post(
            route('admin.pesanan.pengajuan-harga.acc', $pesanan),
            [
                'catatan_admin' => 'Disetujui sesuai penawaran pelanggan.',
            ]
        );

        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_DISETUJUI, $pesanan->status);
        $this->assertSame(Pesanan::PRICE_REQUEST_APPROVED, $pesanan->purchasePriceRequestStatus());
        $this->assertSame(5000000.0, (float) $pesanan->approvedPurchaseFinalPrice());
        $this->assertTrue($pesanan->canPay());
        $this->assertTrue($pesanan->canViewInvoice());
        $this->assertSame(5000000.0, (float) $pesanan->payableTotal());

        $historyPage = $this->actingAs($customerUser)->get(route('riwayat-apar'));
        $historyPage->assertOk();
        $historyPage->assertSeeText('Harga Disetujui');
        $historyPage->assertSeeText('Rp 5.000.000');
        $historyPage->assertSeeText('Lihat Invoice');
        $historyPage->assertSeeText('Bayar');

        $invoiceAfterApproval = $this->actingAs($customerUser)->get(route('invoice.show', $pesanan));
        $invoiceAfterApproval->assertOk();
        $invoiceAfterApproval->assertSeeText('Rp 5.000.000');

        $paymentAfterApproval = $this->actingAs($customerUser)->get(route('order.payment', $pesanan));
        $paymentAfterApproval->assertOk();
        $paymentAfterApproval->assertSeeText('Rp 5.000.000');

        $payResponse = $this->actingAs($customerUser)->post(route('order.payment.store', $pesanan), [
            'metode_pembayaran' => 'transfer',
            'bank' => 'bca',
            'bukti_pembayaran' => UploadedFile::fake()->image('bukti-nego.jpg'),
        ]);

        $payResponse->assertRedirect(route('home'));
        $payResponse->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_DIPROSES, $pesanan->status);
        $this->assertSame(5000000.0, (float) $pesanan->total);
        $this->assertSame(5000000.0, (float) $pesanan->total_harga);
        $this->assertSame('deal', $pesanan->tipe_harga);
        $this->assertNotNull($pesanan->kode_nego_terpakai_at);
        $this->assertNotNull($pesanan->bukti_pembayaran);
        Storage::disk('public')->assertExists($pesanan->bukti_pembayaran);
    }

    public function test_rejected_special_price_request_returns_customer_to_normal_amount_and_reopens_payment(): void
    {
        config(['broadcasting.default' => 'null']);

        [$customerUser] = $this->createCustomerAccount('081234567822');
        $produk = $this->createNegotiationProduct('APAR Powder 9 Kg');
        $pesanan = $this->submitSpecialPriceOrder($customerUser, $produk, 'Rp 5.100.000');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $rejectResponse = $this->actingAs($admin)->post(
            route('admin.pesanan.pengajuan-harga.tolak', $pesanan),
            [
                'catatan_admin' => 'Belum dapat disetujui. Gunakan harga normal.',
            ]
        );

        $rejectResponse->assertRedirect();
        $rejectResponse->assertSessionHas('success');

        $pesanan->refresh();

        $this->assertSame(Pesanan::STATUS_PENDING, $pesanan->status);
        $this->assertSame(Pesanan::PRICE_REQUEST_REJECTED, $pesanan->purchasePriceRequestStatus());
        $this->assertNull($pesanan->approvedPurchaseFinalPrice());
        $this->assertTrue($pesanan->canPay());
        $this->assertTrue($pesanan->canViewInvoice());
        $this->assertSame(6000000.0, (float) $pesanan->payableTotal());

        $historyPage = $this->actingAs($customerUser)->get(route('riwayat-apar'));
        $historyPage->assertOk();
        $historyPage->assertSeeText('Pengajuan Ditolak');
        $historyPage->assertSeeText('Rp 6.000.000');
        $historyPage->assertSeeText('Lihat Invoice');
        $historyPage->assertSeeText('Bayar');

        $invoicePage = $this->actingAs($customerUser)->get(route('invoice.show', $pesanan));
        $invoicePage->assertOk();
        $invoicePage->assertSeeText('Rp 6.000.000');

        $paymentPage = $this->actingAs($customerUser)->get(route('order.payment', $pesanan));
        $paymentPage->assertOk();
        $paymentPage->assertSeeText('Rp 6.000.000');
    }

    private function createCustomerAccount(string $phone): array
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
            'name' => 'Pelanggan Negosiasi ' . substr($phone, -3),
            'no_telpon' => $phone,
        ]);

        $pelanggan = Pelanggan::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'no_wa' => $phone,
            'alamat' => 'Jl. Negosiasi No. 1',
            'alamat_maps' => 'Jl. Negosiasi No. 1',
            'alamat_detail' => 'Gudang belakang',
            'status' => 'tetap',
        ]);

        return [$user, $pelanggan];
    }

    private function createNegotiationProduct(string $nama = 'APAR Powder 6 Kg'): Produk
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
            'penggunaan' => 'Gudang dan kantor',
            'harga' => 3000000,
            'deskripsi' => 'Produk uji negosiasi harga.',
            'stok' => 20,
        ]);

        StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 20,
            'sisa_qty' => 20,
            'tgl_produksi' => now()->subWeeks(2),
            'tgl_expired' => now()->addYear(),
            'keterangan' => 'Batch negosiasi',
        ]);

        return $produk;
    }

    private function submitSpecialPriceOrder(User $user, Produk $produk, string $hargaPengajuan): Pesanan
    {
        $response = $this->actingAs($user)->post(route('order.store'), [
            'tipe_layanan' => 'beli',
            'metode_pengiriman' => 'pickup',
            'bank_tujuan' => 'bca',
            'submit_source' => 'special_price_request',
            'harga_pengajuan' => $hargaPengajuan,
            'catatan_pelanggan' => 'Mohon harga proyek untuk pembelian ini.',
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2,
                ],
            ],
        ]);

        $response->assertRedirect(route('riwayat-apar'));
        $response->assertSessionHas('success');

        return Pesanan::query()->latest('id')->firstOrFail()->fresh(['details.produk', 'pelanggan']);
    }
}
