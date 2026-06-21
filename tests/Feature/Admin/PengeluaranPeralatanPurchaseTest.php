<?php

namespace Tests\Feature\Admin;

use App\Models\Pengeluaran;
use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranPeralatanPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengeluaran_page_shows_master_and_actual_price_fields_for_peralatan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Peralatan::create([
            'nama' => 'Selang APAR Powder/Foam',
            'stok' => 10,
            'stok_minimum' => 3,
            'harga_standar' => 35000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pengeluaran.index', [
            'open' => 1,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
        ]));

        $response->assertOk();
        $response->assertSeeText('Harga Standar Master');
        $response->assertSeeText('Harga Beli Aktual');
        $response->assertSeeText('Pilih Peralatan');
    }

    public function test_peralatan_purchase_uses_actual_purchase_price_and_increases_stock(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $peralatan = Peralatan::create([
            'nama' => 'Selang APAR Powder/Foam',
            'stok' => 50,
            'stok_minimum' => 3,
            'harga_standar' => 35000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengeluaran.store'), [
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'peralatan_id' => $peralatan->id,
            'qty' => 5,
            'harga_beli' => 40000,
            'tanggal' => '2026-06-21',
            'keterangan' => 'Pembelian supplier A dengan harga promo berbeda dari master.',
        ]);

        $response->assertRedirect(route('admin.pengeluaran.index'));

        $expense = Pengeluaran::query()->where('peralatan_id', $peralatan->id)->first();

        $this->assertNotNull($expense);
        $this->assertSame(5.0, (float) $expense->qty);
        $this->assertSame(40000.0, (float) $expense->harga_beli);
        $this->assertSame(200000.0, (float) $expense->total);
        $this->assertSame(200000.0, (float) $expense->nominal);
        $this->assertSame(55, (int) $peralatan->fresh()->stok);

        $this->assertDatabaseHas('pengeluarans', [
            'id' => $expense->id,
            'jenis_pengeluaran' => Pengeluaran::JENIS_PEMBELIAN_PERALATAN,
            'peralatan_id' => $peralatan->id,
            'harga_beli' => 40000,
            'total' => 200000,
            'nominal' => 200000,
        ]);
    }
}
