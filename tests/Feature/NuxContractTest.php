<?php

namespace Tests\Feature;

use App\Platform\Services\WorkerRegistry;
use App\Workers\NUX\NuxWorker;
use Tests\TestCase;

/**
 * NUX contract — smoke tests ensuring all 31 methods return the expected
 * shape. No DB, no queue, no external calls — pure contract validation.
 */
class NuxContractTest extends TestCase
{
    private NuxWorker $nux;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nux = new NuxWorker();
    }

    // ── Block 1: Identity ────────────────────────────────────────────────────

    public function test_identity_has_required_keys(): void
    {
        $id = $this->nux->identity();
        $this->assertArrayHasKey('name',        $id);
        $this->assertArrayHasKey('slug',        $id);
        $this->assertArrayHasKey('version',     $id);
        $this->assertArrayHasKey('description', $id);
        $this->assertSame('nux', $id['slug']);
    }

    public function test_employee_has_required_keys(): void
    {
        $emp = $this->nux->employee();
        foreach (['name','pronoun','title','department','employer','mission','introduction','what_i_do','activity_labels'] as $key) {
            $this->assertArrayHasKey($key, $emp, "employee() missing key: {$key}");
        }
        $this->assertIsArray($emp['what_i_do']);
        $this->assertNotEmpty($emp['what_i_do']);
        $this->assertArrayHasKey('watching',      $emp['activity_labels']);
        $this->assertArrayHasKey('working_on',    $emp['activity_labels']);
        $this->assertArrayHasKey('waiting_label', $emp['activity_labels']);
        $this->assertArrayHasKey('memory_label',  $emp['activity_labels']);
    }

    public function test_org_has_required_keys(): void
    {
        $org = $this->nux->org();
        foreach (['name','type','website','logo'] as $key) {
            $this->assertArrayHasKey($key, $org);
        }
    }

    public function test_demo_payload_has_required_fields(): void
    {
        $payload = $this->nux->demoPayload();
        foreach (['source','post_id','platform','post_text','target_channels'] as $key) {
            $this->assertArrayHasKey($key, $payload);
        }
        $this->assertSame('public_demo', $payload['source']);
        $this->assertIsArray($payload['target_channels']);
    }

    // ── Block 2: Onboarding ──────────────────────────────────────────────────

    public function test_onboarding_steps_returns_four_steps(): void
    {
        $steps = $this->nux->onboardingSteps();
        $this->assertCount(4, $steps);
        $names = array_column($steps, 'name');
        $this->assertContains('credential',  $names);
        $this->assertContains('gmail',       $names);
        $this->assertContains('memory',      $names);
        $this->assertContains('fast-track',  $names);
    }

    public function test_platform_requirements_returns_array(): void
    {
        $reqs = $this->nux->platformRequirements();
        $this->assertIsArray($reqs);
        $this->assertContains('email', $reqs);
    }

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    public function test_instances_declares_single_deployment(): void
    {
        $inst = $this->nux->instances();
        $this->assertFalse((bool) $inst['multiple']);
        $this->assertSame(1, $inst['max']);
    }

    public function test_credential_returns_three_slots(): void
    {
        $creds = $this->nux->credential();
        $this->assertCount(3, $creds);
        $keys = array_column($creds, 'key');
        $this->assertContains('linkedin', $keys);
        $this->assertContains('x',        $keys);
        $this->assertContains('inbox',    $keys);
    }

    public function test_credential_is_multi_slot(): void
    {
        $creds = $this->nux->credential();
        // Multi-slot detection: first key is 0 (indexed array)
        $this->assertArrayHasKey(0, $creds);
    }

    // ── Block 3: Pipeline ────────────────────────────────────────────────────

    public function test_pipeline_has_six_stages(): void
    {
        $stages = $this->nux->pipeline();
        $this->assertCount(6, $stages);
    }

    public function test_pipeline_stages_are_sequential(): void
    {
        $stages = $this->nux->pipeline();
        foreach ($stages as $i => $stage) {
            $this->assertSame($i + 1, $stage['stage']);
            $this->assertSame(6, $stage['total']);
        }
    }

    public function test_pipeline_terminal_stage_has_null_connects_to(): void
    {
        $stages = $this->nux->pipeline();
        $last   = end($stages);
        $this->assertNull($last['connects_to']);
    }

    public function test_pipeline_stage_keys_are_expected(): void
    {
        $stages     = $this->nux->pipeline();
        $jobs       = array_column($stages, 'job');
        $expectedJobs = [
            \App\Workers\NUX\Jobs\ReadPostJob::class,
            \App\Workers\NUX\Jobs\ClassifyPostJob::class,
            \App\Workers\NUX\Jobs\RepurposePostJob::class,
            \App\Workers\NUX\Jobs\MediaJob::class,
            \App\Workers\NUX\Jobs\DraftPostJob::class,
            \App\Workers\NUX\Jobs\PushToGmailJob::class,
        ];
        $this->assertSame($expectedJobs, $jobs);
    }

    public function test_ingest_job_class_is_read_post_job(): void
    {
        $this->assertSame(
            \App\Workers\NUX\Jobs\ReadPostJob::class,
            $this->nux->ingestJobClass()
        );
    }

    public function test_emit_returns_expected_events(): void
    {
        $events = array_column($this->nux->emit(), 'event');
        $this->assertContains('content.draft_ready',     $events);
        $this->assertContains('content.low_value_skipped', $events);
    }

    public function test_subscriptions_returns_empty_array(): void
    {
        $this->assertSame([], $this->nux->subscriptions());
    }

    public function test_commit_returns_null(): void
    {
        $this->assertNull($this->nux->commit());
    }

    // ── Block 4–7: Quality / Output / Prompts / Owner ────────────────────────

    public function test_qa_requirements_returns_array(): void
    {
        $qa = $this->nux->qaRequirements();
        $this->assertIsArray($qa);
        $this->assertNotEmpty($qa);
    }

    public function test_output_has_required_keys(): void
    {
        $out = $this->nux->output();
        foreach (['description','destination','format','fields','human_action'] as $key) {
            $this->assertArrayHasKey($key, $out);
        }
    }

    public function test_prompts_covers_all_six_stages(): void
    {
        $prompts = $this->nux->prompts();
        $this->assertCount(6, $prompts);
        $stages = array_column($prompts, 'stage');
        foreach (['read_post','classify','repurpose','media','draft_post','push_draft'] as $s) {
            $this->assertContains($s, $stages, "prompts() missing stage: {$s}");
        }
    }

    public function test_ai_stages_have_system_and_user_prompts(): void
    {
        foreach ($this->nux->prompts() as $prompt) {
            // Image-generation stages use a single prompt (no system message)
            if ($prompt['uses_ai'] && ($prompt['model'] ?? '') !== 'dall-e-3') {
                $this->assertNotEmpty($prompt['system'], "Stage {$prompt['stage']} missing system prompt");
                $this->assertNotEmpty($prompt['user'],   "Stage {$prompt['stage']} missing user prompt");
            }
        }
    }

    public function test_owner_is_platform_type(): void
    {
        $owner = $this->nux->owner();
        $this->assertSame('platform', $owner['type']);
        $this->assertSame('UNIT', $owner['name']);
    }

    // ── Block 8: Platform Integration ────────────────────────────────────────

    public function test_scheduled_jobs_returns_performance_feedback_job(): void
    {
        $jobs = $this->nux->scheduledJobs();
        $this->assertNotEmpty($jobs);
        $this->assertSame(\App\Workers\NUX\Jobs\NuxPerformanceFeedbackJob::class, $jobs[0]['job']);
        $this->assertArrayHasKey('cron', $jobs[0]);
        $this->assertArrayHasKey('queue', $jobs[0]);
        $this->assertTrue($jobs[0]['per_deployment']);
    }

    public function test_stuck_recovery_map_covers_pipeline_statuses(): void
    {
        $map = $this->nux->stuckRecoveryMap();
        $this->assertArrayHasKey('reading',     $map);
        $this->assertArrayHasKey('classifying', $map);
        $this->assertArrayHasKey('repurposing', $map);
        $this->assertArrayHasKey('drafting',    $map);
    }

    // ── Registry ─────────────────────────────────────────────────────────────

    public function test_nux_resolves_from_registry(): void
    {
        $contract = WorkerRegistry::resolve('nux');
        $this->assertInstanceOf(NuxWorker::class, $contract);
        $this->assertFalse(WorkerRegistry::isNull($contract));
    }

    public function test_nux_appears_in_registry_all(): void
    {
        $slugs = array_map(
            fn($c) => $c->identity()['slug'],
            WorkerRegistry::all()
        );
        $this->assertContains('nux', $slugs);
    }
}
