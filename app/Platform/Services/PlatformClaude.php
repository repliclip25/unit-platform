<?php

namespace App\Platform\Services;

use App\Platform\Services\ClaudeService;
use App\Platform\Services\LLM\ModelCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Wraps ClaudeService for platform-level (non-tenant) AI calls.
 * Logs token usage to platform_usage_events instead of usage_events.
 */
class PlatformClaude
{
    private ClaudeService $claude;
    private string $model = 'claude-sonnet-4-6';

    public function __construct()
    {
        $this->claude = app(ClaudeService::class);
        $this->claude->configure($this->model, 0);
    }

    public function ask(string $system, string $user, int $maxTokens, string $promptKey, string $triggeredBy = 'platform'): string
    {
        $text = $this->claude->askForText($system, $user, $maxTokens);
        $this->logPlatformUsage($promptKey, $triggeredBy);
        return $text;
    }

    /**
     * Pull token counts from the last ClaudeService call and log to platform_usage_events.
     * ClaudeService already attempted to log to usage_events (and skipped due to no userId).
     * We read the same driver usage data via a second pull — since the call already happened,
     * we log the cost separately here.
     */
    private function logPlatformUsage(string $promptKey, string $triggeredBy): void
    {
        try {
            // ClaudeService exposes lastUsage() via the resolved driver
            $driver = (new \ReflectionProperty($this->claude, 'driver'))->getValue($this->claude);
            if (!$driver) return;

            $usage     = $driver->lastUsage();
            $tokensIn  = $usage['input_tokens']  ?? 0;
            $tokensOut = $usage['output_tokens'] ?? 0;
            if (!$tokensIn && !$tokensOut) return;

            [$priceIn, $priceOut] = ModelCatalog::pricing($this->model);
            $cost = ($tokensIn * $priceIn) + ($tokensOut * $priceOut);

            DB::table('platform_usage_events')->insert([
                'prompt_key'   => $promptKey,
                'model'        => $this->model,
                'tokens_input' => $tokensIn,
                'tokens_output'=> $tokensOut,
                'cost_usd'     => $cost,
                'triggered_by' => $triggeredBy,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('PlatformClaude usage log failed', ['error' => $e->getMessage()]);
        }
    }
}
