<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class TrackWebsiteVisitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_request_still_succeeds_when_tracking_table_is_unavailable(): void
    {
        Schema::drop('website_visits');

        $this->get('/')
            ->assertOk();
    }

    public function test_root_visit_is_recorded_with_a_single_leading_slash(): void
    {
        $this->get('/')
            ->assertOk();

        $this->assertDatabaseHas('website_visits', [
            'page_url' => '/',
            'event_type' => 'page_view',
        ]);

        $this->assertDatabaseMissing('website_visits', [
            'page_url' => '//',
        ]);
    }

    public function test_tracking_cookie_uses_http_defaults_for_local_requests(): void
    {
        $this->get('/')
            ->assertOk();

        $visitorCookie = CookieFacade::queued('apar_visitor_id');

        $this->assertNotNull($visitorCookie);
        $this->assertInstanceOf(Cookie::class, $visitorCookie);
        $this->assertFalse($visitorCookie->isSecure());
        $this->assertTrue($visitorCookie->isHttpOnly());
    }
}
