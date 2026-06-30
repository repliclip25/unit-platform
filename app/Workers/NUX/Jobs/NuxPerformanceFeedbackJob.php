<?php

namespace App\Workers\NUX\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs daily per NUX deployment.
 * Creates pending performance_log rows when a tracked draft hits T+7/14/30/90,
 * and enriches nux_memory with engagement patterns after manual feedback is submitted.
 */
class NuxPerformanceFeedbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    private const TRACKING_DAYS = [7, 14, 30, 90];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep) return;

        $config  = json_decode($dep->config ?? '{}', true);
        $mode    = $config['performance_tracking_mode'] ?? 'manual';
        $userId  = $dep->user_id;

        // Find nux_register rows that don't yet have all tracking slots created
        $registers = DB::table('nux_register')
            ->where('deployment_id', $this->deploymentId)
            ->where('status', 'draft_ready')
            ->whereNotNull('created_at')
            ->get();

        foreach ($registers as $reg) {
            $this->ensureTrackingSlots($reg, $userId, $mode);
        }

        // Enrich memory from submitted feedback that hasn't been processed yet
        $this->enrichMemoryFromPendingFeedback($userId, $this->deploymentId);
    }

    private function ensureTrackingSlots(object $reg, int $userId, string $mode): void
    {
        $createdAt = \Carbon\Carbon::parse($reg->created_at);

        foreach (self::TRACKING_DAYS as $day) {
            $dueAt = $createdAt->copy()->addDays($day);

            // Only create slot if due date is approaching (within 2 days ahead)
            if ($dueAt->isFuture() && $dueAt->diffInDays(now()) > 2) {
                continue;
            }

            $exists = DB::table('nux_performance_log')
                ->where('nux_register_id', $reg->id)
                ->where('tracking_day', $day)
                ->exists();

            if (!$exists) {
                DB::table('nux_performance_log')->insert([
                    'user_id'        => $userId,
                    'deployment_id'  => $this->deploymentId,
                    'nux_register_id'=> $reg->id,
                    'tx_id'          => $reg->transaction_id ?? '',
                    'tracking_day'   => $day,
                    'tracking_mode'  => $mode,
                    'due_at'         => $dueAt,
                    'submitted_at'   => null,
                    'enriched'       => false,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                Log::info('NUX performance slot created', [
                    'register_id' => $reg->id,
                    'day'         => $day,
                    'due_at'      => $dueAt->toDateString(),
                    'mode'        => $mode,
                ]);
            }
        }
    }

    /**
     * After manual feedback is submitted, aggregate engagement patterns
     * and write them back to nux_memory as 'performance_patterns'.
     */
    private function enrichMemoryFromPendingFeedback(int $userId, int $deploymentId): void
    {
        $pending = DB::table('nux_performance_log')
            ->where('user_id', $userId)
            ->where('deployment_id', $deploymentId)
            ->whereNotNull('submitted_at')
            ->where('enriched', false)
            ->get();

        if ($pending->isEmpty()) return;

        // Aggregate: avg engagement by post_type and tone
        $patterns = [];

        foreach ($pending as $log) {
            $reg = DB::table('nux_register')->where('id', $log->nux_register_id)->first();
            if (!$reg) continue;

            $postType = $reg->post_type ?? 'unknown';
            $tone     = $reg->tone     ?? 'unknown';
            $topic    = $reg->topic    ?? '';

            $engagementScore = ($log->likes ?? 0) + ($log->comments ?? 0) * 2 + ($log->shares ?? 0) * 3;

            $key = "{$postType}|{$tone}";
            if (!isset($patterns[$key])) {
                $patterns[$key] = ['post_type' => $postType, 'tone' => $tone, 'scores' => [], 'topics' => []];
            }
            $patterns[$key]['scores'][] = $engagementScore;
            if ($topic) $patterns[$key]['topics'][] = $topic;

            // Mark as enriched
            DB::table('nux_performance_log')->where('id', $log->id)->update([
                'enriched'   => true,
                'updated_at' => now(),
            ]);
        }

        if (empty($patterns)) return;

        // Build a human-readable performance patterns summary for Claude to use
        $summaryLines = [];
        foreach ($patterns as $p) {
            $avg   = count($p['scores']) ? round(array_sum($p['scores']) / count($p['scores']), 1) : 0;
            $topics = implode(', ', array_unique(array_slice($p['topics'], 0, 5)));
            $summaryLines[] = "- {$p['post_type']} / {$p['tone']}: avg engagement score {$avg}" . ($topics ? " (topics: {$topics})" : '');
        }

        $summary = "Engagement patterns from real post performance:\n" . implode("\n", $summaryLines);

        DB::table('nux_memory')->upsert([
            'user_id'       => $userId,
            'deployment_id' => $deploymentId,
            'source_key'    => 'performance_patterns',
            'content'       => $summary,
            'created_at'    => now(),
            'updated_at'    => now(),
        ], ['user_id', 'deployment_id', 'source_key'], ['content', 'updated_at']);

        Log::info('NUX memory enriched with performance patterns', [
            'deployment_id' => $deploymentId,
            'patterns'      => count($patterns),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('NuxPerformanceFeedbackJob failed', [
            'deployment_id' => $this->deploymentId,
            'error'         => $e->getMessage(),
        ]);
    }
}
