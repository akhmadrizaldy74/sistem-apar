<?php

namespace Tests\Unit;

use App\Models\Pesanan;
use Tests\TestCase;

class PesananPurchasePriceCompatibilityTest extends TestCase
{
    public function test_it_recognizes_pending_purchase_price_request_from_current_columns(): void
    {
        $pesanan = new Pesanan([
            'tipe' => 'produk',
            'status' => 'menunggu persetujuan',
            'harga_pengajuan' => 4250000,
            'status_pengajuan_harga' => 'pending',
            'catatan_pengajuan_harga' => 'Mohon harga khusus untuk pembelian proyek.',
        ]);

        $this->assertTrue($pesanan->hasPurchasePriceRequest());
        $this->assertTrue($pesanan->hasPendingPurchasePriceRequest());
        $this->assertSame(4250000.0, $pesanan->requestedPurchasePrice());
        $this->assertSame('Mohon harga khusus untuk pembelian proyek.', $pesanan->purchasePriceCustomerNote());
    }

    public function test_it_recognizes_approved_purchase_price_request_from_current_columns(): void
    {
        $pesanan = new Pesanan([
            'tipe' => 'produk',
            'status' => Pesanan::STATUS_DISETUJUI,
            'harga_pengajuan' => 4250000,
            'harga_final' => 4000000,
            'status_pengajuan_harga' => 'approved',
            'harga_khusus_digunakan' => true,
        ]);

        $this->assertTrue($pesanan->hasPurchasePriceRequest());
        $this->assertTrue($pesanan->hasApprovedPurchasePriceRequest());
        $this->assertSame(4000000.0, $pesanan->approvedPurchaseFinalPrice());
        $this->assertSame(Pesanan::PRICE_REQUEST_APPROVED, $pesanan->purchasePriceRequestStatus());
    }
}
