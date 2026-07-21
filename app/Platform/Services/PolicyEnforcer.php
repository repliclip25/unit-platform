<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PolicyEnforcer — automated enforcement layer.
 *
 * Runs on a schedule (hourly) and evaluates every active tenant against
 * PolicyEngine rules. When a hard, non-self-service violation is detected
 * that requires platform intervention, the user is auto-blocked and an
 * audit record is written to policy_enforcement_log.
 *
 * Auto-block rules (all require persisted state in DB, not transient checks):
 *   PAYMENT_PAST_DUE  — deployment_billing.status = 'past_due' for > 3 days
 *   SPEND_CAP_REACHED — cap exceeded for the 2nd+ consecutive hourly check
 *                       (soft gate already blocks new TX; auto-block escalates it)
 *
 * Rules intentionally NOT auto-blocked:
 *   TRIAL_EXHAUSTED       — soft limit; tenant should be prompted to subscribe, not blocked
 *   SUBSCRIPTION_CANCELED — Stripe webhook handles this directly
 *   WORKER_PAUSED         — self-service, tenant chooses to pause
 *   GMAIL_WATCH_EXPIRED   — self-service, renew via UI
 */
class PolicyEnforcer
{
    // ── Entry point ───────────────────────────────────────────────────────────

    /**
     * Evaluate all active (unblocked) tenants and auto-block when rules are met.
     * Returns an array of action rows for CLI output.
     */
    public static function run(): array
    {
        // Distributed lock — prevents duplicate runs from clock skew or manual triggers
        $lock = Cache::lock('policy_enforcer_run', 3600);
        if (!$lock->get()) {
            Log::info('PolicyEnforcer: skipped — another run is in progress');
            return [];
        }

        try {
            $actions = [];

            DB::table('users')
                ->whereNull('blocked_at')
                ->select('id', 'email', 'name', 'monthly_spend_cap', 'spend_cap_breach_since')
                ->orderBy('id')
                ->chunkById(100, function ($users) use (&$actions) {
                    foreach ($users as $user) {
                        $userActions = self::evaluateUser($user);
                        $actions     = array_merge($actions, $userActions);
                    }
                });

            if (!empty($actions)) {
                Log::warning('PolicyEnforcer: auto-enforcement actions taken', ['count' => count($actions)]);
            }

            return $actions;
        } finally {
            $lock->release();
        }
    }

    // ── Per-user evaluation ───────────────────────────────────────────────────

