<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\Services\EmailDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs every Monday at 8AM.
 * Replaces DailySummaryJob — weekly cadence is far less intrusive and
 * gives users meaningful volume to celebrate rather than daily noise.
 * No Claude generation — stats are DB-computed, cost is near zero.
 */
class WeeklySummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $ctx = UnitPlatform::getDeploymentContext($this->deploymentId);
        if (!$ctx) return;

        [$deployment, $user] = [$ctx->deployment, $ctx->user];

        $weekStart = now()->subDays(7)->startOfDay();
        $weekEnd   = now()->endOfDay();

        $txs = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get();

        // Don't send an empty report
        if ($txs->isEmpty()) {
            UnitPlatform::log('ava', 'weekly', 'weekly_summary_skipped', [
                'reason'        => 'No transactions this week',
                'deployment_id' => $this->deploymentId,
            ]);
            return;
        }

        $processed   = $txs->count();
        $drafts      = $txs->whereIn('status', ['draft_ready', 'approved', 'sent'])->count();
        $urgent      = $txs->whereIn('priority', ['High', 'Critical'])->count();
        $newClients  = DB::table('clients')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        // Approximate time saved: ~9 minutes per email handled manually
        $minutesSaved = $processed * 9;
        $hoursSaved   = round($minutesSaved / 60, 1);

        $weekLabel = $weekStart->format('M j') . ' – ' . now()->format('M j');

        $appUrl = config('app.url');

        $body = "Hi {$user->name},\n\n";
        $body .= "Here's what Ava accomplished this week ({$weekLabel}):\n\n";
        $body .= "• Processed {$processed} renewal email" . ($processed !== 1 ? 's' : '') . "\n";
        $body .= "• Prepared {$drafts} draft" . ($drafts !== 1 ? 's' : '') . "\n";
        $body .= "• Saved you approximately {$hoursSaved} hour" . ($hoursSaved !== 1.0 ? 's' : '') . "\n";

        if ($urgent > 0) {
            $body .= "• Flagged {$urgent} urgent renewal" . ($urgent !== 1 ? 's' : '') . " for your attention\n";
        }

        if ($newClients > 0) {
            $body .= "• Learned {$newClients} new client" . ($newClients !== 1 ? 's' : '') . "\n";
        }

        $body .= "\nThanks for trusting Ava with your workflow.\n\n";
        $body .= "Open Dashboard: {$appUrl}/dashboard\n\n";
        $body .= "Franklin at UNIT";

        $subject = "Here's what Ava accomplished this week.";

        EmailDispatcher::send('weekly_summary', $user->email, $user->name, $user->id, [
            '{week_label}'    => $weekLabel,
            '{summary_body}'  => $body,
        ]);

        UnitPlatform::log('ava', 'weekly', 'weekly_summary_sent', [
            'week'          => $weekLabel,
            'processed'     => $processed,
            'drafts'        => $drafts,
            'deployment_id' => $this->deploymentId,
            'sent_to'       => $user->email,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', 'weekly', 'job_failed', [
            'job' => 'WeeklySummaryJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
