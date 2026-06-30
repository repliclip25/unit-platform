<?php
namespace App\Platform\Services\LLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicDriver implements LLMDriverInterface
{
    private array $usage = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function chat(string $system, string $user, int $maxTokens): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'anthropic-beta'    => 'prompt-caching-2024-07-31',
            'content-type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            // System prompt passed as array block with cache_control so Anthropic caches it
            // across repeated calls — cuts input token cost 60–90% for the system prefix
            'system'  => [['type' => 'text', 'text' => $system, 'cache_control' => ['type' => 'ephemeral']]],
            'messages'   => [['role' => 'user', 'content' => $user]],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Anthropic API request failed: ' . $response->body());
        }

        $this->usage = [
            'input_tokens'              => $response->json('usage.input_tokens', 0),
            'output_tokens'             => $response->json('usage.output_tokens', 0),
            'cache_creation_input_tokens' => $response->json('usage.cache_creation_input_tokens', 0),
            'cache_read_input_tokens'   => $response->json('usage.cache_read_input_tokens', 0),
        ];

        return trim($response->json('content.0.text', ''));
    }

    public function lastUsage(): array { return $this->usage; }
}
