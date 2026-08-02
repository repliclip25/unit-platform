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
 * 'confirm_payment' hard gate and re-nudges the tenant — AVA "hunts for
 * user actions via email reminders on various cadence based on priority"
 * rather than silently waiting forever for a click. Tone escalates by
 * attempt number; after ReminderCopy::MAX_ATTEMPTS with no response,
 * nudging pauses rather than nagging forever — see nudging_paused_at.
 */
class PaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    // Days between reminders, by classify_output.priority — more urgent
    // renewals get chased more often.
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

        // Message gate — "Nudge Me" master switch on AVA Settings.
        if (!UnitPlatform::gateEnabled($dep->id, 'nudge_me', true)) return;

        $stuck = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('fulfillment_stage', 'confirm_payment')
            ->whereNull('nudging_paused_at')
            ->get();

        $userTemplates = DB::table('email_templates')
            ->where('user_id', $dep->user_id)->where('worker_slug', 'ava')
            ->where('stage_key', 'confirm_payment')->where('active', true)->get()->all();
        $defaultTemplates = DB::table('email_templates')
            ->whereNull('user_id')->where('worker_slug', 'ava')
            ->where('stage_key', 'confirm_payment')->get()->all();

        foreach ($stuck as $tx) {
            $priority = $tx->priority ?? 'Medium';
            $cadence  = self::CADENCE_DAYS[$priority] ?? self::CADENCE_DAYS['Medium'];

            // Carbon::diffInDays() returns a signed value whose sign depends
            // on call order/version — comparing against an explicit cutoff
            // instant avoids that ambiguity entirely.
            $lastSent = $tx->payment_reminder_sent_at ?? $tx->updated_at;
            if (\Carbon\Carbon::parse($lastSent)->gt(now()->subDays($cadence))) continue;

            $tenantEmail = DB::table('users')->where('id', $tx->user_id)->value('email');
            if (!$tenantEmail) continue;

            $memory     = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $asset      = $memory['asset'] ?? 'this item';
            $reminders  = json_decode($tx->reminders ?? '[]', true) ?: [];
            $attempt    = count(array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === 'confirm_payment')) + 1;

            if ($attempt > ReminderCopy::MAX_ATTEMPTS) {
                DB::table('transactions')->where('id', $tx->id)->update(['nudging_paused_at' => now()]);
                UnitPlatform::log('ava', $tx->tx_id, 'nudging_paused', ['stage_key' => 'confirm_payment', 'attempts' => $attempt - 1], 'warning');
                continue;
            }

            $tone     = ReminderCopy::tone($attempt);
            $template = \App\Platform\Services\TemplateResolver::resolveByStage($userTemplates, $defaultTemplates, $tone);
            $subject  = str_replace('{{asset}}', $asset, $template['subject_template'] ?? "Confirm payment for {$asset}?");
            $body     = str_replace('{{asset}}', $asset, $template['body_template']    ?? "Hi,\n\nJust checking in on payment for the {$asset} renewal.\n\n— AVA");

            EmailDispatcher::send(
                'ava_payment_reminder',
                $tenantEmail,
                'there',
                $tx->user_id,
                ['{asset}' => $asset, '{tx_id}' => $tx->tx_id],
                ['subject' => $subject, 'body' => $body]
            );

            UnitPlatform::recordReminder($tx->tx_id, 'confirm_payment', $subject, $body);
            DB::table('transactions')->where('id', $tx->id)->update(['payment_reminder_sent_at' => now()]);

            UnitPlatform::log('ava', $tx->tx_id, 'payment_reminder_sent', ['priority' => $priority, 'cadence_days' => $cadence, 'attempt' => $attempt]);
        }
    }
}
