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
use Illuminate\Support\Facades\DB;

class RepurposePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        $input = UnitPlatform::getInput($this->txId);
        $claude->configure($input->aiModel, $input->userId);
        UnitPlatform::setStatus($this->txId, 'repurposing');

        $read     = $input->stage('read_post');
        $classify = $input->stage('classify');

        $postText       = $read['post_text'] ?? '';
        $sourcePlatform = $read['platform'] ?? 'linkedin';
        $targetChannels = $read['target_channels'] ?? [];
        $postType       = $classify['post_type'] ?? 'other';
        $tone           = $classify['tone'] ?? 'conversational';
        $topic          = $classify['topic'] ?? '';

        // Load memory: brand voice and channel rules from tenant rules table
        $brandVoice   = $this->loadMemory($input->userId, $input->deploymentId, 'brand_voice');
        $channelRules = $this->loadMemory($input->userId, $input->deploymentId, 'channel_rules');
        $imageStyle   = $this->loadMemory($input->userId, $input->deploymentId, 'image_style');

        $override = UnitPlatform::getPromptOverride($input->deploymentId, 'repurpose') ?? [];
        $system   = $override['system'] ?? 'You are NUX, a social media content strategist. Return valid JSON only. No extra text.';

        $channelList = implode(', ', $targetChannels) ?: 'x';

        $defaultPrompt = <<<PROMPT
Repurpose this {$sourcePlatform} post for each target channel listed below.

For each channel, write a new version that fits its native format and character limits:
- x: max 280 chars, punchy hook, no hashtag spam
- linkedin: can be longer, hook in line 1, can include 3-5 hashtags

Also write a DALL-E 3 image prompt for a visual that matches this post's topic and tone.

Brand voice: {$brandVoice}
Channel rules: {$channelRules}
Image style: {$imageStyle}

Return JSON:
{
  "repurposed_copies": [
    {"channel": "", "copy": "", "char_count": 0}
  ],
  "image_prompt": "",
  "image_needed": true
}

Target channels: {$channelList}

ORIGINAL POST:
{$postText}
PROMPT;

        $prompt = !empty($override['user'])
            ? str_replace(
                ['{SOURCE_PLATFORM}', '{POST_TEXT}', '{TARGET_CHANNELS}', '{BRAND_VOICE}', '{CHANNEL_RULES}'],
                [$sourcePlatform, $postText, $channelList, $brandVoice, $channelRules],
                $override['user']
            )
            : $defaultPrompt;

        $maxTokens = $override['max_tokens'] ?? 1024;
        $output    = $claude->ask($system, $prompt, $maxTokens, $this->txId, 'repurpose');

        // Inject actual char counts (AI tends to miscalculate)
        if (!empty($output['repurposed_copies'])) {
            $output['repurposed_copies'] = array_map(function ($copy) {
                $copy['char_count'] = mb_strlen($copy['copy'] ?? '');
                return $copy;
            }, $output['repurposed_copies']);
        }

        // Check deployment config: if generate_image is false, override
        $depRow = \Illuminate\Support\Facades\DB::table('worker_deployments')->where('id', $input->deploymentId)->first();
        $config = json_decode($depRow?->config ?? '{}', true);
        if (isset($config['generate_image']) && !$config['generate_image']) {
            $output['image_needed'] = false;
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'repurpose',
            status: 'repurposing',
            data:   array_merge($output, ['topic' => $topic]),
        ));

        UnitPlatform::log('nux', $this->txId, 'post_repurposed', [
            'channels'     => $targetChannels,
            'copy_count'   => count($output['repurposed_copies'] ?? []),
            'image_needed' => $output['image_needed'] ?? true,
        ]);

        MediaJob::dispatch($this->txId)->onQueue($input->queue);
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
            'job' => 'RepurposePostJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function loadMemory(int $userId, int $deploymentId, string $key): string
    {
        // Deployment-level memory wins over tenant-global
        $row = DB::table('nux_memory')
            ->where('user_id', $userId)
            ->where('source_key', $key)
            ->where(function ($q) use ($deploymentId) {
                $q->where('deployment_id', $deploymentId)->orWhereNull('deployment_id');
            })
            ->orderByDesc('deployment_id')
            ->value('content');

        return $row ?? '';
    }
}
