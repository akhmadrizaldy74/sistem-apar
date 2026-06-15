<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\UnitApar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranProductionDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_apar_purchase_uses_explicit_production_date_for_batch_expiry(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Foam',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR Foam 6 KG',
            'merek' => 'SAFE',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '6 kg',
            'penggunaan' => 'Gudang',
            'harga' => 950000,
            'deskripsi' => 'Produk test produksi batch',
            'stok' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => 'pembelian_apar',
            'produk_id' => $produk->id,
            'qty' => 2,
            'harga_beli' => 800000,
            'tanggal' => '2026-06-15',
            'tgl_produksi_apar' => '2026-05-01',
            'keterangan' => 'Pembelian batch foam baru',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));

        $batch = StokBatch::query()->where('produk_id', $produk->id)->first();

        $this->assertNotNull($batch);
        $this->assertSame('2026-05-01', $batch->tgl_produksi->toDateString());
        $this->assertSame(
            UnitApar::calculateExpiry('2026-05-01', '6 kg', 'Foam')->toDateString(),
            $batch->tgl_expired->toDateString()
        );
        $this->assertSame(2, (int) $produk->fresh()->stok);
        $this->assertSame(2, (int) $batch->sisa_qty);
    }
}
