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

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        $input    = UnitPlatform::getInput($this->txId);
        $claude->configure($input->modelFor('draft'), $input->userId, $input->workerSlug);
        $memory   = $input->stage('memory');
        $classify = $input->stage('classify');
        $template = $input->stage('template');
        $read     = $input->stage('read');

        UnitPlatform::setStatus($this->txId, 'drafting');

        $lowConfidence        = !empty($memory['low_confidence_warning']);
        $lowConfidenceWarning = $memory['low_confidence_warning'] ?? null;

        $contactName = $memory['primary_contact_name'] ?? 'there';
        $firstName   = explode(' ', $contactName)[0];
        $subject     = $this->fillPlaceholders($template['subject_template'] ?? '', $memory, $read, $firstName);
        $body        = $this->fillPlaceholders($template['body_template']    ?? '', $memory, $read, $firstName);

        if (strlen($body) < 100 || $lowConfidence) {
            $body = $this->generateWithClaude($claude, $memory, $classify, $read, $template, $firstName, $lowConfidence);
        }

        $reviewNote = $lowConfidence
            ? "⚠️ LOW CONFIDENCE MATCH ({$memory['confidence']}%). {$lowConfidenceWarning} Review carefully before sending."
            : 'Review before sending. No work has been confirmed or promised.';

        $output = [
            'to'                 => $memory['primary_contact_email'] ?? '',
            'subject'            => $subject,
            'body'               => $body,
            'human_review_note'  => $reviewNote,
            'gmail_draft_action' => 'Create Gmail draft only',
            'low_confidence'     => $lowConfidence,
        ];

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'draft',
            status: 'drafting',
            data:   $output,
        ));

        UnitPlatform::log('ava', $this->txId, 'draft_created', [
            'to'             => $output['to'],
            'subject'        => $output['subject'],
            'low_confidence' => $lowConfidence,
        ]);

        UnitPlatform::advance($this->txId, 'draft_email');
    }

    private function fillPlaceholders(string $tpl, array $memory, array $read, string $firstName): string
    {
        $sanitize = fn(?string $v): string => trim(str_replace(["\r\n", "\r", "\n"], ' ', $v ?? ''));

        return str_replace(
            ['{{contact_first_name}}', '{{contact_name}}', '{{asset}}', '{{client}}', '{{due_date}}', '{{sender_name}}'],
            [
                $sanitize($firstName),
                $sanitize($memory['primary_contact_name'] ?? ''),
                $sanitize($memory['asset'] ?? ''),
                $sanitize($memory['matched_client'] ?? ''),
                $sanitize($read['due_date_or_deadline'] ?? ''),
                'Franklin',
            ],
            $tpl
        );
    }

    private function generateWithClaude(ClaudeService $claude, array $memory, array $classify, array $read, array $template, string $firstName, bool $lowConfidence): string
    {
        $input   = UnitPlatform::getInput($this->txId);
        $override = UnitPlatform::getPromptOverride($input->deploymentId, 'draft') ?? [];

        $lowNote = $lowConfidence
            ? "\n\nIMPORTANT: Memory match confidence is low ({$memory['confidence']}%). Keep the draft general enough to work even if the asset match is slightly off."
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
{$lowNote}

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
