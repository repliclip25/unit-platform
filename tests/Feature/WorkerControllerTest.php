<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'tenant']);

        DB::table('workers')->insert([
            'slug'     => 'ava',
            'name'     => 'AVA Renewal Coordinator',
            'category' => 'Operations',
            'version'  => '1.0',
        ]);
    }

    private function makeDeployment(array $overrides = []): int
    {
        return DB::table('worker_deployments')->insertGetId(array_merge([
            'user_id'     => $this->user->id,
            'worker_slug' => 'ava',
            'name'        => 'Test AVA',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    // ── index ─────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('workers.deploy'))->assertRedirect('/login');
    }

    public function test_index_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user)
            ->get(route('workers.deploy'))
            ->assertOk()
            ->assertViewIs('dashboard.workers');
    }

    // ── store (deploy) ────────────────────────────────────────────────────────

    public function test_store_rejects_blocked_users(): void
    {
        DB::table('users')->where('id', $this->user->id)->update(['blocked_at' => now()]);
        $this->user->refresh();

        $this->actingAs($this->user)
            ->post(route('workers.store'), ['worker_slug' => 'ava', 'name' => 'My AVA'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('worker_deployments', 0);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('workers.store'), [])
            ->assertSessionHasErrors(['worker_slug', 'name']);
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_removes_own_deployment(): void
    {
        $id = $this->makeDeployment();

        $this->actingAs($this->user)
            ->delete(route('workers.destroy', $id))
            ->assertRedirect(route('workers.deploy'));

        $this->assertNull(DB::table('worker_deployments')->where('id', $id)->first());
    }

    public function test_destroy_cannot_remove_other_users_deployment(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $id    = DB::table('worker_deployments')->insertGetId([
            'user_id'     => $other->id,
            'worker_slug' => 'ava',
            'name'        => 'Other AVA',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->actingAs($this->user)
            ->delete(route('workers.destroy', $id));

        // Row should still exist
        $this->assertNotNull(DB::table('worker_deployments')->where('id', $id)->first());
    }

    // ── updateStatus ──────────────────────────────────────────────────────────

    public function test_update_status_pauses_active_worker(): void
    {
        $id = $this->makeDeployment(['status' => 'active']);

        $this->actingAs($this->user)
            ->patch(route('workers.status', $id), ['status' => 'paused'])
            ->assertRedirect();

        $this->assertEquals('paused', DB::table('worker_deployments')->where('id', $id)->value('status'));
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $id = $this->makeDeployment();

        $this->actingAs($this->user)
            ->patch(route('workers.status', $id), ['status' => 'deleted'])
            ->assertSessionHasErrors('status');

        $this->assertEquals('active', DB::table('worker_deployments')->where('id', $id)->value('status'));
    }

    public function test_update_status_only_affects_own_deployment(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        $id    = DB::table('worker_deployments')->insertGetId([
            'user_id'     => $other->id,
            'worker_slug' => 'ava',
            'name'        => 'Other AVA',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->actingAs($this->user)
            ->patch(route('workers.status', $id), ['status' => 'paused']);

        // Should remain active since it belongs to $other
        $this->assertEquals('active', DB::table('worker_deployments')->where('id', $id)->value('status'));
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_show_loads_worker_detail(): void
    {
        $this->makeDeployment();

        $this->actingAs($this->user)
            ->get(route('workers.show', 'ava'))
            ->assertOk()
            ->assertViewIs('dashboard.worker-detail');
    }

    public function test_show_404s_for_wrong_user(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        DB::table('worker_deployments')->insert([
            'user_id'     => $other->id,
            'worker_slug' => 'ava',
            'name'        => 'Other AVA',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('workers.show', 'ava'))
            ->assertNotFound();
    }
}
