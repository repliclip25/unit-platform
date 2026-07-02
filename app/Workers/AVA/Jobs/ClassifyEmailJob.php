<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerEvent;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyEmailJob implements ShouldQueue
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
        $input = UnitPlatform::getInput($this->txId);
        // Use Haiku for classification — cheap, fast, sufficient for categorization
        $claude->configure('claude-haiku-4-5-20251001', $input->userId);
        UnitPlatform::setStatus($this->txId, 'classifying');

        $readOutput = $input->stage('read');

        $override = UnitPlatform::getPromptOverride($input->deploymentId, 'classify') ?? [];

        $system = $override['system'] ?? 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.';

        $defaultPrompt = <<<PROMPT
Classify this transaction using the email understanding below.

Available categories:
- Domain Renewal
- SSL Expiry
- Hosting Invoice
- SaaS Renewal
- Failed Payment
- Security Alert
- Meeting Request
- Client Support
- Other

Return JSON:
{
  "category": "",
  "subcategory": "",
  "priority": "Low|Medium|High|Critical",
  "required_action": "",
  "register_to_update": "",
  "status": "",
  "reason": ""
}

CONTEXT:
{$this->jsonPretty($readOutput)}
PROMPT;
        $contextJson = $this->jsonPretty($readOutput);
        $prompt = !empty($override['user'])
            ? str_replace('{READ_OUTPUT}', $contextJson, $override['user'])
            : $defaultPrompt;

        $maxTokens = $override['max_tokens'] ?? $input->maxTokens('classify');
        $output    = $claude->ask($system, $prompt, $maxTokens, $this->txId, 'classify');

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:    'classify',
            status:   'classifying',
            data:     $output,
            category: $output['category'] ?? null,
            priority: $output['priority'] ?? null,
        ));

        UnitPlatform::log('ava', $this->txId, 'email_classified', $output);

        // ── Early exit: stop pipeline for non-renewal categories to avoid wasting AI spend
        $renewalCategories = [
            'Domain Renewal', 'SSL Expiry', 'Hosting Invoice',
            'SaaS Renewal', 'Failed Payment',
        ];
        $category = $output['category'] ?? 'Other';
        if (!in_array($category, $renewalCategories)) {
            UnitPlatform::setStatus($this->txId, 'dismissed');
            UnitPlatform::log('ava', $this->txId, 'pipeline_skipped', [
                'reason'   => 'non_renewal_category',
                'category' => $category,
            ], 'info');
            return;
        }

        // ── Break-injection: early-stage packet from read output only (memory not yet run).
        //    'renewal.draft_ready' carries the full enriched handover after memory lookup.
        UnitPlatform::emit($this->txId, new WorkerEvent('renewal.classified', [
            'category'    => $output['category']         ?? null,
            'subcategory' => $output['subcategory']      ?? null,
            'priority'    => $output['priority']         ?? null,
            'action'      => $output['required_action']  ?? null,
            'summary'     => $readOutput['plain_english_summary'] ?? null,

            'asset' => [
                'name'      => $readOutput['asset_name']         ?? null,
                'type'      => $readOutput['asset_type']         ?? null,
                'registrar' => $readOutput['sender_company']     ?? null,
                'expiry'    => $readOutput['expiry_date']        ?? null,
                'days_left' => $readOutput['days_until_expiry']  ?? null,
            ],

            'ava' => [
                'classified_at' => now()->toISOString(),
            ],
        ]));

        MemoryLookupJob::dispatch($this->txId)->onQueue($input->queue);
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
            'job' => 'ClassifyEmailJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function jsonPretty(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
