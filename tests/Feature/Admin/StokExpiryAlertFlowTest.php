<?php

namespace Tests\Feature\Admin;

use App\Models\JenisApar;
use App\Models\JenisRefill;
use App\Models\Produk;
use App\Models\StokBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokExpiryAlertFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_can_open_problem_filter_and_update_expiry_without_changing_stock(): void
    {
        Carbon::setTestNow('2026-06-22');

        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111189',
        ]);

        $jenisApar = JenisApar::create([
            'nama' => 'Dry Chemical Powder',
        ]);

        $produk = Produk::create([
            'nama' => 'APAR TONATA Powder 2 kg',
            'merek' => 'TONATA',
            'jenis_apar_id' => $jenisApar->id,
            'kapasitas' => '2 kg',
            'penggunaan' => 'Gudang',
            'harga' => 618000,
            'stok' => 7,
        ]);

        JenisRefill::create([
            'nama' => 'Dry Chemical Powder',
            'stok' => 20,
            'satuan' => 'kg',
            'harga' => 100000,
            'stok_minimum' => 1,
        ]);

        $batchExpired = StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 4,
            'sisa_qty' => 4,
            'tgl_produksi' => '2025-06-21',
            'tgl_expired' => '2026-06-21',
            'keterangan' => 'Batch expired',
        ]);

        $batchHampirExpired = StokBatch::create([
            'produk_id' => $produk->id,
            'jumlah_masuk' => 3,
            'sisa_qty' => 3,
            'tgl_produksi' => '2025-06-26',
            'tgl_expired' => '2026-06-26',
            'keterangan' => 'Batch hampir expired',
        ]);

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSeeText('1 produk APAR perlu perhatian masa berlaku.');
        $dashboard->assertSeeText('0 produk hampir expired, 1 produk sudah expired.');
        $dashboard->assertSeeText('Status masa berlaku: Expired');
        $dashboard->assertSeeText('Sudah expired sejak 21 Juni 2026');
        $dashboard->assertDontSeeText('Sisa: Expired');
        $dashboard->assertSee('/admin/stok?', false);
        $dashboard->assertSee('filter=masa-berlaku', false);

        $stockPage = $this->actingAs($admin)->get(route('admin.stok.index', [
            'tab' => 'apar',
            'filter' => 'masa-berlaku',
        ]));

        $stockPage->assertOk();
        $stockPage->assertSeeText('APAR TONATA Powder 2 kg');
        $stockPage->assertSeeText('Perbarui Masa Berlaku');
        $stockPage->assertSeeText('Tambah Stok');
        $stockPage->assertDontSeeText('Buat Tugas Refill');

        $catalogBefore = $this->get(route('produk.index'));
        $catalogBefore->assertOk();
        $catalogBefore->assertDontSeeText('APAR TONATA Powder 2 kg');

        $response = $this->actingAs($admin)->post(route('admin.stok.batch.refill', $batchExpired), [
            'batch_id_refill' => $batchExpired->id,
            'tanggal_refill' => '2026-06-22',
            'keterangan' => 'Refill admin stok lama',
        ]);

        $response->assertRedirect(route('admin.stok.index', ['tab' => 'apar', 'filter' => 'masa-berlaku']));
        $response->assertSessionHas('success');

        $this->assertSame('2027-06-22', $batchExpired->fresh()->tgl_expired?->toDateString());
        $this->assertSame('2027-06-22', $batchHampirExpired->fresh()->tgl_expired?->toDateString());
        $this->assertSame(4, (int) $batchExpired->fresh()->sisa_qty);
        $this->assertSame(3, (int) $batchHampirExpired->fresh()->sisa_qty);
        $this->assertSame(7, (int) $produk->fresh()->catalog_ready_stock);

        $problemPageAfter = $this->actingAs($admin)->get(route('admin.stok.index', [
            'tab' => 'apar',
            'filter' => 'masa-berlaku',
        ]));

        $problemPageAfter->assertOk();
        $problemPageAfter->assertDontSeeText('APAR TONATA Powder 2 kg');

        $catalogAfter = $this->get(route('produk.index'));
        $catalogAfter->assertOk();
        $catalogAfter->assertSeeText('APAR TONATA Powder 2 kg');
    }
}
