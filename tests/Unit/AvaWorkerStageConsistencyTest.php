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
}
