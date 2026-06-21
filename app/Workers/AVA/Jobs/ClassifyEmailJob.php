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

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        $input = UnitPlatform::getInput($this->txId);
        $claude->configure($input->aiModel, $input->userId);
        UnitPlatform::setStatus($this->txId, 'classifying');

        $readOutput = $input->stage('read');

        $system = 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.';

        $prompt = <<<PROMPT
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

        $output = $claude->ask($system, $prompt, $input->maxTokens('classify'), $this->txId, 'classify');

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:    'classify',
            status:   'classifying',
            data:     $output,
            category: $output['category'] ?? null,
            priority: $output['priority'] ?? null,
        ));

        UnitPlatform::log('ava', $this->txId, 'email_classified', $output);

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