    private static function evaluateUser(object $user): array
    {
        $actions = [];

        // ── Rule 1: PAYMENT_PAST_DUE > 3 days → hard block ───────────────────
        $pastDueDep = DB::table('deployment_billing')
            ->join('worker_deployments', 'worker_deployments.id', '=', 'deployment_billing.deployment_id')
            ->where('worker_deployments.user_id', $user->id)
            ->where('deployment_billing.status', 'past_due')
            ->whereNotNull('deployment_billing.past_due_since')
            ->where('deployment_billing.past_due_since', '<=', now()->subDays(3))
            ->first();

        if ($pastDueDep) {
            UsageGuard::blockUser(
                $user->id,
                "Auto-blocked: payment past due for 3+ days on deployment #{$pastDueDep->deployment_id}.",
                'PAYMENT_PAST_DUE'
            );
            self::log($user->id, 'auto_block', 'PAYMENT_PAST_DUE', "Past due > 3 days");
            self::notifyTenant($user, 'PAYMENT_PAST_DUE');
            $actions[] = ['auto_block', $user->id, 'PAYMENT_PAST_DUE', "Past due > 3 days ({$user->email})"];
            return $actions;
        }

        // ── Rule 2: Spend cap breach persisted for 2+ consecutive checks ──────
        $cap = (float) ($user->monthly_spend_cap ?? 0);
        if ($cap > 0) {
            $spent = (float) DB::table('usage_events')
                ->where('user_id', $user->id)
                ->whereYear('created_at',  now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('cost_usd');

            if ($spent >= $cap) {
                $breachSince = $user->spend_cap_breach_since;

                if ($breachSince === null) {
                    // First detection — record the timestamp, don't block yet
                    DB::table('users')->where('id', $user->id)->update([
                        'spend_cap_breach_since' => now(),
                        'updated_at'             => now(),
                    ]);
                    self::log($user->id, 'breach_detected', 'SPEND_CAP_REACHED', "First detection — watching");
                    $actions[] = ['breach_detected', $user->id, 'SPEND_CAP_REACHED', "First detection ({$user->email})"];
                } elseif (now()->diffInSeconds($breachSince) >= 7200) {
                    // 2+ hours over cap → escalate to hard block
                    UsageGuard::blockUser(
                        $user->id,
                        "Auto-blocked: monthly spend cap of \${$cap} exceeded for 2+ hours. Spent: \${$spent}.",
                        'SPEND_CAP_REACHED'
                    );
                    DB::table('users')->where('id', $user->id)->update([
                        'spend_cap_breach_since' => null,
                        'updated_at'             => now(),
                    ]);
                    self::log($user->id, 'auto_block', 'SPEND_CAP_REACHED', "Cap \${$cap} breached 2+ hours (spent \${$spent})");
                    self::notifyTenant($user, 'SPEND_CAP_REACHED', ['cap' => $cap, 'spent' => $spent]);
                    $actions[] = ['auto_block', $user->id, 'SPEND_CAP_REACHED', "Spend \${$spent} > cap \${$cap} ({$user->email})"];
                }
            } else {
                // Cap no longer exceeded — clear breach timer if set
                if ($user->spend_cap_breach_since !== null) {
                    DB::table('users')->where('id', $user->id)->update([
                        'spend_cap_breach_since' => null,
                        'updated_at'             => now(),
                    ]);
                }
            }
        }

        return $actions;
    }

    // ── Tenant notification ───────────────────────────────────────────────────

    private static function notifyTenant(object $user, string $policyCode, array $context = []): void
    {
        $policy = \App\Platform\Services\PolicyEngine::POLICIES[$policyCode] ?? null;
        if (!$policy) return;

        $resolution = implode("\n", array_map(
            fn($step, $i) => ($i + 1) . '. ' . $step,
            $policy['resolution'],
            array_keys($policy['resolution'])
        ));

        $extras = match ($policyCode) {
            'SPEND_CAP_REACHED' => "\nYour cap: \${$context['cap']} · Spent this month: \$" . number_format($context['spent'] ?? 0, 4),
            default             => '',
        };

        $body = "Hi {$user->name},\n\n"
            . "Your UNIT account has been automatically blocked under policy: {$policy['title']}.\n\n"
            . $policy['description'] . $extras . "\n\n"
            . "How to resolve:\n{$resolution}\n\n"
            . "Log in to your account to take action:\n" . url('/app/billing') . "\n\n"
            . "UNIT Platform";

        try {
            \Illuminate\Support\Facades\Mail::raw(
                $body,
                fn($m) => $m->to($user->email)->subject("Action Required: {$policy['title']} — Your UNIT account has been paused")
            );
        } catch (\Throwable $e) {
            Log::error('PolicyEnforcer: notification email failed', [
                'user_id' => $user->id, 'policy' => $policyCode, 'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Audit log ─────────────────────────────────────────────────────────────

    private static function log(int $userId, string $action, string $policyCode, string $detail): void
    {
        try {
            DB::table('policy_enforcement_log')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'policy_code' => $policyCode,
                'detail'      => $detail,
                'created_at'  => now(),
            ]);
        } catch (\Throwable) {
            // Table may not exist yet — fail silently, main log captures it
            Log::info("PolicyEnforcer [{$action}] user={$userId} policy={$policyCode}: {$detail}");
        }
    }
}
