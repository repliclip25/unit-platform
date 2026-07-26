<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnsureCanonicalHostTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // The middleware only acts in production — flip env for this test
        // only, without touching real .env or booting a second app instance.
        app()->detectEnvironment(fn () => 'production');
        config(['app.canonical_host' => 'unit.report']);
    }

    public function test_www_host_redirects_to_apex_canonical_host(): void
    {
        $response = $this->get('https://www.unit.report/terms');

        $response->assertStatus(301);
        $response->assertRedirect('https://unit.report/terms');
    }

    public function test_insecure_request_redirects_to_https(): void
    {
        $response = $this->get('http://unit.report/terms');

        $response->assertStatus(301);
        $response->assertRedirect('https://unit.report/terms');
    }

    public function test_canonical_host_over_https_passes_through_untouched(): void
    {
        $response = $this->get('https://unit.report/terms');

        $response->assertStatus(200);
    }

    public function test_middleware_is_inert_outside_production(): void
    {
        app()->detectEnvironment(fn () => 'local');

        $response = $this->get('http://www.unit.report/terms');

        $response->assertStatus(200);
    }
}
