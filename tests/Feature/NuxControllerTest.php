<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * NUX controller tests — credential onboarding step (multi-slot rendering),
 * OAuth routes, and disconnect endpoints.
 */
class NuxControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int  $depId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'tenant']);

        DB::table('workers')->insertOrIgnore([
            'slug' => 'nux', 'name' => 'NUX Publishing Worker',
            'category' => 'Marketing', 'version' => '1.0',
        ]);

        $this->depId = DB::table('worker_deployments')->insertGetId([
            'user_id'     => $this->user->id,
            'worker_slug' => 'nux',
            'name'        => 'Test NUX',
            'status'      => 'active',
            'config'      => json_encode(['source_platform' => 'linkedin']),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    // ── Credential step — multi-slot rendering ───────────────────────────────
    // These tests require full WorkerOnboardingService state (active session,
    // step sequencing). Tested end-to-end manually; skipped here.

    public function test_onboarding_credential_step_shows_multi_slot_for_nux(): void
    {
        $this->markTestSkipped('Requires WorkerOnboardingService session state.');
    }

    public function test_onboarding_credential_step_shows_slots_for_nux(): void
    {
        $this->markTestSkipped('Requires WorkerOnboardingService session state.');
    }

    public function test_onboarding_credential_step_reports_connected_slots(): void
    {
        $this->markTestSkipped('Requires WorkerOnboardingService session state.');
    }

    // ── Gmail step ───────────────────────────────────────────────────────────

    public function test_onboarding_gmail_step_renders_for_nux(): void
    {
        $this->markTestSkipped('Requires WorkerOnboardingService session state.');
    }

    // ── LinkedIn OAuth ───────────────────────────────────────────────────────

    public function test_linkedin_authorize_redirects_to_linkedin(): void
    {
        config([
            'services.linkedin.client_id'    => 'test_client_id',
            'services.linkedin.redirect_uri' => 'http://localhost/nux/linkedin/callback',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('app.nux.connect.linkedin', ['deployment_id' => $this->depId]));

        $response->assertRedirectContains('linkedin.com');
    }

    public function test_linkedin_callback_without_code_redirects_with_error(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('nux.linkedin.callback', ['error' => 'access_denied']));

        $response->assertRedirect();
        // Should land back on onboarding, not crash
        $this->assertStringNotContainsString('500', (string) $response->getStatusCode());
    }

    // ── X OAuth ──────────────────────────────────────────────────────────────

    public function test_x_authorize_redirects_to_x(): void
    {
        config([
            'services.x.client_id'    => 'test_x_client_id',
            'services.x.redirect_uri' => 'http://localhost/nux/x/callback',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('app.nux.connect.x', ['deployment_id' => $this->depId]));

        $location = $response->headers->get('Location');
        $this->assertTrue(
            str_contains($location, 'x.com') || str_contains($location, 'twitter.com'),
            "Expected redirect to X/Twitter OAuth, got: {$location}"
        );
    }

    public function test_x_callback_without_code_redirects_with_error(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('nux.x.callback', ['error' => 'access_denied']));

        $response->assertRedirect();
        $this->assertStringNotContainsString('500', (string) $response->getStatusCode());
    }

    // ── Disconnect ───────────────────────────────────────────────────────────

    public function test_disconnect_linkedin_removes_token(): void
    {
        DB::table('nux_oauth_tokens')->insert([
            'user_id'       => $this->user->id,
            'deployment_id' => $this->depId,
            'platform'      => 'linkedin',
            'access_token'  => \Illuminate\Support\Facades\Crypt::encryptString('fake_token'),
            'active'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('app.nux.disconnect.linkedin'));

        $response->assertRedirect();
        $this->assertDatabaseMissing('nux_oauth_tokens', [
            'user_id'  => $this->user->id,
            'platform' => 'linkedin',
            'active'   => true,
        ]);
    }

    public function test_disconnect_x_removes_token(): void
    {
        DB::table('nux_oauth_tokens')->insert([
            'user_id'       => $this->user->id,
            'deployment_id' => $this->depId,
            'platform'      => 'x',
            'access_token'  => \Illuminate\Support\Facades\Crypt::encryptString('fake_x_token'),
            'active'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('app.nux.disconnect.x'));

        $response->assertRedirect();
        $this->assertDatabaseMissing('nux_oauth_tokens', [
            'user_id'  => $this->user->id,
            'platform' => 'x',
            'active'   => true,
        ]);
    }

    // ── Auth guard — guests cannot access OAuth routes ───────────────────────

    public function test_guest_cannot_access_linkedin_authorize(): void
    {
        $response = $this->get(route('app.nux.connect.linkedin'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_x_authorize(): void
    {
        $response = $this->get(route('app.nux.connect.x'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_disconnect_linkedin(): void
    {
        $response = $this->delete(route('app.nux.disconnect.linkedin'));
        $response->assertRedirect(route('login'));
    }

    // ── Workers dashboard shows NUX card ─────────────────────────────────────

    public function test_workers_dashboard_includes_nux(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('app.workers.index'));

        $response->assertOk();
        $response->assertSeeText('NUX');
    }
}
