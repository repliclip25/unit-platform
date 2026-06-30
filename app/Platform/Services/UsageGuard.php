<?php

namespace App\Platform\Services;

use App\Platform\Exceptions\BillingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UsageGuard — enforcement layer. Evaluates PolicyEngine violations and throws
 * BillingException with the policy code so failed() handlers can record it cleanly.
 */
class UsageGuard
{
    // ── Hard block — stops any job mid-pipeline ───────────────────────────────

    public static function checkHard(string $txId): void
    {
        $ctx = self::context($txId);
        if (!$ctx) return;

        $violations = PolicyEngine::evaluate($ctx->tx->user_id, $ctx->tx->deployment_id);

        foreach ($violations as $v) {
            if ($v['severity'] === 'hard' && array_intersect(['pipeline'], $v['blocks'])) {
                throw new BillingException($v['title'] . ': ' . $v['description'], $v['code']);
            }
        }
    }

    // ── New-transaction gate — quota, spend cap, paused ───────────────────────

    public static function checkNew(string $txId): void
    {
        $ctx = self::context($txId);
        if (!$ctx) return;

        $violations = PolicyEngine::evaluate($ctx->tx->user_id, $ctx->tx->deployment_id);

        foreach ($violations as $v) {
            if (array_intersect(['pipeline', 'pipeline_new'], $v['blocks'])) {
                throw new BillingException($v['title'] . ': ' . $v['description'], $v['code']);
            }
        }
    }

    // ── Pre-TX gate — check before creating the transaction ───────────────────
    // Call this before txService->create() to prevent quota bypass via rapid submissions.

    public static function checkDeployment(int $userId, int $deploymentId): void
    {
        $violations = PolicyEngine::evaluate($userId, $deploymentId);

        foreach ($violations as $v) {
            if (array_intersect(['pipeline', 'pipeline_new'], $v['blocks'])) {
                throw new BillingException($v['title'] . ': ' . $v['description'], $v['code']);
            }
        }
    }

    // ── Admin actions ─────────────────────────────────────────────────────────

    public static function blockUser(int $userId, string $reason, string $policyCode = 'ACCOUNT_SUSPENDED', bool $notify = true): void
    {
        DB::table('users')->where('id', $userId)->update([
            'blocked_at'        => now(),
            'block_reason'      => $reason,
            'block_policy_code' => $policyCode,
            'updated_at'        => now(),
        ]);

        Log::warning('UsageGuard: user blocked', [
            'user_id' => $userId, 'policy' => $policyCode, 'reason' => $reason,
        ]);

        if ($notify) {
            $user   = DB::table('users')->where('id', $userId)->first();
            $policy = PolicyEngine::POLICIES[$policyCode] ?? null;

            if ($user && $policy) {
                $resolution = implode("\n", array_map(
                    fn($step, $i) => ($i + 1) . '. ' . $step,
                    $policy['resolution'],
                    array_keys($policy['resolution'])
                ));

                $body = "Hi {$user->name},\n\n"
                    . "Your UNIT account has been blocked under policy: {$policy['title']}.\n\n"
                    . $policy['description'] . "\n\n"
                    . "How to resolve:\n{$resolution}\n\n"
                    . "Log in to take action:\n" . url('/billing') . "\n\n"
                    . "UNIT Platform";

                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        $body,
                        fn($m) => $m->to($user->email)
                            ->subject("Your UNIT account has been paused: {$policy['title']}")
                    );
                } catch (\Throwable $e) {
                    Log::error('UsageGuard: block notification failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

                    // Write to platform_events so admin can see this in the dashboard
                    DB::table('platform_events')->insert([
                        'user_id'     => $userId,
                        'worker_slug' => null,
                        'tx_id'       => null,
                        'event'       => 'block_notify_failed',
                        'payload'     => json_encode([
                            'policy_code' => $policyCode,
                            'error'       => $e->getMessage(),
                        ]),
                        'level'      => 'error',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public static function unblockUser(int $userId): void
    {
        DB::table('users')->where('id', $userId)->update([
            'blocked_at'        => null,
            'block_reason'      => null,
            'block_policy_code' => null,
            'updated_at'        => now(),
        ]);
        Log::info('UsageGuard: user unblocked', ['user_id' => $userId]);
    }

    public static function setSpendCap(int $userId, ?float $cap): void
    {
        DB::table('users')->where('id', $userId)->update([
            'monthly_spend_cap' => $cap,
            'updated_at'        => now(),
        ]);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private static function context(string $txId): ?object
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        if (!$tx) return null;
        return (object) ['tx' => $tx];
    }
}
