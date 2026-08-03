<?php

namespace Tests\Unit;

use App\Platform\SDK\Internal\PipelineAdvancer;
use PHPUnit\Framework\TestCase;

/**
 * PipelineAdvancer::resolveNextDispatch() is the gate-scanning decision
 * logic extracted out of UnitPlatform::advance() (see that class's own
 * docblock). Before this extraction, the only way to exercise a scenario
 * like "three consecutive disabled stages in a row" was to build a real
 * transaction, disable three real gates, and actually dispatch it — slow,
 * and exactly the kind of scenario nobody bothers writing three times.
 * These are synthetic stage arrays; no DB, no queue, milliseconds each.
 */
class PipelineAdvancerTest extends TestCase
{
    private function stage(string $key, ?string $jobClass = null, ?string $gateType = null): array
    {
        return ['key' => $key, 'job_class' => $jobClass, 'gate_type' => $gateType];
    }

    private function alwaysEnabled(): callable
    {
        return fn(string $key) => true;
    }

    public function test_dispatches_the_immediate_next_stage_with_a_job(): void
    {
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('b', 'JobB'),
            $this->stage('c', 'JobC'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $this->alwaysEnabled());

        $this->assertSame(PipelineAdvancer::DISPATCH, $decision['type']);
        $this->assertSame('b', $decision['stage']['key']);
    }

    public function test_skips_job_less_markers_with_no_gate_type(): void
    {
        // e.g. 'webhook' — a synthetic entry-point marker, safe to skip past
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('marker', null, null),
            $this->stage('c', 'JobC'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $this->alwaysEnabled());

        $this->assertSame(PipelineAdvancer::DISPATCH, $decision['type']);
        $this->assertSame('c', $decision['stage']['key']);
    }

    public function test_halts_at_a_hard_gate_with_no_job_class(): void
    {
        // e.g. 'human_decide', 'confirm_payment' — waits on a human action
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('human_decide', null, 'hard'),
            $this->stage('c', 'JobC'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $this->alwaysEnabled());

        $this->assertSame(PipelineAdvancer::HALT, $decision['type']);
        $this->assertSame('human_decide', $decision['stage']['key']);
    }

    public function test_a_disabled_hard_gate_is_skipped_not_halted(): void
    {
        // A tenant can turn off even a hard gate from AVA Settings — it
        // never dispatches and never halts, scanning just continues.
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('confirm_payment', null, 'hard'),
            $this->stage('c', 'JobC'),
        ];

        $gate = fn(string $key) => $key !== 'confirm_payment';

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $gate);

        $this->assertSame(PipelineAdvancer::DISPATCH, $decision['type']);
        $this->assertSame('c', $decision['stage']['key']);
    }

    public function test_multiple_consecutive_disabled_stages_all_get_skipped_in_one_pass(): void
    {
        // The exact scenario the load-bearing audit flagged as undertested:
        // a chain of several disabled stages in a row, including a hard gate
        // mixed in among soft/skippable ones, all skipped in one scan.
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('request_invoice', 'InvoiceJob', 'soft'),
            $this->stage('request_documents', 'DocsJob', 'skippable'),
            $this->stage('confirm_payment', null, 'hard'),
            $this->stage('e', 'JobE'),
        ];

        $disabled = ['request_invoice', 'request_documents', 'confirm_payment'];
        $gate = fn(string $key) => !in_array($key, $disabled, true);

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $gate);

        $this->assertSame(PipelineAdvancer::DISPATCH, $decision['type']);
        $this->assertSame('e', $decision['stage']['key'], 'Three consecutive disabled stages (soft, skippable, and a hard gate) should all be skipped in one pass, landing on the next real job.');
    }

    public function test_reports_complete_when_nothing_remains_after_current_stage(): void
    {
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('b', 'JobB'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 1, $this->alwaysEnabled());

        $this->assertSame(PipelineAdvancer::COMPLETE, $decision['type']);
    }

    public function test_reports_complete_when_every_remaining_stage_is_disabled(): void
    {
        // Every stage after the current one is gated off — the scan
        // exhausts the array without ever dispatching or halting.
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('b', 'JobB'),
            $this->stage('c', 'JobC'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, fn(string $key) => false);

        $this->assertSame(PipelineAdvancer::COMPLETE, $decision['type']);
    }

    public function test_soft_and_skippable_gates_with_a_job_class_dispatch_normally(): void
    {
        // gate_type only matters for job-less stages (whether to halt or
        // skip) — a soft/skippable stage that DOES have a job_class is
        // dispatched exactly like any other stage, gate_type is irrelevant.
        $stages = [
            $this->stage('a', 'JobA'),
            $this->stage('request_invoice', 'InvoiceJob', 'soft'),
        ];

        $decision = (new PipelineAdvancer())->resolveNextDispatch($stages, 0, $this->alwaysEnabled());

        $this->assertSame(PipelineAdvancer::DISPATCH, $decision['type']);
        $this->assertSame('request_invoice', $decision['stage']['key']);
    }
}
