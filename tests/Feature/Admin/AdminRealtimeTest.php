<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRealtimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_realtime_endpoints_return_json_snapshots(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'no_telpon' => '081111111240',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.realtime.dashboard'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['kpi_html', 'priority_html', 'product_expiry_html']);

        $this->actingAs($admin)
            ->getJson(route('admin.realtime.pesanan'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['summary_html', 'active_rows_html', 'history_rows_html', 'detail_data']);

        $this->actingAs($admin)
            ->getJson(route('admin.realtime.pelanggan'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['summary_html', 'desktop_rows_html', 'mobile_rows_html', 'pagination_html']);

        $this->actingAs($admin)
            ->getJson(route('admin.realtime.complain'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['counts_html', 'rows_html', 'pagination_html']);

        $this->actingAs($admin)
            ->getJson(route('admin.realtime.testimoni'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['counts_html', 'rows_html', 'pagination_html']);
    }
}
