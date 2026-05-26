<?php

namespace Tests\Unit;

use App\Models\Peralatan;
use App\Models\ServicePaket;
use App\Services\ServicePackagePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePackagePricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_package_price_varies_by_size_and_media(): void
    {
        $service = app(ServicePackagePricingService::class);

        $paketC = ServicePaket::create([
            'nama' => 'Service Lengkap',
            'label' => 'Paket C',
            'harga' => 150000,
        ]);

        $powder2Kg = $service->resolvePackagePrice($paketC, 'Dry Chemical Powder', '2 kg');
        $powder4Kg = $service->resolvePackagePrice($paketC, 'Dry Chemical Powder', '4 kg');
        $co22Kg = $service->resolvePackagePrice($paketC, 'CO2', '2 kg');

        $this->assertSame(100000.0, $powder2Kg);
        $this->assertSame(150000.0, $powder4Kg);
        $this->assertGreaterThan($powder2Kg, $powder4Kg);
        $this->assertGreaterThan($powder2Kg, $co22Kg);
    }

    public function test_package_equipment_is_cumulative_and_uses_master_peralatan(): void
    {
        $service = app(ServicePackagePricingService::class);

        Peralatan::create(['nama' => 'Safety Pin APAR', 'stok' => 20, 'harga_standar' => 5000, 'stok_minimum' => 3]);
        Peralatan::create(['nama' => 'Selang APAR', 'stok' => 20, 'harga_standar' => 35000, 'stok_minimum' => 3]);
        Peralatan::create(['nama' => 'Valve APAR', 'stok' => 20, 'harga_standar' => 40000, 'stok_minimum' => 3]);

        $paketA = ServicePaket::create(['nama' => 'Service Ringan', 'label' => 'Paket A', 'harga' => 40000]);
        $paketB = ServicePaket::create(['nama' => 'Service Standar', 'label' => 'Paket B', 'harga' => 90000]);
        $paketC = ServicePaket::create(['nama' => 'Service Lengkap', 'label' => 'Paket C', 'harga' => 150000]);

        $itemsA = collect($service->resolveEstimatedPeralatan($paketA, 1));
        $itemsB = collect($service->resolveEstimatedPeralatan($paketB, 1));
        $itemsC = collect($service->resolveEstimatedPeralatan($paketC, 2));

        $this->assertTrue($itemsA->contains(fn (array $item) => $item['nama'] === 'Safety Pin APAR'));
        $this->assertFalse($itemsA->contains(fn (array $item) => $item['nama'] === 'Valve APAR'));
        $this->assertTrue($itemsB->contains(fn (array $item) => $item['nama'] === 'Safety Pin APAR'));
        $this->assertTrue($itemsB->contains(fn (array $item) => $item['nama'] === 'Selang APAR'));
        $this->assertTrue($itemsC->contains(fn (array $item) => $item['nama'] === 'Valve APAR' && (int) $item['jumlah'] === 2));
        $this->assertCount(3, $itemsC);
    }
}
