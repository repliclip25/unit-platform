<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\Exceptions\BillingException;
use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\ClaudeService;
use App\Platform\Services\UsageGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReadEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        // New-transaction gate: trial quota, spend cap, paused — checked before any work
        UsageGuard::checkNew($this->txId);

        $input = UnitPlatform::getInput($this->txId);
        $claude->configure($input->aiModel, $input->userId);
        UnitPlatform::setStatus($this->txId, 'reading');

        $rawEmail = $input->raw['raw_email'] ?? '';

        $system = 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.';

        $prompt = <<<PROMPT
Read the email below and explain what it means.

Return valid JSON only with:
{
  "plain_english_summary": "",
  "what_happened": "",
  "action_needed": "",
  "due_date_or_deadline": "",
  "risk_if_ignored": "",
  "urgency": "Low|Medium|High|Critical",
  "questions_for_memory_lookup": []
}

EMAIL:
{$rawEmail}
PROMPT;

        $output = $claude->ask($system, $prompt, $input->maxTokens('read'), $this->txId, 'read');

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'read',
            status: 'reading',
            data:   $output,
        ));

        UnitPlatform::log('ava', $this->txId, 'email_read', $output);

        ClassifyEmailJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('ava', $this->txId, 'billing_blocked', [
                'code' => $e->code, 'reason' => $e->getMessage(),
            ], 'warning');
            $this->delete(); // don't retry billing blocks
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'ReadEmailJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
