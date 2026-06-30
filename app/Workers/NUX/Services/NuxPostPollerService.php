<?php

namespace App\Workers\NUX\Services;

use App\Workers\NUX\Jobs\ReadPostJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Polls LinkedIn and X for new posts and dispatches the NUX pipeline
 * for each post not yet in nux_post_tracker.
 *
 * Called by NuxPollJob (scheduled) — once per active NUX deployment.
 */
class NuxPostPollerService
{
    public function __construct(
        private LinkedInService $linkedin,
        private XService        $x,
    ) {}

    /**
     * Poll all active NUX deployments and dispatch pipeline jobs for new posts.
     * Called by the scheduled NuxPollJob with a specific deployment_id.
     */
    public function pollDeployment(int $deploymentId): void
    {
        $dep = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('worker_slug', 'nux')
            ->where('status', 'active')
            ->first();

        if (!$dep) return;

        $userId = $dep->user_id;
        $config = json_decode($dep->config ?? '{}', true);

        $sourcePlatform = $config['source_platform'] ?? 'linkedin';
        $targetChannels = array_filter(
            array_map('trim', explode(',', $config['target_channels'] ?? ''))
        );

        if (empty($targetChannels)) {
            // Default: repurpose to the opposite platform
            $targetChannels = $sourcePlatform === 'linkedin' ? ['x'] : ['linkedin'];
        }

        $posts = [];

        if (in_array($sourcePlatform, ['linkedin', 'both'])) {
            $posts = array_merge($posts, $this->linkedin->fetchRecentPosts($userId));
        }

        if (in_array($sourcePlatform, ['x', 'both'])) {
            $posts = array_merge($posts, $this->x->fetchRecentTweets($userId));
        }

        if (empty($posts)) {
            Log::info('[NUX Poller] No posts fetched', ['deployment_id' => $deploymentId]);
            return;
        }

        $dispatched = 0;
        foreach ($posts as $post) {
            if ($this->alreadyProcessed($deploymentId, $post['platform'], $post['post_id'])) {
                continue;
            }

            $txId = $this->createTransaction($userId, $deploymentId, $post, $targetChannels);
            ReadPostJob::dispatch($txId)->onQueue('nux');
            $dispatched++;
        }

        Log::info('[NUX Poller] Dispatched', [
            'deployment_id' => $deploymentId,
            'posts_found'   => count($posts),
            'dispatched'    => $dispatched,
        ]);
    }

    private function alreadyProcessed(int $deploymentId, string $platform, ?string $postId): bool
    {
        if (!$postId) return false;

        return DB::table('nux_post_tracker')
            ->where('deployment_id', $deploymentId)
            ->where('platform', $platform)
            ->where('post_id', $postId)
            ->exists();
    }

    private function createTransaction(
        int    $userId,
        int    $deploymentId,
        array  $post,
        array  $targetChannels
    ): string {
        $txId = 'nux-' . $userId . '-' . now()->timestamp . '-' . substr(md5($post['post_id'] ?? uniqid()), 0, 8);

        DB::table('transactions')->insert([
            'tx_id'         => $txId,
            'user_id'       => $userId,
            'deployment_id' => $deploymentId,
            'worker_slug'   => 'nux',
            'status'        => 'received',
            'raw_input'     => json_encode(array_merge($post, [
                'source'          => 'poller',
                'target_channels' => array_values($targetChannels),
            ])),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return $txId;
    }
}
