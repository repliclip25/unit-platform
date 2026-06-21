<?php
namespace App\Platform\Services\LLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIDriver implements LLMDriverInterface
{
    private array $usage = [];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl = 'https://api.openai.com/v1',
    ) {}

    public function chat(string $system, string $user, int $maxTokens): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120)->post(rtrim($this->baseUrl, '/') . '/chat/completions', [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'messages'   => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ]);

        if ($response->failed()) {
            Log::error('OpenAI-compatible API error', ['status' => $response->status(), 'body' => $response->body(), 'base' => $this->baseUrl]);
            throw new \RuntimeException('LLM API request failed: ' . $response->body());
        }

        $this->usage = [
            'input_tokens'  => $response->json('usage.prompt_tokens', 0),
            'output_tokens' => $response->json('usage.completion_tokens', 0),
        ];

        return trim($response->json('choices.0.message.content', ''));
    }

    public function lastUsage(): array { return $this->usage; }
}
