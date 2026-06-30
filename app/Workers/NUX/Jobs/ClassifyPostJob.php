<?php

namespace App\Workers\NUX\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyPostJob implements ShouldQueue
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
        $claude->configure($input->aiModel, $input->userId);
        UnitPlatform::setStatus($this->txId, 'classifying');

        $read     = $input->stage('read_post');
        $postText = $read['post_text'] ?? '';
        $isIdea   = (bool) ($read['is_idea'] ?? false);

        // Ideas are always worth classifying — skip length gate
        $depRow    = \Illuminate\Support\Facades\DB::table('worker_deployments')->where('id', $input->deploymentId)->first();
        $config    = json_decode($depRow?->config ?? '{}', true);
        $minLength = (int) ($config['min_post_length'] ?? 100);

        if (!$isIdea && mb_strlen($postText) < $minLength) {
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'classify',
                status: 'classifying',
                data:   [
                    'post_type'       => 'other',
                    'topic'           => '',
                    'tone'            => 'conversational',
                    'repurpose_value' => 'low',
                    'confidence'      => 1.0,
                    'skip_reason'     => "Post is too short ({$minLength} char minimum).",
                ],
            ));

            UnitPlatform::setStatus($this->txId, 'skipped');
            UnitPlatform::log('nux', $this->txId, 'post_too_short', ['length' => mb_strlen($postText)]);
            return;
        }

        $override = UnitPlatform::getPromptOverride($input->deploymentId, 'classify') ?? [];
        $system   = $override['system'] ?? 'You are NUX, a social media content strategist. Return valid JSON only. No extra text.';

        $defaultPrompt = <<<PROMPT
Analyze this social media post and classify it.

Return JSON only:
{
  "post_type": "thought_leadership|tip|story|product|other",
  "topic": "",
  "tone": "conversational|professional|motivational|educational",
  "repurpose_value": "high|medium|low",
  "confidence": 0.0,
  "skip_reason": ""
}

Only set skip_reason if repurpose_value is low.

POST:
{$postText}
PROMPT;

        $prompt = !empty($override['user'])
            ? str_replace('{POST_TEXT}', $postText, $override['user'])
            : $defaultPrompt;

        $maxTokens = $override['max_tokens'] ?? 256;
        $output    = $claude->ask($system, $prompt, $maxTokens, $this->txId, 'classify');

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:    'classify',
            status:   'classifying',
            data:     $output,
            category: $output['post_type'] ?? null,
            priority: null,
        ));

        UnitPlatform::log('nux', $this->txId, 'post_classified', [
            'post_type'       => $output['post_type']       ?? null,
            'repurpose_value' => $output['repurpose_value'] ?? null,
            'confidence'      => $output['confidence']      ?? null,
        ]);

        // Skip low-value posts — no further processing
        if (($output['repurpose_value'] ?? 'low') === 'low') {
            UnitPlatform::setStatus($this->txId, 'skipped');
            UnitPlatform::log('nux', $this->txId, 'post_skipped', [
                'reason' => $output['skip_reason'] ?? 'Low repurpose value',
            ]);
            return;
        }

        RepurposePostJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof \App\Platform\Exceptions\BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('nux', $this->txId, 'billing_blocked', [
                'code' => $e->billingCode, 'reason' => $e->getMessage(),
            ], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('nux', $this->txId, 'job_failed', [
            'job' => 'ClassifyPostJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
