<?php

namespace App\Workers\NUX\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 60];
    }

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input = UnitPlatform::getInput($this->txId);
        UnitPlatform::setStatus($this->txId, 'generating');

        $repurpose = $input->stage('repurpose');
        $read      = $input->stage('read_post');

        $imageNeeded = (bool) ($repurpose['image_needed'] ?? true);
        $imagePrompt = $repurpose['image_prompt'] ?? null;
        $topic       = $repurpose['topic'] ?? $read['post_text'] ?? '';

        if (!$imageNeeded || !$imagePrompt) {
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'media',
                status: 'generating',
                data:   ['image_url' => null, 'image_path' => null, 'image_generated' => false],
            ));

            UnitPlatform::log('nux', $this->txId, 'image_skipped', ['reason' => 'image_needed is false or no prompt']);
            DraftPostJob::dispatch($this->txId)->onQueue($input->queue);
            return;
        }

        $apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');

        if (!$apiKey) {
            // No API key configured — skip image generation gracefully
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'media',
                status: 'generating',
                data:   ['image_url' => null, 'image_path' => null, 'image_generated' => false],
            ));

            UnitPlatform::log('nux', $this->txId, 'image_skipped', ['reason' => 'OPENAI_API_KEY not configured'], 'warning');
            DraftPostJob::dispatch($this->txId)->onQueue($input->queue);
            return;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(90)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model'   => 'dall-e-3',
                    'prompt'  => $imagePrompt,
                    'n'       => 1,
                    'size'    => '1024x1024',
                    'quality' => 'standard',
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('DALL-E API error: ' . $response->body());
            }

            $imageUrl  = $response->json('data.0.url');
            $imagePath = null;

            // Download and store locally so the URL stays accessible after OpenAI's TTL
            if ($imageUrl) {
                $imageContent = Http::timeout(30)->get($imageUrl)->body();
                $filename     = 'nux/' . $input->deploymentId . '/' . $this->txId . '.png';
                Storage::disk('public')->put($filename, $imageContent);
                $imagePath = $filename;
            }

            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'media',
                status: 'generating',
                data:   [
                    'image_url'       => $imageUrl,
                    'image_path'      => $imagePath,
                    'image_generated' => true,
                ],
            ));

            UnitPlatform::log('nux', $this->txId, 'image_generated', ['image_path' => $imagePath]);

        } catch (\Throwable $e) {
            // Image failure is non-fatal — pipeline continues without image
            Log::warning('[NUX MediaJob] Image generation failed', [
                'tx_id' => $this->txId,
                'error' => $e->getMessage(),
            ]);

            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'media',
                status: 'generating',
                data:   ['image_url' => null, 'image_path' => null, 'image_generated' => false],
            ));

            UnitPlatform::log('nux', $this->txId, 'image_failed', ['error' => $e->getMessage()], 'warning');
        }

        DraftPostJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('nux', $this->txId, 'job_failed', [
            'job' => 'MediaJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}
