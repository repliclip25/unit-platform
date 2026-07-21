<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'tenant']);

        // Every test needs at least one worker row for the FK
        DB::table('workers')->insert([
            'slug'     => 'ava',
            'name'     => 'AVA',
            'category' => 'Operations',
            'version'  => '1.0',
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function makeTx(array $overrides = []): string
    {
        $txId = $overrides['tx_id'] ?? 'TX-' . uniqid();
        DB::table('transactions')->insert(array_merge([
            'tx_id'       => $txId,
            'user_id'     => $this->user->id,
            'worker_slug' => 'ava',
            'status'      => 'failed',
            'raw_input'   => json_encode(['source' => 'gmail']),
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
        return $txId;
    }

    // ── index ─────────────────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('app.transactions'))->assertRedirect('/login');
    }

    public function test_index_shows_transactions_for_authenticated_user(): void
    {
        $this->makeTx(['status' => 'draft_ready']);
        $this->makeTx(['status' => 'dismissed']); // should be hidden by default

        $this->actingAs($this->user)
            ->get(route('app.transactions'))
            ->assertOk()
            ->assertViewIs('dashboard.transactions');
    }

    public function test_index_filter_draft_ready(): void
    {
        $this->makeTx(['tx_id' => 'TX-DRAFT', 'status' => 'draft_ready']);
        $this->makeTx(['tx_id' => 'TX-FAIL',  'status' => 'failed']);

        $response = $this->actingAs($this->user)
            ->get(route('app.transactions', ['filter' => 'draft_ready']));

        $response->assertOk();
        $txns = $response->viewData('transactions');
        $this->assertTrue($txns->every(fn($t) => $t->status === 'draft_ready'));
    }

    public function test_index_does_not_show_other_user_transactions(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        DB::table('transactions')->insert([
            'tx_id'       => 'TX-OTHER',
            'user_id'     => $other->id,
            'worker_slug' => 'ava',
            'status'      => 'draft_ready',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('app.transactions'));

        $txns = $response->viewData('transactions');
        $this->assertCount(0, $txns);
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_show_returns_transaction_detail(): void
    {
        $txId = $this->makeTx(['status' => 'draft_ready']);

        $this->actingAs($this->user)
            ->get(route('app.transactions.show', $txId))
            ->assertOk()
            ->assertViewIs('dashboard.transaction-detail');
    }

    public function test_show_404s_for_other_user_transaction(): void
    {
        $other = User::factory()->create(['role' => 'tenant']);
        DB::table('transactions')->insert([
            'tx_id'       => 'TX-OTHER',
            'user_id'     => $other->id,
            'worker_slug' => 'ava',
            'status'      => 'received',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->actingAs($this->user)
            ->get(route('app.transactions.show', 'TX-OTHER'))
            ->assertNotFound();
    }

    // ── status (polling endpoint) ─────────────────────────────────────────────

    public function test_status_returns_json_for_in_progress_tx(): void
    {
        $txId = $this->makeTx(['status' => 'reading']);

        $this->actingAs($this->user)
            ->getJson(route('app.transactions.status', $txId))
            ->assertOk()
            ->assertJson(['status' => 'reading', 'done' => false, 'failed' => false]);
    }

    public function test_status_marks_draft_ready_as_done(): void
    {
        $txId = $this->makeTx(['status' => 'draft_ready']);

        $this->actingAs($this->user)
            ->getJson(route('app.transactions.status', $txId))
            ->assertJson(['done' => true, 'failed' => false]);
    }

    public function test_status_marks_failed_as_done_and_failed(): void
    {
        $txId = $this->makeTx(['status' => 'failed']);

        $this->actingAs($this->user)
            ->getJson(route('app.transactions.status', $txId))
            ->assertJson(['done' => true, 'failed' => true]);
    }

    public function test_status_returns_404_for_unknown_tx(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('app.transactions.status', 'TX-NOPE'))
            ->assertNotFound();
    }

    // ── refire ────────────────────────────────────────────────────────────────

    public function test_refire_resets_failed_tx_and_dispatches_job(): void
    {
        Queue::fake();
        $txId = $this->makeTx(['status' => 'failed', 'read_output' => json_encode(['x' => 1])]);

        DB::table('worker_deployments')->insert([
            'id'          => 1,
            'user_id'     => $this->user->id,
            'worker_slug' => 'ava',
            'name'        => 'Test',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        DB::table('transactions')->where('tx_id', $txId)->update(['deployment_id' => 1]);

        $this->actingAs($this->user)
            ->post(route('app.transactions.refire', $txId))
            ->assertRedirect();

        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        $this->assertEquals('received', $tx->status);
        $this->assertNull($tx->read_output);

        Queue::assertPushed(\App\Workers\AVA\Jobs\FilterEmailJob::class);
    }

    public function test_refire_rejects_non_failed_transactions(): void
    {
        $txId = $this->makeTx(['status' => 'draft_ready']);

        $this->actingAs($this->user)
            ->post(route('app.transactions.refire', $txId))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals('draft_ready', DB::table('transactions')->where('tx_id', $txId)->value('status'));
    }

    public function test_refire_rejects_fast_track_test_transactions(): void
    {
        Queue::fake();
        $txId = $this->makeTx([
            'status'    => 'failed',
            'raw_input' => json_encode(['source' => 'fast_track_test']),
        ]);

        $this->actingAs($this->user)
            ->post(route('app.transactions.refire', $txId))
            ->assertRedirect()
            ->assertSessionHas('error');

        Queue::assertNothingPushed();
    }

    // ── dismiss ───────────────────────────────────────────────────────────────

    public function test_dismiss_marks_failed_tx_as_dismissed(): void
    {
        $txId = $this->makeTx(['status' => 'failed']);

        $this->actingAs($this->user)
            ->post(route('app.transactions.dismiss', $txId), ['reason' => 'Not relevant'])
            ->assertRedirect(route('app.transactions'));

        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        $this->assertEquals('dismissed', $tx->status);
        $this->assertEquals('Not relevant', $tx->human_notes);
    }

    public function test_dismiss_fails_for_non_dismissable_status(): void
    {
        $txId = $this->makeTx(['status' => 'sent']);

        $this->actingAs($this->user)
            ->post(route('app.transactions.dismiss', $txId))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals('sent', DB::table('transactions')->where('tx_id', $txId)->value('status'));
    }

    public function test_dismiss_works_for_draft_ready_status(): void
    {
        $txId = $this->makeTx(['status' => 'draft_ready']);

        $this->actingAs($this->user)
            ->post(route('app.transactions.dismiss', $txId))
            ->assertRedirect(route('app.transactions'));

        $this->assertEquals('dismissed', DB::table('transactions')->where('tx_id', $txId)->value('status'));
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function test_destroy_deletes_fast_track_test_tx(): void
    {
        $txId = $this->makeTx([
            'status'    => 'failed',
            'raw_input' => json_encode(['source' => 'fast_track_test']),
        ]);

        $this->actingAs($this->user)
            ->delete(route('app.transactions.delete', $txId))
            ->assertRedirect(route('app.transactions'));

        $this->assertNull(DB::table('transactions')->where('tx_id', $txId)->first());
    }

    public function test_destroy_refuses_real_transactions(): void
    {
        $txId = $this->makeTx([
            'status'    => 'failed',
            'raw_input' => json_encode(['source' => 'gmail']),
        ]);

        $this->actingAs($this->user)
            ->delete(route('app.transactions.delete', $txId))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull(DB::table('transactions')->where('tx_id', $txId)->first());
    }

    // ── decide ────────────────────────────────────────────────────────────────

    public function test_decide_approve_updates_status_to_approved(): void
    {
        $txId = $this->makeTx([
            'status'        => 'draft_ready',
            'gmail_draft_id' => null, // no draft — skips Gmail call
        ]);

        DB::table('renewal_register')->insert([
            'tx_id'      => $txId,
            'user_id'    => $this->user->id,
            'category'   => 'SSL Expiry',
            'asset'      => 'example.com',
            'client'     => 'Acme Corp',
            'contact'    => 'John Doe',
            'priority'   => 'High',
            'status'     => 'Draft Ready',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->post(route('app.transactions.decide', $txId), ['decision' => 'approved'])
            ->assertRedirect(route('app.transactions'));

        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        // Approval marks the tx as 'approved' — sending happens manually from Gmail
        $this->assertEquals('approved', $tx->status);
        $this->assertEquals('approved', $tx->human_decision);

        $this->assertEquals('Approved', DB::table('renewal_register')->where('tx_id', $txId)->value('status'));
    }

    public function test_decide_reject_updates_status_to_rejected(): void
    {
        $txId = $this->makeTx([
            'status'         => 'draft_ready',
            'gmail_draft_id' => null,
        ]);

        DB::table('renewal_register')->insert([
            'tx_id'      => $txId,
            'user_id'    => $this->user->id,
            'category'   => 'SSL Expiry',
            'asset'      => 'example.com',
            'client'     => 'Acme Corp',
            'contact'    => 'John Doe',
            'priority'   => 'High',
            'status'     => 'Draft Ready',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->post(route('app.transactions.decide', $txId), ['decision' => 'rejected', 'notes' => 'Wrong client'])
            ->assertRedirect(route('app.transactions'));

        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        $this->assertEquals('rejected', $tx->status);
        $this->assertEquals('Wrong client', $tx->human_notes);

        $this->assertEquals('Rejected', DB::table('renewal_register')->where('tx_id', $txId)->value('status'));
    }
}
