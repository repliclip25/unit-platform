<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DraftEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    // Set by ClientReminderCycleJob when re-drafting the 15/0-day reminder —
    // null on the initial run, where the actual day count is whatever
    // triggered detection in the first place.
    public function __construct(public string $txId, public ?int $daysBeforeExpiry = null) {}

    public function handle(ClaudeService $claude): void
    {
        $input        = UnitPlatform::getInput($this->txId);
        $claude->configure($input->modelFor('draft'), $input->userId, $input->workerSlug);
        $memory       = $input->stage('memory');
        $classify     = $input->stage('classify');
        $baseTemplate = $input->stage('template');
        $read         = $input->stage('read');

        // Rounds 2/3 (dispatched by ClientReminderCycleJob with the next
        // cadence threshold) re-resolve the template instead of reusing
        // round 1's frozen template_output — a tenant may have written
        // distinct wording for the 2nd/3rd reminder under the same category.
        $roundNumber = match ($this->daysBeforeExpiry) {
            15      => 2,
            0       => 3,
            default => 1,
        };

        UnitPlatform::setStatus($this->txId, 'drafting');

        $drafted = $this->draftForRound($roundNumber, $claude, $input, $memory, $classify, $read, $baseTemplate);

        $output = [
            'to'                 => $drafted['to'],
            'subject'            => $drafted['subject'],
            'body'               => $drafted['body'],
            'human_review_note'  => $drafted['reviewNote'],
            'gmail_draft_action' => 'Create Gmail draft only',
            'low_confidence'     => $drafted['lowConfidence'],
        ];

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'draft',
            status: 'drafting',
            data:   $output,
        ));

        // Full history of every client-facing reminder drafted for this
        // transaction — up to 3, on the 30/15/0-day cadence. draft_output
        // above stays as "most recent" for anything still reading it directly.
        $reminderNumber = UnitPlatform::recordClientDraft(
            $this->txId, $output['to'], $output['subject'], $output['body'], $this->daysBeforeExpiry
        );

        UnitPlatform::log('ava', $this->txId, 'draft_created', [
            'to'              => $output['to'],
            'subject'         => $output['subject'],
            'low_confidence'  => $drafted['lowConfidence'],
            'reminder_number' => $reminderNumber,
        ]);

        // Round 1's first-ever draft — approving it now authorizes the
        // whole cadence (see ClientReminderCycleJob / PushToGmailJob), so
        // generate rounds 2 and 3 as previews right now too, using what's
        // known today. Not what actually gets sent later — each round
        // still regenerates fresh against real conditions when its real
        // threshold hits (recordClientDraft() replaces the preview in
        // place). Skipped for Fast Track (no real calendar days to preview
        // against) and when client_cadence is off (round 1 is the only
        // round, there's nothing to preview).
        if ($roundNumber === 1
            && !$input->isFastTrack()
            && UnitPlatform::gateEnabled($input->deploymentId, 'client_cadence', true)
        ) {
            foreach ([2 => 15, 3 => 0] as $previewRound => $previewThreshold) {
                $preview = $this->draftForRound($previewRound, $claude, $input, $memory, $classify, $read, $baseTemplate);
                UnitPlatform::recordClientDraftPreview(
                    $this->txId, $previewRound, $previewThreshold, $preview['to'], $preview['subject'], $preview['body']
                );
            }
        }

        // human_decide now sits right after this stage (was after push_draft
        // — see AvaWorker::pipelineStages()'s comment on why that moved).
        // This job is the one that decides whether the gate should even
        // halt: no approval needed at all, cadence already authorized by an
        // earlier round, or a genuine first decision is required.
        $cadence = UnitPlatform::getCadenceState($this->txId);

        if (!$drafted['approvalRequired'] && !$input->isFastTrack()) {
            // No human ever needs to see this — push immediately (auto-sends
            // inside PushToGmailJob) and continue straight into fulfillment.
            // Fast Track is exempt regardless of the computed value — it
            // always demonstrates the full human-decide flow, never
            // auto-sends to a real contact during a test run.
            PushToGmailJob::dispatch($this->txId, autoSend: true, advanceAfter: true)->onQueue($input->queue);
        } elseif ($cadence['approvedOnce']) {
            // Cadence already authorized by round 1's approval — push this
            // round without asking again. Only continue into fulfillment if
            // this is the final round; earlier rounds deliver and wait for
            // the next threshold (see ClientReminderCycleJob).
            $isFinalRound = $cadence['reminderNumber'] >= 3;
            PushToGmailJob::dispatch($this->txId, autoSend: $cadence['sendModeDirect'], advanceAfter: $isFinalRound)->onQueue($input->queue);
        } else {
            // Genuinely needs a first decision — mark ready for review (the
            // status a human sees while it waits at the gate; PushToGmailJob
            // no longer runs before a decision, so nothing sets this label
            // otherwise now) and halt.
            UnitPlatform::setStatus($this->txId, 'draft_ready');
            UnitPlatform::advance($this->txId, 'draft_email');
        }
    }

    /**
     * Drafts one round's subject/body — round 1's real draft, a later
     * round's real redraft (dispatched by ClientReminderCycleJob), or a
     * round 2/3 preview generated alongside round 1. Same logic either
     * way; the caller decides what to do with the result (commit as real
     * output, or store as a preview).
     */
    private function draftForRound(int $roundNumber, ClaudeService $claude, \App\Platform\SDK\WorkerInput $input, array $memory, array $classify, array $read, array $baseTemplate): array
    {
        $template = $baseTemplate;
        if ($roundNumber > 1) {
            $roundTemplate = \App\Platform\Services\TemplateResolver::resolve(
                $classify['category'] ?? 'Other', $input->memory['templates'], $input->memory['templates_default'], $roundNumber
            );
            if ($roundTemplate) $template = $roundTemplate;
        }

        $lowConfidence        = !empty($memory['low_confidence_warning']);
        $lowConfidenceWarning = $memory['low_confidence_warning'] ?? null;

        $contactName = $memory['primary_contact_name'] ?? 'there';
        $firstName   = explode(' ', $contactName)[0];
        $subject     = $this->fillPlaceholders($template['subject_template'] ?? '', $memory, $read, $firstName);
        $body        = $this->fillPlaceholders($template['body_template']    ?? '', $memory, $read, $firstName);

        if (strlen($body) < 100 || $lowConfidence) {
            $body = $this->generateWithClaude($claude, $memory, $classify, $read, $template, $firstName, $lowConfidence);
        }

        // Every existing template predates {{line_items}} — a bundle
        // shouldn't depend on a tenant remembering to add the placeholder
        // to get a correct, informative draft. If it's present in memory
        // but the template text never referenced it (so fillPlaceholders()
        // had nothing to substitute), append the breakdown automatically.
        $lineItems = $memory['line_items'] ?? null;
        if ($lineItems && !str_contains($template['body_template'] ?? '', '{{line_items}}')) {
            $body = rtrim($body) . "\n\n" . $this->formatLineItems($lineItems);
        }

        $reviewNote = $lowConfidence
            ? "⚠️ LOW CONFIDENCE MATCH ({$memory['confidence']}%). {$lowConfidenceWarning} Review carefully before sending."
            : 'Review before sending. No work has been confirmed or promised.';

        // Relocated from PushToGmailJob — now that human_decide sits right
        // after this stage instead of after push_draft, this job is the one
        // that has to decide whether the gate should even halt. Any one of
        // the three is enough to force human review; none can be overridden
        // by the others.
        $approvalRequired = (bool) ($template['approval_required'] ?? true)
            || (bool) ($memory['rule_requires_approval'] ?? false)
            || $lowConfidence;

        return [
            'to'               => $memory['primary_contact_email'] ?? '',
            'subject'          => $subject,
            'body'             => $body,
            'reviewNote'       => $reviewNote,
            'lowConfidence'    => $lowConfidence,
            'approvalRequired' => $approvalRequired,
        ];
    }

    private function fillPlaceholders(string $tpl, array $memory, array $read, string $firstName): string
    {
        $sanitize = fn(?string $v): string => trim(str_replace(["\r\n", "\r", "\n"], ' ', $v ?? ''));

        return str_replace(
            ['{{contact_first_name}}', '{{contact_name}}', '{{asset}}', '{{client}}', '{{due_date}}', '{{sender_name}}', '{{line_items}}'],
            [
                $sanitize($firstName),
                $sanitize($memory['primary_contact_name'] ?? ''),
                $sanitize($memory['asset'] ?? ''),
                $sanitize($memory['matched_client'] ?? ''),
                $sanitize($read['due_date_or_deadline'] ?? ''),
                'Franklin',
                // Deliberately not run through $sanitize — this is the one
                // placeholder meant to span multiple lines (a bundle's
                // per-asset breakdown), everything else here is a single
                // value that shouldn't wrap.
                $this->formatLineItems($memory['line_items'] ?? null),
            ],
            $tpl
        );
    }

    // Renders a renews_together bundle's per-asset breakdown as a short
    // bullet list — empty string (nothing to insert) for a normal
    // single-asset transaction, so {{line_items}} is safe to leave in a
    // template unconditionally without it ever printing something odd.
    private function formatLineItems(?array $lineItems): string
    {
        if (!$lineItems) return '';

        return collect($lineItems)->map(function ($item) {
            $date = $item['renewal_date'] ?? null;
            $when = $date ? \Carbon\Carbon::parse($date)->format('M j, Y') : 'date on file';
            return "- {$item['name']} ({$item['type']}) — renews {$when}";
        })->implode("\n");
    }

    private function generateWithClaude(ClaudeService $claude, array $memory, array $classify, array $read, array $template, string $firstName, bool $lowConfidence): string
    {
        $input   = UnitPlatform::getInput($this->txId);
        // Key matches the pipeline stage ('draft_email') and prompts()'s
        // declared default — the Configure page saves overrides under this
        // exact key, so querying the short 'draft' tag here was a silent
        // no-op: any tenant edit to this card never applied.
        $override = UnitPlatform::getPromptOverride($input->deploymentId, 'draft_email') ?? [];

        $lowNote = $lowConfidence
            ? "\n\nIMPORTANT: Memory match confidence is low ({$memory['confidence']}%). Keep the draft general enough to work even if the asset match is slightly off."
            : '';

        $bundleNote = !empty($memory['line_items'])
            ? "\n\nThis is a bundled renewal covering multiple services — mention that several items are renewing together, but don't list them individually in your prose; the itemized breakdown is appended separately after your draft."
            : '';

        $system = $override['system'] ?? 'You are Ava, a professional email coordinator. Return only the email body — no subject line, no JSON, no extra text.';

        if ($override['user'] ?? null) {
            $prompt = str_replace(
                ['{FIRST_NAME}', '{ASSET}', '{CLIENT}', '{DUE_DATE}', '{CATEGORY}', '{APPROVAL_REQUIRED}', '{SENDER_NAME}', '{TEMPLATE_NAME}', '{TONE}', '{BODY_TEMPLATE}'],
                [$firstName, $memory['asset'] ?? '', $memory['matched_client'] ?? '', $read['due_date_or_deadline'] ?? '', $classify['category'] ?? '', $template['approval_required'] ?? '', 'Franklin', $template['template_name'] ?? '', $template['tone'] ?? '', $template['body_template'] ?? ''],
                $override['user']
            );
        } else {
            $prompt = <<<PROMPT
Write an email body using the template structure below.

Template style: {$template['template_name']}
Tone: {$template['tone']}
Template body to follow:
{$template['body_template']}

Fill in:
- Contact first name: {$firstName}
- Asset: {$memory['asset']}
- Client: {$memory['matched_client']}
- Due date: {$read['due_date_or_deadline']}
- Category: {$classify['category']}
- Approval required: {$template['approval_required']}
- Sign as: Franklin
{$lowNote}{$bundleNote}

Rules:
- Keep it concise
- Do not promise work is done
- Ask for approval when required
- Return only the email body
PROMPT;
        }

        return $claude->askForText($system, $prompt, $override['max_tokens'] ?? 1024, $this->txId, 'draft');
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof \App\Platform\Exceptions\BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('ava', $this->txId, 'billing_blocked', ['code' => $e->billingCode, 'reason' => $e->getMessage()], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'DraftEmailJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
