<?php

namespace App\Platform\Services;

use App\Platform\Contracts\WorkerContract;
use Illuminate\Support\Facades\DB;

/**
 * Shared logic for seeding, swapping, and diffing persona-specific rules.
 * Used by OnboardingController, WorkerController, and WorkerRulesController.
 */
class PersonaRuleSeeder
{
    /**
     * Wipe existing persona rules for a deployment and seed from the contract.
     * Platform rules (persona IS NULL) are never touched.
     */
    public static function seed(int $depId, int $userId, WorkerContract $contract, string $persona): void
    {
        $rules = $contract->personas()[$persona]['capture_rules'] ?? [];
        if (empty($rules)) return;

        DB::table('ava_rules')
            ->where('deployment_id', $depId)
            ->whereNotNull('persona')
            ->delete();

        $now = now();
        foreach ($rules as $rule) {
            DB::table('ava_rules')->insertOrIgnore([
                'user_id'           => $userId,
                'deployment_id'     => $depId,
                'persona'           => $persona,
                'rule_id'           => $rule['rule_id'],
                'condition'         => $rule['condition'],
                'priority'          => $rule['priority'],
                'action'            => $rule['action'],
                'approval_required' => $rule['approval_required'],
                'notes'             => $rule['notes'] ?? null,
                'active'            => true,
                'is_platform'       => false,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }

    /**
     * Compare a deployment's persona rules against the current contract definition.
     * Returns arrays of rule_ids in three states:
     *   stale    — rule exists in both but condition or action has changed
     *   orphaned — rule exists in deployment but no longer in contract
     *   missing  — rule exists in contract but not yet in deployment
     */
    public static function diff(array $deployedRules, WorkerContract $contract, string $persona): array
    {
        $contractRules = collect($contract->personas()[$persona]['capture_rules'] ?? [])->keyBy('rule_id');

        $stale    = [];
        $orphaned = [];

        foreach ($deployedRules as $r) {
            $id        = is_object($r) ? $r->rule_id  : ($r['rule_id']  ?? '');
            $condition = is_object($r) ? $r->condition : ($r['condition'] ?? '');
            $action    = is_object($r) ? $r->action    : ($r['action']    ?? '');

            $cr = $contractRules->get($id);
            if (!$cr) {
                $orphaned[] = $id;
            } elseif ($condition !== $cr['condition'] || $action !== $cr['action']) {
                $stale[] = $id;
            }
        }

        $deployedIds = collect($deployedRules)
            ->map(fn($r) => is_object($r) ? $r->rule_id : ($r['rule_id'] ?? ''))
            ->all();

        $missing = $contractRules->keys()->diff($deployedIds)->values()->all();

        return compact('stale', 'orphaned', 'missing');
    }

    /**
     * Seed platform master rules for a worker from the contract.
     * Master rules have no user_id or deployment_id — they are the template
     * copied to each new deployment.
     * Existing master rules for this persona are replaced.
     */
    public static function seedPlatformRules(string $workerSlug, WorkerContract $contract, string $persona): void
    {
        $rules = $contract->personas()[$persona]['capture_rules'] ?? [];
        if (empty($rules)) return;

        // Remove existing platform master rules for this persona
        DB::table('ava_rules')
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->where('persona', $persona)
            ->delete();

        $now = now();
        foreach ($rules as $rule) {
            DB::table('ava_rules')->insertOrIgnore([
                'user_id'           => null,
                'deployment_id'     => null,
                'persona'           => $persona,
                'rule_id'           => $rule['rule_id'],
                'condition'         => $rule['condition'],
                'priority'          => $rule['priority'],
                'action'            => $rule['action'],
                'approval_required' => $rule['approval_required'],
                'notes'             => $rule['notes'] ?? null,
                'active'            => true,
                'is_platform'       => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }
}
