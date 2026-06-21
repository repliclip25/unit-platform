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
            'content-type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $user]],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Anthropic API request failed: ' . $response->body());
        }

        $this->usage = [
            'input_tokens'  => $response->json('usage.input_tokens', 0),
            'output_tokens' => $response->json('usage.output_tokens', 0),
        ];

        return trim($response->json('content.0.text', ''));
    }

    public function lastUsage(): array { return $this->usage; }
}
