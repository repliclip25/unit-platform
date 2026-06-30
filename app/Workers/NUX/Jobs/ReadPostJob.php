<?php

namespace App\Workers\NUX\Jobs;

use App\Platform\Exceptions\BillingException;
use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\UsageGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ReadPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        UsageGuard::checkNew($this->txId);

        $input = UnitPlatform::getInput($this->txId);
        UnitPlatform::setStatus($this->txId, 'reading');

        $raw    = $input->raw;
        $source = $raw['source'] ?? 'poller';
        $isIdea = $source === 'idea';

        // Idea submissions: no dedup, no min-length enforcement, synthetic platform
        $platform = $isIdea ? 'idea' : ($raw['platform'] ?? 'linkedin');
        $postId   = $isIdea ? ('idea-' . $this->txId) : ($raw['post_id'] ?? null);
        $postText = trim($isIdea ? ($raw['idea_text'] ?? $raw['post_text'] ?? '') : ($raw['post_text'] ?? ''));

        // Dedup: only for poller/fast_track sources with a known post_id
        if (!$isIdea && $postId && $input->deploymentId) {
            $alreadyProcessed = DB::table('nux_post_tracker')
                ->where('deployment_id', $input->deploymentId)
                ->where('platform', $platform)
                ->where('post_id', $postId)
                ->whereNotNull('processed_at')
                ->exists();

            if ($alreadyProcessed) {
                UnitPlatform::setStatus($this->txId, 'skipped');
                UnitPlatform::log('nux', $this->txId, 'post_already_processed', [
                    'post_id'  => $postId,
                    'platform' => $platform,
                ]);
                return;
            }
        }

        $wordCount      = str_word_count($postText);
        $detectedTopics = $this->extractTopics($postText);

        // Record in tracker — ideas get their own row so the user can see pipeline history
        if ($postId && $input->deploymentId) {
            DB::table('nux_post_tracker')->insertOrIgnore([
                'user_id'        => $input->userId,
                'deployment_id'  => $input->deploymentId,
                'platform'       => $platform,
                'post_id'        => $postId,
                'post_url'       => $raw['post_url'] ?? null,
                'post_text'      => $postText,
                'author'         => $isIdea ? ($raw['author'] ?? 'You') : ($raw['author'] ?? null),
                'posted_at'      => isset($raw['posted_at']) ? now()->parse($raw['posted_at']) : now(),
                'transaction_id' => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'read_post',
            status: 'reading',
            data:   [
                'post_text'       => $postText,
                'platform'        => $platform,
                'post_url'        => $raw['post_url'] ?? null,
                'author'          => $isIdea ? ($raw['author'] ?? 'You') : ($raw['author'] ?? null),
                'posted_at'       => $raw['posted_at'] ?? now()->toIso8601String(),
                'word_count'      => $wordCount,
                'detected_topics' => $detectedTopics,
                'target_channels' => $raw['target_channels'] ?? [],
                'source'          => $source,
                'is_idea'         => $isIdea,
            ],
        ));

        UnitPlatform::log('nux', $this->txId, $isIdea ? 'idea_received' : 'post_read', [
            'platform'   => $platform,
            'post_id'    => $postId,
            'word_count' => $wordCount,
            'source'     => $source,
        ]);

        ClassifyPostJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('nux', $this->txId, 'billing_blocked', [
                'code' => $e->billingCode, 'reason' => $e->getMessage(),
            ], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('nux', $this->txId, 'job_failed', [
            'job' => 'ReadPostJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function extractTopics(string $text): array
    {
        // Lightweight keyword extraction — no AI needed at this stage
        $stopWords = ['the', 'and', 'for', 'you', 'your', 'that', 'with', 'this', 'have',
                      'are', 'was', 'will', 'can', 'from', 'not', 'but', 'they', 'been'];

        preg_match_all('/\b[a-zA-Z]{5,}\b/', strtolower($text), $matches);
        $words   = array_diff($matches[0], $stopWords);
        $counts  = array_count_values($words);
        arsort($counts);

        return array_keys(array_slice($counts, 0, 5, true));
    }
}
