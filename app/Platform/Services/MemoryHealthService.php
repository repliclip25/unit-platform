<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class MemoryHealthService
{
    // Complete records needed across the tenant's deployed workers to reach healthy status.
    public const HEALTHY_THRESHOLD = 5;

    /**
     * Calculate platform-level memory health for a tenant.
     *
     * Inspects all active worker deployments, collects their memoryRequirements(),
     * and determines what a "complete record" means for this specific tenant's stack.
     * Workers that return [] from memoryRequirements() are ignored — they don't
     * contribute to or reduce the health score.
     *
     * Returns:
     *   score        — 0–100 (complete_records / HEALTHY_THRESHOLD * 100, capped at 100)
     *   complete     — clients that satisfy all requirements across deployed workers
     *   total        — total clients loaded
     *   needed       — complete records still needed to reach threshold
     *   healthy      — bool: score >= 100
     *   needs_memory — bool: at least one deployed worker requires memory
     */
    public static function score(int $userId): array
    {
        $empty = [
            'score'        => 0,
            'complete'     => 0,
            'total'        => 0,
            'needed'       => self::HEALTHY_THRESHOLD,
            'healthy'      => false,
            'needs_memory' => false,
        ];

        // Collect memory requirements from all active deployments
        $requirements = self::collectRequirements($userId);

        if (empty($requirements)) {
            // No deployed workers use memory — health is not applicable
            return array_merge($empty, ['needs_memory' => false]);
        }

        $clients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($clients->isEmpty()) {
            return array_merge($empty, ['needs_memory' => true]);
        }

        $complete = 0;

        foreach ($clients as $clientId) {
            if (self::clientSatisfiesRequirements($userId, $clientId, $requirements)) {
                $complete++;
            }
        }

        $score = (int) min(100, round($complete / self::HEALTHY_THRESHOLD * 100));

        return [
            'score'        => $score,
            'complete'     => $complete,
            'total'        => $clients->count(),
            'needed'       => max(0, self::HEALTHY_THRESHOLD - $complete),
            'healthy'      => $score >= 100,
            'needs_memory' => true,
        ];
    }

    /**
     * Collect the union of memory requirements from all the tenant's deployed workers.
     * If multiple workers need contacts.email, it appears once — union, not duplication.
     */
    public static function collectRequirements(int $userId): array
    {
        $deployedSlugs = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('status', '!=', 'deleted')
            ->pluck('worker_slug')
            ->unique();

        $union = [];

        foreach ($deployedSlugs as $slug) {
            try {
                $contract = WorkerRegistry::resolveActive($slug);
                if (WorkerRegistry::isNull($contract)) continue;

                $reqs = $contract->memoryRequirements();
                if (empty($reqs)) continue;

                foreach ($reqs as $entity => $fields) {
                    foreach ($fields as $field) {
                        $union[$entity][$field] = true;
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }

        // Convert to flat arrays: ['clients' => ['name'], 'contacts' => ['name','email'], ...]
        return array_map(fn($fields) => array_keys($fields), $union);
    }

    /**
     * Check whether a single client record satisfies all declared requirements.
     * A contact is required only if 'contacts' is in requirements.
     * An asset is required only if 'assets' is in requirements.
     */
    private static function clientSatisfiesRequirements(int $userId, int $clientId, array $requirements): bool
    {
        if (isset($requirements['contacts'])) {
            $needsEmail = in_array('email', $requirements['contacts']);

            $contactQuery = DB::table('contacts')
                ->where('user_id', $userId)
                ->where('client_id', $clientId)
                ->whereNull('deleted_at');

            if ($needsEmail) {
                $contactQuery->whereNotNull('email')->where('email', '!=', '');
            }

            if (!$contactQuery->exists()) return false;
        }

        if (isset($requirements['assets'])) {
            $needsRenewal = in_array('renewal_date', $requirements['assets']);

            $assetQuery = DB::table('assets')
                ->where('user_id', $userId)
                ->where('client_id', $clientId)
                ->whereNull('deleted_at');

            if ($needsRenewal) {
                $assetQuery->whereNotNull('renewal_date');
            }

            if (!$assetQuery->exists()) return false;
        }

        return true;
    }
}
