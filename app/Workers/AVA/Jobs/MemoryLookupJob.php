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

class MemoryLookupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        $input = UnitPlatform::getInput($this->txId);
        $claude->configure($input->aiModel, $input->userId);
        UnitPlatform::setStatus($this->txId, 'memory_lookup');

        $readOutput = $input->stage('read');

        // Memory is pre-loaded by UnitPlatform::getInput() — no DB calls needed here
        $memory = [
            'clients'  => $input->memory['clients'],
            'contacts' => $input->memory['contacts'],
            'assets'   => $input->memory['assets'],
            'ava_rules'=> $input->memory['rules'],
        ];

        $system = 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.';

        $prompt = <<<PROMPT
Using the extracted email information and the memory tables below, find who owns this asset and how it should be handled.

Return JSON:
{
  "asset": "",
  "matched_client": "",
  "primary_contact_name": "",
  "primary_contact_email": "",
  "related_project_or_service": "",
  "client_preference": "",
  "ava_rule": "",
  "confidence": 0,
  "missing_information": []
}

EXTRACTED EMAIL CONTEXT:
{$this->jsonPretty($readOutput)}

MEMORY TABLES:
{$this->jsonPretty($memory)}
PROMPT;

        $output = $claude->ask($system, $prompt, $input->maxTokens('memory', 768), $this->txId, 'memory');

        // Low confidence — flag but continue pipeline (AVA-006)
        $confidence = $output['confidence'] ?? 0;
        if ($confidence < 70) {
            UnitPlatform::log('ava', $this->txId, 'low_confidence_flagged', [
                'confidence' => $confidence,
                'rule'       => 'AVA-006',
                'action'     => 'continuing_with_draft',
            ], 'warning');

            $output['low_confidence_warning'] = "AVA confidence is {$confidence}%. "
                . "Client/asset match is uncertain. Please verify before sending.";
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'memory',
            status: 'memory_lookup',
            data:   $output,
        ));

        UnitPlatform::log('ava', $this->txId, 'memory_matched', $output);

        // Best-effort memory contributions — never let this block the pipeline
        try {
            if (!empty($output['primary_contact_email']) && !empty($output['primary_contact_name'])) {
                UnitPlatform::contributeMemory($this->txId, 'contacts', [
                    'name'  => $output['primary_contact_name'],
                    'email' => $output['primary_contact_email'],
                ]);
            }
            if (!empty($output['asset'])) {
                UnitPlatform::contributeMemory($this->txId, 'assets', [
                    'name' => $output['asset'],
                ]);
            }
        } catch (\Throwable $e) {
            UnitPlatform::log('ava', $this->txId, 'memory_contribute_skipped', [
                'error' => $e->getMessage(),
            ], 'warning');
        }

        LogTransactionJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'MemoryLookupJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function jsonPretty(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
