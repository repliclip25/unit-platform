<?php
namespace App\Platform\Services\LLM;

interface LLMDriverInterface
{
    public function chat(string $system, string $user, int $maxTokens): string;
    public function lastUsage(): array; // ['input_tokens' => int, 'output_tokens' => int]
}
