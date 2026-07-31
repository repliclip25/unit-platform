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
 * Runs daily per active AVA deployment. Request Invoice is a soft gate —
 * the pipeline never waits on it — but AVA still chases the tenant to
 * attach one if they haven't, same escalating cadence and pause-after-N
 * pattern as the two hard gates. Never blocks anything; a transaction can
 * be fully renewed with an unattached invoice.
 */
class InvoiceNudgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    private const CADENCE_DAYS = [
        'Critical' => 2,
        'High'     => 3,
        'Medium'   => 5,
        'Low'      => 7,
    ];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        $notAttached = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('is_test', false)
            ->whereNull('nudging_paused_at')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(invoice_output, '$.status')) = 'not_attached'")
            ->get();

        $userTemplates = DB::table('email_templates')
            ->where('user_id', $dep->user_id)->where('worker_slug', 'ava')
            ->where('stage_key', 'request_invoice_nudge')->where('active', true)->get()->all();
        $defaultTemplates = DB::table('email_templates')
            ->whereNull('user_id')->where('worker_slug', 'ava')
            ->where('stage_key', 'request_invoice_nudge')->get()->all();

        foreach ($notAttached as $tx) {
            $priority = $tx->priority ?? 'Medium';
            $cadence  = self::CADENCE_DAYS[$priority] ?? self::CADENCE_DAYS['Medium'];

            $reminders  = json_decode($tx->reminders ?? '[]', true) ?: [];
            $invoiceReminders = array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === 'request_invoice');
            $lastSent   = collect($invoiceReminders)->last()['sent_at'] ?? $tx->updated_at;

            if (\Carbon\Carbon::parse($lastSent)->gt(now()->subDays($cadence))) continue;

            $tenantEmail = DB::table('users')->where('id', $tx->user_id)->value('email');
            if (!$tenantEmail) continue;

            $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $asset   = $memory['asset'] ?? 'this item';
            $attempt = count($invoiceReminders) + 1;

            if ($attempt > ReminderCopy::MAX_ATTEMPTS) {
                DB::table('transactions')->where('id', $tx->id)->update(['nudging_paused_at' => now()]);
                UnitPlatform::log('ava', $tx->tx_id, 'nudging_paused', ['stage_key' => 'request_invoice', 'attempts' => $attempt - 1], 'warning');
                continue;
            }

            $tone     = ReminderCopy::tone($attempt);
            $template = \App\Platform\Services\TemplateResolver::resolveByStage($userTemplates, $defaultTemplates, $tone);
            $subject  = str_replace('{{asset}}', $asset, $template['subject_template'] ?? "Got an invoice for {$asset}?");
            $body     = str_replace('{{asset}}', $asset, $template['body_template']    ?? "Hi,\n\nIf you have an invoice for {$asset}, attach it in UNIT.\n\n— AVA");

            EmailDispatcher::send(
                'ava_invoice_nudge', $tenantEmail, 'there', $tx->user_id,
                ['{asset}' => $asset, '{tx_id}' => $tx->tx_id], ['subject' => $subject, 'body' => $body]
            );

            UnitPlatform::recordReminder($tx->tx_id, 'request_invoice', $subject, $body);
            UnitPlatform::log('ava', $tx->tx_id, 'invoice_nudge_sent', ['priority' => $priority, 'attempt' => $attempt]);
        }
    }
}
