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

    public function test_service_package_uses_standard_price_for_every_media_and_size(): void
    {
        $service = app(ServicePackagePricingService::class);

        $paket = ServicePaket::create([
            'nama' => 'Ganti Valve APAR',
            'label' => 'Valve',
            'harga' => 100000,
        ]);

        $powder2Kg = $service->resolvePackagePrice($paket, 'Dry Chemical Powder', '2 kg');
        $powder6Kg = $service->resolvePackagePrice($paket, 'Dry Chemical Powder', '6 kg');
        $co22Kg = $service->resolvePackagePrice($paket, 'CO2', '2 kg');

        $this->assertSame(100000.0, $powder2Kg);
        $this->assertSame(100000.0, $powder6Kg);
        $this->assertSame(100000.0, $co22Kg);
    }

    public function test_package_equipment_is_cumulative_and_uses_explicit_master_peralatan_relations(): void
    {
        $service = app(ServicePackagePricingService::class);

        $valve = Peralatan::create(['nama' => 'Valve APAR', 'stok' => 20, 'harga_standar' => 50000, 'stok_minimum' => 3]);
        $seal = Peralatan::create(['nama' => 'O-Ring/Karet Seal', 'stok' => 20, 'harga_standar' => 5000, 'stok_minimum' => 3]);
        $segel = Peralatan::create(['nama' => 'Segel Pengaman Plastik', 'stok' => 20, 'harga_standar' => 5000, 'stok_minimum' => 3]);

        $paket = ServicePaket::create(['nama' => 'Ganti Valve APAR', 'label' => 'Valve', 'harga' => 100000]);
        $paket->peralatans()->attach([
            $valve->id => ['jumlah_estimasi' => 1],
            $seal->id => ['jumlah_estimasi' => 1],
            $segel->id => ['jumlah_estimasi' => 1],
        ]);

        $items = collect($service->resolveEstimatedPeralatan($paket, 2));

        $this->assertCount(3, $items);
        $this->assertTrue($items->contains(fn (array $item) => $item['nama'] === 'Valve APAR' && (int) $item['jumlah'] === 2));
        $this->assertTrue($items->contains(fn (array $item) => $item['nama'] === 'O-Ring/Karet Seal' && (int) $item['jumlah'] === 2));
        $this->assertTrue($items->contains(fn (array $item) => $item['nama'] === 'Segel Pengaman Plastik' && (int) $item['jumlah'] === 2));
    }
}
