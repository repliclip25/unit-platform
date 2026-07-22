<?php

namespace App\Platform\Services;

use App\Platform\Services\EmailDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PolicyEngine — defines every gate, what it blocks, and how the tenant resolves it.
 *
 * Two levels:
 *   platform  — account-wide (affects all workers)
 *   worker    — scoped to a single deployment
 *
 * Two severities:
 *   hard  — stops ALL pipeline stages including in-flight jobs
 *   soft  — stops NEW transactions only; existing pipeline continues
 */
class PolicyEngine
{
    // ── Policy catalogue ──────────────────────────────────────────────────────

    public const POLICIES = [

        // ── Platform-level ───────────────────────────────────────────────────

        'ACCOUNT_SUSPENDED' => [
            'level'        => 'platform',
            'severity'     => 'hard',
            'title'        => 'Account Suspended',
            'description'  => 'Your account has been suspended by the platform team.',
            'blocks'       => ['pipeline', 'pipeline_new', 'deploy', 'fast_track'],
            'self_service' => false,
            'resolution'   => [
                'Review the suspension reason shown above.',
                'Contact support referencing your account email.',
                'Once the issue is resolved, the platform team will lift the suspension.',
            ],
            'cta_label'    => 'Email Support',
            'cta_url'      => 'mailto:support@unit.app',
            'cta_route'    => null,
            'color'        => 'red',
        ],

        'PAYMENT_PAST_DUE' => [
            'level'        => 'platform',
            'severity'     => 'hard',
            'title'        => 'Payment Past Due',
            'description'  => 'A payment on your account failed. All processing is paused until your payment method is updated.',
            'blocks'       => ['pipeline', 'pipeline_new', 'deploy'],
            'self_service' => true,
            'resolution'   => [
                'Click "Update Payment" to open the billing portal.',
                'Update or replace your payment method.',
                'Processing resumes automatically once payment is confirmed.',
            ],
            'cta_label'    => 'Update Payment',
            'cta_url'      => null,
            'cta_route'    => 'app.billing.portal',
            'color'        => 'red',
        ],

        'SPEND_CAP_REACHED' => [
            'level'        => 'platform',
            'severity'     => 'soft',
            'title'        => 'Monthly Spend Cap Reached',
            'description'  => 'Your configured monthly AI spend cap has been reached. No new transactions will be processed until the cap resets.',
            'blocks'       => ['pipeline_new', 'fast_track'],
            'self_service' => true,
            'resolution'   => [
                'New transactions resume automatically on the 1st of next month.',
                'Or contact support to increase your spend cap.',
                'Existing drafts and approved transactions are not affected.',
            ],
            'cta_label'    => 'View Usage',
            'cta_url'      => null,
            'cta_route'    => 'app.billing',
            'color'        => 'amber',
        ],

        // ── Worker-level ─────────────────────────────────────────────────────

        'TRIAL_EXHAUSTED' => [
            'level'        => 'worker',
            'severity'     => 'soft',
            'title'        => 'Free Trial Exhausted',
            'description'  => 'You have used all free trial transactions for this worker.',
            'blocks'       => ['pipeline_new', 'fast_track'],
            'self_service' => true,
            'resolution'   => [
                'Subscribe to this worker to continue processing.',
                'All your memory, templates, and rules are preserved.',
                'Drafts already created are still available for review.',
            ],
            'cta_label'    => 'Choose a Plan',
            'cta_url'      => null,
            'cta_route'    => 'app.billing',
            'color'        => 'amber',
        ],

        'SUBSCRIPTION_CANCELED' => [
            'level'        => 'worker',
            'severity'     => 'hard',
            'title'        => 'Subscription Canceled',
            'description'  => 'The subscription for this worker has been canceled. No emails will be processed.',
            'blocks'       => ['pipeline', 'pipeline_new', 'fast_track'],
            'self_service' => true,
            'resolution'   => [
                'Reactivate your subscription in the billing portal.',
                'Choose a plan to resume processing.',
                'Existing data is preserved and available immediately on reactivation.',
            ],
            'cta_label'    => 'Reactivate',
            'cta_url'      => null,
            'cta_route'    => 'app.billing.portal',
            'color'        => 'red',
        ],

        'PLAN_QUOTA_REACHED' => [
            'level'        => 'worker',
            'severity'     => 'soft',
            'title'        => 'Monthly Plan Quota Reached',
            'description'  => 'You have reached the transaction limit for your current plan this month.',
            'blocks'       => ['pipeline_new', 'fast_track'],
            'self_service' => true,
            'resolution'   => [
                'Upgrade to the Pro plan for unlimited processing.',
                'Or wait until your billing period resets next month.',
                'Existing drafts and approved transactions are not affected.',
            ],
            'cta_label'    => 'Upgrade Plan',
            'cta_url'      => null,
            'cta_route'    => 'app.billing',
            'color'        => 'amber',
        ],

        'WORKER_PAUSED' => [
            'level'        => 'worker',
            'severity'     => 'soft',
            'title'        => 'Worker Paused',
            'description'  => 'This worker is paused and will not process new emails.',
            'blocks'       => ['pipeline_new'],
            'self_service' => true,
            'resolution'   => [
                'Resume the worker from the worker page.',
                'Emails received while paused are not queued — they will not be processed retroactively.',
            ],
            'cta_label'    => 'Resume Worker',
            'cta_url'      => null,
            'cta_route'    => 'app.workers.show',
            'color'        => 'gray',
        ],

        'SPEND_WARNING' => [
            'level'        => 'platform',
            'severity'     => 'soft',
            'title'        => 'Approaching Spend Cap',
            'description'  => 'You have used 80% or more of your monthly AI spend cap. Processing continues, but you may want to review usage.',
            'blocks'       => [], // warning only — never blocks pipeline
            'self_service' => true,
            'resolution'   => [
                'Review your usage breakdown in the Billing section.',
                'Increase your spend cap or reduce transaction volume to avoid a hard stop.',
            ],
            'cta_label'    => 'View Usage',
            'cta_url'      => null,
            'cta_route'    => 'app.billing',
            'color'        => 'amber',
        ],

        'GMAIL_WATCH_EXPIRED' => [
            'level'        => 'worker',
            'severity'     => 'soft',
            'title'        => 'Gmail Watch Expired',
            'description'  => 'The Gmail inbox watch has expired. New emails are not being received by this worker.',
            'blocks'       => ['email_ingestion'],
            'self_service' => true,
            'resolution'   => [
                'Click "Renew Watch" to re-authorise Gmail monitoring.',
                'Emails received while the watch was expired will not be processed retroactively.',
                'Watch must be renewed every 7 days (Google requirement).',
            ],
            'cta_label'    => 'Renew Watch',
            'cta_url'      => null,
            'cta_route'    => 'app.ava.gmail.watch',
            'color'        => 'amber',
        ],
    ];

