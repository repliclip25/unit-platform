<?php

namespace App\Workers\AVA\Jobs;

use App\Mail\DailySummary;
use App\Platform\SDK\UnitPlatform;
use App\Platform\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DailySummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(public int $deploymentId) {}

    public function handle(ClaudeService $claude): void
    {
        // DailySummary is a scheduled job (no tx_id) — UNIT provides the deployment context
        $ctx = UnitPlatform::getDeploymentContext($this->deploymentId);
        if (!$ctx) return;

        [$deployment, $user] = [$ctx->deployment, $ctx->user];

        $today        = now()->toDateString();
        $transactions = UnitPlatform::getRegisterEntries($this->deploymentId, $today)->toArray();

        if (empty($transactions)) {
            UnitPlatform::log('ava', 'daily', 'daily_summary_skipped', [
                'reason'        => 'No transactions today',
                'date'          => $today,
                'deployment_id' => $this->deploymentId,
            ]);
            return;
        }

        $logged        = count($transactions);
        $draftsReady   = collect($transactions)->where('status', 'Draft Ready')->count();
        $urgent        = collect($transactions)->whereIn('priority', ['High', 'Critical'])->count();
        $pendingReview = collect($transactions)->whereNull('human_decision')->whereNotNull('draft_id')->count();

        $context = json_encode(compact('today', 'logged', 'draftsReady', 'urgent', 'pendingReview', 'transactions'), JSON_PRETTY_PRINT);

        $system = 'You are Ava, UNIT\'s Renewal & Subscription Coordinator. Write a concise daily summary email. No JSON — just a plain email body.';
        $prompt = <<<PROMPT
Generate a concise daily summary for {$user->name}.

Structure:
1. What I processed today
2. What I drafted
3. What needs your review
4. Urgent items
5. Recommended next actions

Keep it short and action-focused. Sign as Ava.

DATA:
{$context}
PROMPT;

        $body    = $claude->askForText($system, $prompt);
        $subject = "Ava Daily Summary — {$today} — {$logged} item(s), {$urgent} urgent";

        Mail::to($user->email)->send(new DailySummary($subject, $body, $today, $logged, $urgent));

        UnitPlatform::log('ava', 'daily', 'daily_summary_sent', [
            'date'          => $today,
            'total'         => $logged,
            'deployment_id' => $this->deploymentId,
            'sent_to'       => $user->email,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', 'daily', 'job_failed', [
            'job' => 'DailySummaryJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
