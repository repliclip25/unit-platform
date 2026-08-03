<?php

namespace App\Platform\SDK\Internal;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Writes to transaction_stage_log — the granular started/completed/failed
 * audit trail behind Desk's activity feed and per-stage duration tracking.
 * Extracted out of UnitPlatform, which it was only ever a private,
 * internal-only concern of (commitOutput() and setStatus() are its only two
 * callers, both inside UnitPlatform itself — nothing external ever called
 * these methods directly). No public API changed by this extraction.
 */
final class StageLog
{
    public function started(string $txId, string $stageKey): void
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)
            ->select('deployment_id', 'user_id', 'worker_slug')
            ->first();

        try {
            DB::table('transaction_stage_log')->insert([
                'tx_id'         => $txId,
                'deployment_id' => $tx?->deployment_id,
                'user_id'       => $tx?->user_id,
                'worker_slug'   => $tx?->worker_slug,
                'stage_key'     => $stageKey,
                'event'         => 'started',
                'attempt'       => $this->currentAttempt($txId, $stageKey),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StageLog write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    public function completed(string $txId, string $stageKey): void
    {
        try {
            $tx = DB::table('transactions')->where('tx_id', $txId)
                ->select('deployment_id', 'user_id', 'worker_slug')
                ->first();

            DB::table('transaction_stage_log')->insert([
                'tx_id'         => $txId,
                'deployment_id' => $tx?->deployment_id,
                'user_id'       => $tx?->user_id,
                'worker_slug'   => $tx?->worker_slug,
                'stage_key'     => $stageKey,
                'event'         => 'completed',
                'duration_ms'   => $this->durationSinceStarted($txId, $stageKey),
                'attempt'       => $this->currentAttempt($txId, $stageKey),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StageLog completed write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    public function failed(string $txId, string $stageKey, string $errorSummary = 'failed'): void
    {
        try {
            $tx = DB::table('transactions')->where('tx_id', $txId)
                ->select('deployment_id', 'user_id', 'worker_slug')
                ->first();

            DB::table('transaction_stage_log')->insert([
                'tx_id'         => $txId,
                'deployment_id' => $tx?->deployment_id,
                'user_id'       => $tx?->user_id,
                'worker_slug'   => $tx?->worker_slug,
                'stage_key'     => $stageKey,
                'event'         => 'failed',
                'duration_ms'   => $this->durationSinceStarted($txId, $stageKey),
                'error_summary' => $errorSummary,
                'attempt'       => $this->currentAttempt($txId, $stageKey),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StageLog failed write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    private function durationSinceStarted(string $txId, string $stageKey): ?int
    {
        $started = DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('stage_key', $stageKey)
            ->where('event', 'started')
            ->latest('id')
            ->first();

        return $started
            ? max(0, (int) \Carbon\Carbon::parse($started->created_at)->diffInMilliseconds(now()))
            : null;
    }

    private function currentAttempt(string $txId, string $stageKey): int
    {
        return (int) DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('stage_key', $stageKey)
            ->where('event', 'started')
            ->count() + 1;
    }
}
