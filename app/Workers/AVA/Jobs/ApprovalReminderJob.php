<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\ReminderCopy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Runs daily per active AVA deployment. Finds transactions stuck at the
 * 'human_decide' hard gate — a draft sitting unapproved in Gmail — and
 * nudges the tenant. This gate previously had no reminder mechanism at
 * all; a draft could sit forever with nothing chasing it. Mirrors
 * PaymentReminderJob's cadence, tone escalation, and pause-after-N-attempts.
 */
class ApprovalReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    private const CADENCE_DAYS = [
        'Critical' => 1,
        'High'     => 2,
        'Medium'   => 4,
        'Low'      => 7,
    ];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        // Message gate — "Nudge Me" master switch on AVA Settings. Off means
        // AVA still waits on the tenant exactly as before, it just stops
        // emailing them about it — they check UNIT themselves instead.
        if (!UnitPlatform::gateEnabled($dep->id, 'nudge_me', true)) return;

        // human_decision IS NULL — a real transaction's client draft is
        // approved/rejected once per round (up to 3, on the 30/15/0-day
        // cadence, see ClientReminderCycleJob); fulfillment_stage stays
        // 'human_decide' across all of them, but between rounds the
        // previous round is already approved and there's genuinely nothing
        // pending — nudging then would be nagging about a decision that's
        // already been made.
        $stuck = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('fulfillment_stage', 'human_decide')
            ->whereNull('human_decision')
            ->whereNull('nudging_paused_at')
            ->get();

        $userTemplates = DB::table('email_templates')
            ->where('user_id', $dep->user_id)->where('worker_slug', 'ava')
            ->where('stage_key', 'human_decide')->where('active', true)->get()->all();
        $defaultTemplates = DB::table('email_templates')
            ->whereNull('user_id')->where('worker_slug', 'ava')
            ->where('stage_key', 'human_decide')->get()->all();

        foreach ($stuck as $tx) {
            $priority = $tx->priority ?? 'Medium';
            $cadence  = self::CADENCE_DAYS[$priority] ?? self::CADENCE_DAYS['Medium'];

            // Carbon::diffInDays() returns a signed value whose sign depends
            // on call order/version — comparing against an explicit cutoff
            // instant avoids that ambiguity entirely.
            $lastSent = $tx->approval_reminder_sent_at ?? $tx->updated_at;
            if (\Carbon\Carbon::parse($lastSent)->gt(now()->subDays($cadence))) continue;

            $tenantEmail = DB::table('users')->where('id', $tx->user_id)->value('email');
            if (!$tenantEmail) continue;

            $memory    = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $asset     = $memory['asset'] ?? 'this item';
            $reminders = json_decode($tx->reminders ?? '[]', true) ?: [];
            $attempt   = count(array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === 'human_decide')) + 1;

            if ($attempt > ReminderCopy::MAX_ATTEMPTS) {
                DB::table('transactions')->where('id', $tx->id)->update(['nudging_paused_at' => now()]);
                UnitPlatform::log('ava', $tx->tx_id, 'nudging_paused', ['stage_key' => 'human_decide', 'attempts' => $attempt - 1], 'warning');
                continue;
            }

            $tone     = ReminderCopy::tone($attempt);
            $template = \App\Platform\Services\TemplateResolver::resolveByStage($userTemplates, $defaultTemplates, $tone);
            $subject  = str_replace('{{asset}}', $asset, $template['subject_template'] ?? "A draft is waiting on you — {$asset}");
            $body     = str_replace('{{asset}}', $asset, $template['body_template']    ?? "Hi,\n\nA renewal draft for {$asset} is waiting on your approval.\n\n— AVA");

            EmailDispatcher::send(
                'ava_approval_reminder',
                $tenantEmail,
                'there',
                $tx->user_id,
                ['{asset}' => $asset, '{tx_id}' => $tx->tx_id],
                ['subject' => $subject, 'body' => $body]
            );

            UnitPlatform::recordReminder($tx->tx_id, 'human_decide', $subject, $body);
            DB::table('transactions')->where('id', $tx->id)->update(['approval_reminder_sent_at' => now()]);

            UnitPlatform::log('ava', $tx->tx_id, 'approval_reminder_sent', ['priority' => $priority, 'cadence_days' => $cadence, 'attempt' => $attempt]);
        }
    }
}
