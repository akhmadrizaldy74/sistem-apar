<?php

namespace Tests\Unit;

use App\Models\Pesanan;
use App\Models\PesananDetail;
use App\Services\OrderPricingService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrderPricingServiceTest extends TestCase
{
    public function test_it_calculates_bulk_discount_summary_for_product_items(): void
    {
        $service = app(OrderPricingService::class);

        $summary = $service->summarizeProductItems([
            [
                'produk_id' => 1,
                'jumlah' => 17,
                'harga' => 172500,
            ],
        ]);

        $this->assertSame(2932500.0, $summary['subtotalProduk']);
        $this->assertSame(17, $summary['totalUnit']);
        $this->assertSame(10, $summary['diskonPersen']);
        $this->assertSame(293250.0, $summary['nominalDiskon']);
        $this->assertEquals(0.0, $summary['ongkir']);
        $this->assertSame(2639250.0, $summary['totalPembayaran']);
        $this->assertSame(3, $summary['nextDiscountUnitsNeeded']);
        $this->assertSame(15, $summary['nextDiscountPercent']);
    }

    public function test_it_summarizes_product_order_from_details_and_model_total_uses_same_service(): void
    {
        $pesanan = new Pesanan([
            'tipe' => 'produk',
            'ongkir' => 0,
            'total' => 0,
            'total_harga' => 0,
        ]);

        $pesanan->setRelation('details', new Collection([
            new PesananDetail([
                'produk_id' => 1,
                'jumlah' => 17,
                'harga' => 172500,
                'subtotal' => 2932500,
            ]),
        ]));

        $summary = $pesanan->pricingSummary();

        $this->assertSame(2932500.0, $summary['subtotalProduk']);
        $this->assertSame(17, $summary['totalUnit']);
        $this->assertSame(10, $summary['diskonPersen']);
        $this->assertSame(293250.0, $summary['nominalDiskon']);
        $this->assertSame(2639250.0, $summary['totalPembayaran']);
        $this->assertSame(2639250.0, $pesanan->payableTotal());
    }
}
