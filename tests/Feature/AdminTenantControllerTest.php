<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTenantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin  = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    // ── middleware guard ───────────────────────────────────────────────────────

    public function test_admin_routes_redirect_guests(): void
    {
        $this->get(route('admin.tenants'))->assertRedirect('/login');
    }

    public function test_admin_routes_reject_tenant_users(): void
    {
        $this->actingAs($this->tenant)
            ->get(route('admin.tenants'))
            ->assertForbidden();
    }

    // ── index ─────────────────────────────────────────────────────────────────

    public function test_index_loads_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.tenants'))
            ->assertOk()
            ->assertViewIs('admin.tenants');
    }

    // ── block / unblock ───────────────────────────────────────────────────────

    public function test_block_sets_blocked_at_and_reason(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tenants.block', $this->tenant->id), [
                'reason'      => 'Policy violation',
                'policy_code' => 'ACCOUNT_SUSPENDED',
            ])
            ->assertRedirect();

        $fresh = DB::table('users')->where('id', $this->tenant->id)->first();
        $this->assertNotNull($fresh->blocked_at);
        $this->assertEquals('Policy violation', $fresh->block_reason);
    }

    public function test_unblock_clears_blocked_at(): void
    {
        DB::table('users')->where('id', $this->tenant->id)->update([
            'blocked_at'   => now(),
            'block_reason' => 'Test',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.tenants.unblock', $this->tenant->id))
            ->assertRedirect();

        $fresh = DB::table('users')->where('id', $this->tenant->id)->first();
        $this->assertNull($fresh->blocked_at);
        $this->assertNull($fresh->block_reason);
    }

    // ── spend cap ─────────────────────────────────────────────────────────────

    public function test_set_spend_cap_updates_user(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.tenants.spend-cap', $this->tenant->id), ['cap' => 50.00])
            ->assertRedirect();

        $this->assertEquals(50.00, (float) DB::table('users')->where('id', $this->tenant->id)->value('monthly_spend_cap'));
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_show_loads_tenant_detail(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('AdminTenantController::show uses DATE_FORMAT (MySQL only).');
        }

        $this->actingAs($this->admin)
            ->get(route('admin.tenants.show', $this->tenant->id))
            ->assertOk()
            ->assertViewIs('admin.tenant-detail');
    }

    public function test_show_404s_for_missing_tenant(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('AdminTenantController::show uses DATE_FORMAT (MySQL only).');
        }

        $this->actingAs($this->admin)
            ->get(route('admin.tenants.show', 99999))
            ->assertNotFound();
    }

    // ── reset trial ───────────────────────────────────────────────────────────

    public function test_reset_trial_zeroes_trial_usage(): void
    {
        DB::table('workers')->insert([
            'slug' => 'ava', 'name' => 'AVA', 'category' => 'Operations', 'version' => '1.0',
        ]);
        $depId = DB::table('worker_deployments')->insertGetId([
            'user_id'     => $this->tenant->id,
            'worker_slug' => 'ava',
            'name'        => 'AVA',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        DB::table('deployment_billing')->insert([
            'deployment_id'             => $depId,
            'user_id'                   => $this->tenant->id,
            'worker_slug'               => 'ava',
            'status'                    => 'trial',
            'trial_transactions_used'   => 8,
            'trial_transactions_limit'  => 10,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.tenants.reset-trial', $this->tenant->id), ['deployment_id' => $depId])
            ->assertRedirect();

        $billing = DB::table('deployment_billing')->where('deployment_id', $depId)->first();
        $this->assertEquals(0, $billing->trial_transactions_used);
    }
}
