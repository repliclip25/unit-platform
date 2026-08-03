<?php

namespace App\Platform\SDK\Internal;

/**
 * Decides which Claude model each pipeline stage uses — pure decision logic
 * extracted out of UnitPlatform::getInput(), which every single job in the
 * pipeline calls. Two genuinely different concerns were tangled together in
 * that one method: "assemble the job's context" and "figure out which AI
 * model to bill this stage against." A bug in either showed up with the
 * exact same failure signature — "getInput is broken" — with no way to tell
 * which without reading the whole ~120-line method.
 *
 * Priority order (highest wins):
 *   1. Tenant custom model override — applies to every stage, no exceptions
 *   2. Plan's per-stage model map (stage_models JSON)
 *   3. Plan's legacy two-model fields (classify_model / draft_model)
 *   4. Platform default (DEFAULT_CLASSIFY_MODEL / DEFAULT_DRAFT_MODEL)
 * ...then, only if none of the above already produced a per-stage map: a
 * margin-protection downgrade once a deployment's usage crosses the plan's
 * draft_model_threshold — draft stage silently drops to the cheaper
 * classify-tier model (or the equivalent per-stage entry) past that point.
 *
 * DB lookups (deployment_billing, worker_pricing) stay in getInput() itself
 * — this class only makes the decision once the raw values are already in
 * hand, so it can be tested with plain arrays and no database at all.
 */
final class ModelResolver
{
    public const DEFAULT_CLASSIFY_MODEL = 'claude-haiku-4-5-20251001';
    public const DEFAULT_DRAFT_MODEL    = 'claude-sonnet-4-6';

    /**
     * @param ?string $tenantOverrideModel deployment config's ai_model — set means it wins outright
     * @param ?int $billingUnitCount deployment_billing.unit_count, checked against the plan's threshold
     * @param ?array{stage_models: ?string, classify_model: ?string, draft_model: ?string, draft_model_threshold: ?int} $plan
     *        The resolved worker_pricing row for this deployment's plan, or null if none resolved
     *        (no billing row, no plan_slug, or the plan_slug didn't match a real row).
     * @return array{classifyModel: string, draftModel: string, stageModels: array}
     */
    public function resolve(?string $tenantOverrideModel, ?int $billingUnitCount, ?array $plan): array
    {
        if (!empty($tenantOverrideModel)) {
            return [
                'classifyModel' => $tenantOverrideModel,
                'draftModel'    => $tenantOverrideModel,
                'stageModels'   => [],
            ];
        }

        $classifyModel = self::DEFAULT_CLASSIFY_MODEL;
        $draftModel    = self::DEFAULT_DRAFT_MODEL;
        $stageModels   = [];

        if (!$plan) {
            return compact('classifyModel', 'draftModel', 'stageModels');
        }

        if (!empty($plan['stage_models'])) {
            $stageModels = json_decode($plan['stage_models'], true) ?: [];
        }

        $classifyModel = $plan['classify_model'] ?: $classifyModel;
        $draftModel    = $plan['draft_model']    ?: $draftModel;

        $thresholdCrossed = !empty($plan['draft_model_threshold'])
            && $billingUnitCount !== null
            && $billingUnitCount >= $plan['draft_model_threshold'];

        if ($thresholdCrossed) {
            if (empty($stageModels)) {
                $draftModel = $classifyModel;
            } else {
                $stageModels['draft'] = $stageModels['classify'] ?? $stageModels['read'] ?? $classifyModel;
            }
        }

        return compact('classifyModel', 'draftModel', 'stageModels');
    }
}
