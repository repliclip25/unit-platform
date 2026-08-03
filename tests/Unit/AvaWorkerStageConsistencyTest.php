<?php

namespace Tests\Unit;

use App\Workers\AVA\AvaWorker;
use PHPUnit\Framework\TestCase;

/**
 * Guards against the exact silent-drift bug found and fixed in this
 * session: prompts() and pipelineStages() used to be two hand-maintained
 * arrays that were supposed to agree on stage keys, and drifted apart
 * twice without any error — a tenant's saved Configure-page prompt
 * override for "Read Email" or "Draft Email" simply had no effect,
 * because the job queried a different stage_key than the one the
 * Configure page saved under. prompts() is now derived from
 * pipelineStages() (see AvaWorker::prompts()), which makes this
 * structurally impossible — this test is cheap insurance against a
 * future edit reintroducing a hand-maintained divergence.
 */
class AvaWorkerStageConsistencyTest extends TestCase
{
    public function test_every_prompt_stage_key_exists_in_pipeline_stages(): void
    {
        $worker = new AvaWorker();
        $stageKeys = collect($worker->pipelineStages())->pluck('key')->all();

        foreach ($worker->prompts() as $prompt) {
            $this->assertContains(
                $prompt['stage'],
                $stageKeys,
                "prompts() declares stage '{$prompt['stage']}' which does not exist in pipelineStages() — " .
                'the Configure page keys its cards by pipelineStages() keys, so this card would silently never render.'
            );
        }
    }

    public function test_every_ai_flagged_pipeline_stage_has_a_prompt_entry(): void
    {
        $worker = new AvaWorker();
        $promptStages = collect($worker->prompts())->pluck('stage')->all();

        foreach ($worker->pipelineStages() as $stage) {
            if (empty($stage['uses_ai'])) {
                continue;
            }

            $this->assertContains(
                $stage['key'],
                $promptStages,
                "pipelineStages() stage '{$stage['key']}' is flagged uses_ai but has no prompts() entry — " .
                'its override would silently never apply.'
            );
        }
    }

    /**
     * Guards a second, distinct silent-drift bug found in this same
     * session: ScheduleNextWatchJob was given a real output_column
     * ('watch_output') in pipelineStages() so its Transaction Center card
     * would stop rendering empty — but UnitPlatform::commitOutput() wrote
     * through a *different*, separately-maintained map
     * (UnitPlatform::STAGE_COLUMNS), which never got the matching entry.
     * The job ran successfully, logged success, advanced the pipeline —
     * and silently wrote to no column at all. Only caught by manually
     * dispatching the job and checking the actual column, not by reading
     * the Transaction Center (which had been verified earlier using a
     * hand-set value, not a real job run).
     *
     * UnitPlatform::stageColumns() is now derived from pipelineStages()
     * directly (see its own docblock) rather than hand-maintained, which
     * makes this specific class of drift structurally impossible — this
     * test instead guards the derivation logic itself: every stage with a
     * real output_column must end up in the derived map, keyed by its
     * log_stage_key alias where one is declared (the six original
     * synchronous stages — read_email, classify, memory, select_template,
     * draft_email, push_draft — commit under a short name that differs
     * from their pipelineStages() key), or by its own key otherwise.
     */
    public function test_every_stage_output_column_is_derived_correctly(): void
    {
        $worker = new AvaWorker();

        $reflection = new \ReflectionClass(\App\Platform\SDK\UnitPlatform::class);
        $method = $reflection->getMethod('stageColumns');
        $method->setAccessible(true);
        $stageColumns = $method->invoke(null);

        foreach ($worker->pipelineStages() as $stage) {
            if (empty($stage['output_column'])) continue;

            $mapKey = $stage['log_stage_key'] ?? $stage['key'];

            $this->assertArrayHasKey(
                $mapKey,
                $stageColumns,
                "pipelineStages() stage '{$stage['key']}' declares output_column '{$stage['output_column']}', " .
                "but the derived stageColumns() map has no entry under '{$mapKey}' — commitOutput() would silently write nowhere."
            );

            $this->assertSame(
                $stage['output_column'],
                $stageColumns[$mapKey],
                "pipelineStages() stage '{$stage['key']}' declares output_column '{$stage['output_column']}', " .
                "but the derived map has '{$stageColumns[$mapKey]}' under '{$mapKey}' instead — these must match."
            );
        }
    }
}
