<?php

namespace App\Platform\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationEngine
{
    /**
     * Evaluate notifications for a user, scoped by role.
     *
     * Admins receive all platform-tier + worker-tier notifications.
     * Tenants receive only notifications they can act on — no links to
     * admin-only routes (/qa, /admin/*). Platform infrastructure alerts
     * (failed jobs, stuck pipeline) are admin-only; tenants see only
     * their own worker-declared conditions plus credential/billing warnings.
     *
     * Returns a prioritized Collection (error → warning → info):
     *   level        'error' | 'warning' | 'info'
     *   message      Rendered string
     *   actionLabel  CTA button text
     *   actionUrl    Fully resolved URL (always tenant-accessible for tenants)
     *   source       'platform' | worker slug
     */
    public static function evaluate(int $userId, string $role = 'tenant'): Collection
    {
        $isAdmin = $role === 'admin';
        $notifications = collect();
        $pipelineActive = ['received','ingesting','reading','classifying','memory_lookup','logging','templating','drafting','pushing'];

        // ── Admin-only platform tier ─────────────────────────────────────────
        // Infrastructure signals tenants cannot act on and should not see.

        if ($isAdmin) {
            $failedJobs = DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                $notifications->push(self::make(
                    'error',
                    self::render('{count} failed job{plural} in the queue — worker processing is blocked', $failedJobs),
                    'Inspect', route('qa'),
                    'platform'
                ));
            }

            $stuck = DB::table('transactions')
                ->where('user_id', $userId)
                ->whereIn('status', $pipelineActive)
                ->where('updated_at', '<', now()->subMinutes(10))
                ->count();
            if ($stuck > 0) {
                $notifications->push(self::make(
                    'warning',
                    self::render('{count} transaction{plural} stuck in pipeline (>10 min)', $stuck),
                    'QA', route('qa'),
                    'platform'
                ));
            }
        }

        // ── Shared platform tier (both roles) ────────────────────────────────
        // Credential and billing concerns tenants can and must act on.

        // Watch inactive on any connected inbox
        $inactiveInboxes = DB::table('deployment_credentials')
            ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
            ->join('worker_deployments', 'worker_deployments.id', '=', 'deployment_credentials.deployment_id')
            ->where('worker_deployments.user_id', $userId)
            ->whereIn('worker_deployments.status', ['active', 'paused'])
            ->where('user_gmail_credentials.watch_active', false)
            ->select('user_gmail_credentials.gmail_address', 'deployment_credentials.deployment_id')
            ->get();

        foreach ($inactiveInboxes as $inbox) {
            $notifications->push(self::make(
                'warning',
                "Watch inactive on {$inbox->gmail_address} — emails will not be processed",
                'Fix', route('app.workers.connect', $inbox->deployment_id),
                'platform'
            ));
        }

        // Trial exhausted or nearly exhausted
        $billingRecords = DB::table('deployment_billing')
            ->join('worker_deployments', 'worker_deployments.id', '=', 'deployment_billing.deployment_id')
            ->where('worker_deployments.user_id', $userId)
            ->where('deployment_billing.status', 'trial')
            ->select('deployment_billing.*', 'worker_deployments.name as dep_name')
            ->get();

        foreach ($billingRecords as $b) {
            $left = max(0, ($b->trial_transactions_limit ?? PlatformDefaults::freeTransactionsFor($b->worker_slug)) - ($b->trial_transactions_used ?? 0));
            if ($left === 0) {
                $notifications->push(self::make(
                    'error',
                    "Trial exhausted on \"{$b->dep_name}\" — upgrade to continue processing",
                    'Upgrade', route('app.billing.checkout', $b->deployment_id),
                    'platform'
                ));
            } elseif ($left <= 2) {
                $notifications->push(self::make(
                    'warning',
                    self::render("Only {$left} trial run{plural} left on \"{$b->dep_name}\"", $left),
                    'Upgrade', route('app.billing.checkout', $b->deployment_id),
                    'platform'
                ));
            }
        }

        // ── Worker tier (both roles) ─────────────────────────────────────────
        // Each active deployment's contract declares its own alert conditions.
        // Action routes declared in the contract must be tenant-accessible.

        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->get();

        foreach ($deployments as $dep) {
            $contract = WorkerRegistry::resolve($dep->worker_slug);
            if (!$contract) continue;

            foreach ($contract->notifications() as $rule) {
                $count = self::runQuery($rule['query'], $dep->id, $rule['threshold_minutes'] ?? 10);

                if (!self::triggered($count, $rule['trigger'])) continue;

                $url = self::buildUrl($rule['action_route'], $rule['action_params'] ?? []);

                $notifications->push(self::make(
                    $rule['level'],
                    self::render($rule['message'], $count),
                    $rule['action_label'],
                    $url,
                    $dep->worker_slug
                ));
            }
        }

        // ── Priority sort: error → warning → info ────────────────────────────
        $order = ['error' => 0, 'warning' => 1, 'info' => 2];
        return $notifications->sortBy(fn($n) => $order[$n['level']] ?? 3)->values();
    }

    // ── Internal helpers ─────────────────────────────────────────────────────

    private static function runQuery(string $queryKey, int $deploymentId, int $thresholdMinutes = 10): int
    {
        return match ($queryKey) {
            'tx_draft_ready_undecided' => DB::table('transactions')
                ->where('deployment_id', $deploymentId)
                ->where('status', 'draft_ready')
                ->whereNull('human_decision')
                ->count(),

            'tx_urgent_open' => DB::table('transactions')
                ->where('deployment_id', $deploymentId)
                ->whereIn('priority', ['High', 'Critical'])
                ->whereNotIn('status', ['approved', 'sent', 'failed'])
                ->count(),

            'tx_failed_today' => DB::table('transactions')
                ->where('deployment_id', $deploymentId)
                ->where('status', 'failed')
                ->whereDate('created_at', today())
                ->count(),

            'tx_stuck' => DB::table('transactions')
                ->where('deployment_id', $deploymentId)
                ->whereIn('status', ['received','ingesting','reading','classifying','memory_lookup','logging','templating','drafting','pushing'])
                ->where('updated_at', '<', now()->subMinutes($thresholdMinutes))
                ->count(),

            default => 0,
        };
    }

    private static function triggered(int $count, array $trigger): bool
    {
        return match ($trigger['operator']) {
            '>'  => $count >  $trigger['value'],
            '>=' => $count >= $trigger['value'],
            '==' => $count === $trigger['value'],
            '<'  => $count <  $trigger['value'],
            default => false,
        };
    }

    private static function render(string $template, int $count): string
    {
        return str_replace(
            ['{count}', '{plural}'],
            [$count,    $count !== 1 ? 's' : ''],
            $template
        );
    }

    private static function buildUrl(string $routeName, array $params = []): string
    {
        $base = route($routeName);
        return $params ? $base . '?' . http_build_query($params) : $base;
    }

    private static function make(string $level, string $message, string $actionLabel, string $actionUrl, string $source): array
    {
        return compact('level', 'message', 'actionLabel', 'actionUrl', 'source');
    }
}