    // ── Evaluate active violations ────────────────────────────────────────────

    /**
     * Returns all active policy violations for a user, optionally scoped to a deployment.
     * Each violation is the policy definition merged with live context data.
     */
    public static function evaluate(int $userId, ?int $deploymentId = null): array
    {
        $violations = [];
        $user       = DB::table('users')->where('id', $userId)->first();

        // ── Platform-level ───────────────────────────────────────────────────

        if ($user?->blocked_at) {
            // Use the stored policy code if it exists and is valid, else default to ACCOUNT_SUSPENDED
            $storedCode = $user->block_policy_code ?? 'ACCOUNT_SUSPENDED';
            $code       = isset(self::POLICIES[$storedCode]) ? $storedCode : 'ACCOUNT_SUSPENDED';
            $violations[] = self::violation($code, [
                'reason'      => $user->block_reason,
                'policy_code' => $storedCode,
                'since'       => $user->blocked_at,
            ]);
        }

        // Spend cap (platform-wide)
        $cap = (float) ($user?->monthly_spend_cap ?? 0);
        if ($cap > 0) {
            $spent = (float) DB::table('usage_events')
                ->where('user_id', $userId)
                ->whereYear('created_at',  now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('cost_usd');

            if ($spent >= $cap) {
                $violations[] = self::violation('SPEND_CAP_REACHED', [
                    'cap'       => $cap,
                    'spent'     => $spent,
                    'resets_on' => now()->endOfMonth()->addDay()->format('M j'),
                ]);
            } elseif ($spent / $cap >= 0.80) {
                $violations[] = self::violation('SPEND_WARNING', [
                    'cap'   => $cap,
                    'spent' => $spent,
                    'pct'   => round(($spent / $cap) * 100),
                ]);
            }
        }

        // ── Worker-level (requires deploymentId) ─────────────────────────────

        if ($deploymentId) {
            $billing = DB::table('deployment_billing')
                ->where('deployment_id', $deploymentId)
                ->first();

            if ($billing?->status === 'past_due') {
                $violations[] = self::violation('PAYMENT_PAST_DUE', [
                    'deployment_id' => $deploymentId,
                ]);
            }

            if ($billing?->status === 'canceled') {
                $violations[] = self::violation('SUBSCRIPTION_CANCELED', [
                    'deployment_id' => $deploymentId,
                ]);
            }

            // trial_exhausted is a hard status set at deploy time (re-deploy with no credits)
            if ($billing?->status === 'trial_exhausted') {
                $violations[] = self::violation('TRIAL_EXHAUSTED', [
                    'used'          => (int) ($billing->trial_transactions_used  ?? 0),
                    'limit'         => (int) ($billing->trial_transactions_limit ?? 0),
                    'deployment_id' => $deploymentId,
                    'reason'        => 'exhausted',
                ]);
            }

            if ($billing?->status === 'trial') {
                $used      = (int) ($billing->trial_transactions_used  ?? 0);
                $limit     = (int) ($billing->trial_transactions_limit ?? PlatformDefaults::freeTransactionsFor($billing->worker_slug));
                $expired   = $billing->trial_ends_at && now()->gt($billing->trial_ends_at);
                $txUsedUp  = $used >= $limit;

                if ($txUsedUp || $expired) {
                    // Auto-transition status to trial_exhausted so future checks are instant
                    try {
                        $wasAlreadyExhausted = DB::table('deployment_billing')
                            ->where('id', $billing->id)
                            ->value('status') === 'trial_exhausted';

                        DB::table('deployment_billing')
                            ->where('id', $billing->id)
                            ->update(['status' => 'trial_exhausted', 'updated_at' => now()]);

                        // Fire exhausted nudge exactly once — on the transition, not on every subsequent check
                        if (!$wasAlreadyExhausted) {
                            try {
                                $user = DB::table('users')->where('id', $billing->user_id)->first();
                                if ($user) {
                                    $templateKey = ($billing->worker_slug ?? 'ava') . '_trial_exhausted';
                                    EmailDispatcher::send($templateKey, $user->email, $user->name, $user->id, [
                                        '{limit}' => $limit,
                                    ]);
                                }
                            } catch (\Throwable $e) {
                                Log::error('[PolicyEngine] trial_exhausted nudge failed', ['error' => $e->getMessage()]);
                            }
                        }
                    } catch (\Throwable) {}

                    $violations[] = self::violation('TRIAL_EXHAUSTED', [
                        'used'          => $used,
                        'limit'         => $limit,
                        'deployment_id' => $deploymentId,
                        'reason'        => $expired ? 'expired' : 'transactions',
                    ]);
                }
            }

            // Plan quota — only enforced on active subscriptions with a finite transaction_limit
            if ($billing?->status === 'active' && $billing->plan_slug) {
                $plan = DB::table('worker_pricing')
                    ->where('worker_slug', $billing->worker_slug)
                    ->where('plan_slug', $billing->plan_slug)
                    ->first();

                if ($plan && !is_null($plan->transaction_limit) && $plan->transaction_limit > 0) {
                    $used = (int) ($billing->unit_count ?? 0);
                    if ($used >= $plan->transaction_limit) {
                        $violations[] = self::violation('PLAN_QUOTA_REACHED', [
                            'used'          => $used,
                            'limit'         => $plan->transaction_limit,
                            'plan'          => $billing->plan_slug,
                            'deployment_id' => $deploymentId,
                        ]);
                    }
                }
            }

            if ($billing?->status === 'paused') {
                $violations[] = self::violation('WORKER_PAUSED', [
                    'deployment_id' => $deploymentId,
                ]);
            }

            // Gmail watch expiry — check all inboxes connected via pivot
            $inboxes = DB::table('deployment_credentials')
                ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
                ->where('deployment_credentials.deployment_id', $deploymentId)
                ->select('user_gmail_credentials.*')
                ->get();

            foreach ($inboxes as $inbox) {
                if (!$inbox->watch_active) {
                    $violations[] = self::violation('GMAIL_WATCH_EXPIRED', [
                        'deployment_id' => $deploymentId,
                        'gmail'         => $inbox->gmail_address,
                    ]);
                    break; // one violation per deployment is enough
                }
            }
        }

        return $violations;
    }

    /**
     * Evaluate all deployments for a user and return violations keyed by deployment_id.
     * Also includes platform-level violations under key 'platform'.
     */
    public static function evaluateAll(int $userId): array
    {
        $result      = ['platform' => []];
        $user        = DB::table('users')->where('id', $userId)->first();
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['decommissioned'])
            ->pluck('id');

        // Platform violations (no deployment context)
        if ($user?->blocked_at) {
            $result['platform'][] = self::violation('ACCOUNT_SUSPENDED', [
                'reason' => $user->block_reason,
            ]);
        }

        $cap = (float) ($user?->monthly_spend_cap ?? 0);
        if ($cap > 0) {
            $spent = (float) DB::table('usage_events')
                ->where('user_id', $userId)
                ->whereYear('created_at',  now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('cost_usd');
            if ($spent >= $cap) {
                $result['platform'][] = self::violation('SPEND_CAP_REACHED', [
                    'cap' => $cap, 'spent' => $spent,
                ]);
            }
        }

        // Worker violations
        foreach ($deployments as $depId) {
            $result[$depId] = self::evaluate($userId, $depId);
        }

        return $result;
    }

    // ── Convenience checks ────────────────────────────────────────────────────

    public static function blocksNewTransactions(array $violations): bool
    {
        foreach ($violations as $v) {
            if (array_intersect(['pipeline', 'pipeline_new'], $v['blocks'])) {
                return true;
            }
        }
        return false;
    }

    public static function blocksDeploy(array $violations): bool
    {
        foreach ($violations as $v) {
            if (in_array('deploy', $v['blocks'])) return true;
        }
        return false;
    }

    public static function mostSevere(array $violations): ?array
    {
        $hard = array_filter($violations, fn($v) => $v['severity'] === 'hard');
        return $hard ? array_values($hard)[0] : ($violations[0] ?? null);
    }

    // ── Internal ─────────────────────────────────────────────────────────────

    private static function violation(string $code, array $context = []): array
    {
        $policy = self::POLICIES[$code] ?? [];
        return array_merge($policy, ['code' => $code, 'context' => $context]);
    }
}
