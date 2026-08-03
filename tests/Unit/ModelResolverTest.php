<?php

namespace Tests\Unit;

use App\Platform\SDK\Internal\ModelResolver;
use PHPUnit\Framework\TestCase;

/**
 * ModelResolver::resolve() is the model-priority decision logic extracted
 * out of UnitPlatform::getInput() — see that class's own docblock. Before
 * this extraction, testing "does the margin-protection downgrade actually
 * kick in past the threshold" meant seeding deployment_billing and
 * worker_pricing rows and building a real transaction. These are plain
 * arrays, no database.
 */
class ModelResolverTest extends TestCase
{
    public function test_tenant_override_wins_over_everything_else(): void
    {
        $plan = ['stage_models' => json_encode(['draft' => 'claude-opus-4-7']), 'classify_model' => null, 'draft_model' => null, 'draft_model_threshold' => 1];

        $result = (new ModelResolver())->resolve('custom-ollama-model', 999999, $plan);

        $this->assertSame('custom-ollama-model', $result['classifyModel']);
        $this->assertSame('custom-ollama-model', $result['draftModel']);
        $this->assertSame([], $result['stageModels'], 'A tenant override applies to every stage — the plan\'s per-stage map should be ignored entirely.');
    }

    public function test_falls_back_to_platform_defaults_with_no_override_and_no_plan(): void
    {
        $result = (new ModelResolver())->resolve(null, null, null);

        $this->assertSame(ModelResolver::DEFAULT_CLASSIFY_MODEL, $result['classifyModel']);
        $this->assertSame(ModelResolver::DEFAULT_DRAFT_MODEL, $result['draftModel']);
        $this->assertSame([], $result['stageModels']);
    }

    public function test_plans_per_stage_model_map_takes_priority_over_legacy_fields(): void
    {
        $plan = [
            'stage_models'          => json_encode(['read' => 'claude-haiku-4-5-20251001', 'draft' => 'claude-opus-4-7']),
            'classify_model'        => 'claude-sonnet-4-6', // legacy fields present but should be superseded
            'draft_model'           => 'claude-sonnet-4-6',
            'draft_model_threshold' => null,
        ];

        $result = (new ModelResolver())->resolve(null, 0, $plan);

        $this->assertSame(['read' => 'claude-haiku-4-5-20251001', 'draft' => 'claude-opus-4-7'], $result['stageModels']);
    }

    public function test_legacy_two_model_fields_apply_when_no_stage_models_map(): void
    {
        $plan = ['stage_models' => null, 'classify_model' => 'claude-haiku-4-5-20251001', 'draft_model' => 'claude-opus-4-7', 'draft_model_threshold' => null];

        $result = (new ModelResolver())->resolve(null, 0, $plan);

        $this->assertSame('claude-haiku-4-5-20251001', $result['classifyModel']);
        $this->assertSame('claude-opus-4-7', $result['draftModel']);
    }

    public function test_threshold_not_yet_crossed_leaves_draft_model_untouched(): void
    {
        $plan = ['stage_models' => null, 'classify_model' => 'claude-haiku-4-5-20251001', 'draft_model' => 'claude-opus-4-7', 'draft_model_threshold' => 1000];

        $result = (new ModelResolver())->resolve(null, 500, $plan);

        $this->assertSame('claude-opus-4-7', $result['draftModel'], 'Usage below the threshold should not trigger the margin downgrade.');
    }

    public function test_threshold_crossed_downgrades_draft_to_classify_model_with_no_stage_map(): void
    {
        $plan = ['stage_models' => null, 'classify_model' => 'claude-haiku-4-5-20251001', 'draft_model' => 'claude-opus-4-7', 'draft_model_threshold' => 1000];

        $result = (new ModelResolver())->resolve(null, 1000, $plan);

        $this->assertSame('claude-haiku-4-5-20251001', $result['draftModel'], 'Usage at/past the threshold should downgrade the expensive draft model to the classify-tier model.');
    }

    public function test_threshold_crossed_downgrades_draft_within_stage_models_map(): void
    {
        $plan = [
            'stage_models'          => json_encode(['classify' => 'claude-haiku-4-5-20251001', 'draft' => 'claude-opus-4-7']),
            'classify_model'        => null,
            'draft_model'           => null,
            'draft_model_threshold' => 1000,
        ];

        $result = (new ModelResolver())->resolve(null, 1500, $plan);

        $this->assertSame('claude-haiku-4-5-20251001', $result['stageModels']['draft'], 'Same downgrade, but applied inside the per-stage map rather than the legacy fields.');
        $this->assertSame('claude-haiku-4-5-20251001', $result['stageModels']['classify'], 'The classify entry itself should be untouched by its own downgrade.');
    }

    public function test_threshold_crossed_downgrade_falls_back_to_read_when_no_classify_entry(): void
    {
        $plan = [
            'stage_models'          => json_encode(['read' => 'claude-haiku-4-5-20251001', 'draft' => 'claude-opus-4-7']),
            'classify_model'        => null,
            'draft_model'           => null,
            'draft_model_threshold' => 1000,
        ];

        $result = (new ModelResolver())->resolve(null, 1500, $plan);

        $this->assertSame('claude-haiku-4-5-20251001', $result['stageModels']['draft'], 'With no classify entry in the map, the downgrade should fall back to read.');
    }
}
