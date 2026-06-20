<?php

namespace Tests\Feature;

use App\Models\JenisApar;
use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProductPricesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_product_prices_command_only_updates_matching_product_prices(): void
    {
        $powder = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
            'deskripsi' => 'Powder',
        ]);

        $co2 = JenisApar::create([
            'nama' => 'Carbon Dioxide (CO2)',
            'deskripsi' => 'CO2',
        ]);

        $foam = JenisApar::create([
            'nama' => 'Liquid Foam (Busa)',
            'deskripsi' => 'Foam',
        ]);

        $firefix = Produk::create([
            'nama' => 'APAR FIREFIX Powder 1 kg',
            'merek' => 'FIREFIX',
            'jenis_apar_id' => $powder->id,
            'kapasitas' => '1 kg',
            'penggunaan' => 'Perkantoran',
            'harga' => 150000,
            'deskripsi' => 'Produk uji',
        ]);

        $guardallGeneric = Produk::create([
            'nama' => 'APAR Powder 2 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $powder->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 300000,
            'deskripsi' => 'Produk uji generik',
        ]);

        $tonataAlias = Produk::create([
            'nama' => 'APAR TONATA CO2 6.8 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $co2->id,
            'kapasitas' => '6.8 kg',
            'penggunaan' => 'Panel listrik',
            'harga' => 1092500,
            'deskripsi' => 'Produk uji alias ukuran',
        ]);

        $guardallFoam = Produk::create([
            'nama' => 'APAR GuardALL Foam 9 kg',
            'merek' => 'GuardALL',
            'jenis_apar_id' => $foam->id,
            'kapasitas' => '9 kg',
            'penggunaan' => 'SPBU',
            'harga' => 702000,
            'deskripsi' => 'Produk uji foam',
        ]);

        $unknown = Produk::create([
            'nama' => 'APAR WORKPAL Powder 6 kg',
            'merek' => 'WORKPAL',
            'jenis_apar_id' => $powder->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Pabrik',
            'harga' => 555000,
            'deskripsi' => 'Produk tanpa referensi',
        ]);

        $this->artisan('update:product-prices --dry-run')
            ->assertExitCode(0);

        $this->assertSame(150000.0, (float) $firefix->fresh()->harga);
        $this->assertSame(300000.0, (float) $guardallGeneric->fresh()->harga);
        $this->assertSame(1092500.0, (float) $tonataAlias->fresh()->harga);
        $this->assertSame(702000.0, (float) $guardallFoam->fresh()->harga);
        $this->assertSame(555000.0, (float) $unknown->fresh()->harga);

        $this->artisan('update:product-prices')
            ->assertExitCode(0);

        $this->assertSame(181624.0, (float) $firefix->fresh()->harga);
        $this->assertSame(678321.0, (float) $guardallGeneric->fresh()->harga);
        $this->assertSame(2607000.0, (float) $tonataAlias->fresh()->harga);
        $this->assertSame(2242228.0, (float) $guardallFoam->fresh()->harga);
        $this->assertSame(555000.0, (float) $unknown->fresh()->harga);
    }
}
