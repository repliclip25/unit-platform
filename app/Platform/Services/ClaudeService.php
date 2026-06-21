<?php
namespace App\Platform\Services;

use App\Platform\Services\LLM\AnthropicDriver;
use App\Platform\Services\LLM\LLMDriverInterface;
use App\Platform\Services\LLM\ModelCatalog;
use App\Platform\Services\LLM\OpenAIDriver;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $model   = 'claude-sonnet-4-6';
    private int    $userId  = 0;
    private ?LLMDriverInterface $driver = null;

    public function configure(string $model, int $userId): void
    {
        $this->model  = $model;
        $this->userId = $userId;
        $this->driver = null; // reset — rebuilt lazily on next call
    }

    // Legacy shim so existing jobs using setModel() still work
    public function setModel(string $model): void
    {
        $this->model  = $model;
        $this->driver = null;
    }

    public function ask(string $systemPrompt, string $userMessage, int $maxTokens = 1024, ?string $txId = null, ?string $stage = null): array
    {
        $driver   = $this->resolveDriver();
        $text     = $driver->chat($systemPrompt, $userMessage, $maxTokens);
        $this->logUsage($driver->lastUsage(), $txId, $stage);
        return $this->parseJson($text);
    }

    public function askForText(string $systemPrompt, string $userMessage, int $maxTokens = 1024, ?string $txId = null, ?string $stage = null): string
    {
        $driver = $this->resolveDriver();
        $text   = $driver->chat($systemPrompt, $userMessage, $maxTokens);
        $this->logUsage($driver->lastUsage(), $txId, $stage);
        return trim($text);
    }

    private function resolveDriver(): LLMDriverInterface
    {
        if ($this->driver) return $this->driver;

        // Check tenant custom models first (model_id not in catalog)
        $providerKey = ModelCatalog::providerForModel($this->model);
        if (!$providerKey && $this->userId) {
            $custom = DB::table('tenant_custom_models')
                ->where('user_id', $this->userId)
                ->where('model_id', $this->model)
                ->where('active', true)
                ->first();
            if ($custom) {
                $apiKey = $custom->api_key_encrypted
                    ? Crypt::decryptString($custom->api_key_encrypted)
                    : '';
                $this->driver = new OpenAIDriver($apiKey, $custom->model_identifier, $custom->base_url);
                return $this->driver;
            }
        }

        $providerKey = $providerKey ?? 'anthropic';
        $provider    = ModelCatalog::PROVIDERS[$providerKey];
        $apiKey      = $this->resolveApiKey($providerKey);
        $baseUrl     = $provider['base_url'];

        $this->driver = match($provider['driver']) {
            'openai'    => new OpenAIDriver($apiKey, $this->model, $baseUrl),
            default     => new AnthropicDriver($apiKey, $this->model),
        };

        return $this->driver;
    }

    private function resolveApiKey(string $providerKey): string
    {
        // Prefer tenant's own key if they have one for this provider
        if ($this->userId) {
            $row = DB::table('tenant_api_keys')
                ->where('user_id', $this->userId)
                ->where('provider', $providerKey)
                ->where('active', true)
                ->first();
            if ($row) {
                try { return Crypt::decryptString($row->api_key_encrypted); }
                catch (\Throwable) {}
            }
        }

        // Fall back to platform key from config
        return match($providerKey) {
            'openai'    => config('services.openai.api_key', ''),
            'kimi'      => config('services.kimi.api_key', ''),
            'google'    => config('services.google.api_key', ''),
            default     => config('services.claude.api_key', ''),
        };
    }

    private function logUsage(array $usage, ?string $txId, ?string $stage): void
    {
        try {
            $tokensIn  = $usage['input_tokens']  ?? 0;
            $tokensOut = $usage['output_tokens'] ?? 0;

            if (!$tokensIn && !$tokensOut) return; // nothing to record

            [$priceIn, $priceOut] = ModelCatalog::pricing($this->model);
            $cost = ($tokensIn * $priceIn) + ($tokensOut * $priceOut);

            $userId = $this->userId ?: null;
            $deploymentId = null;
            if ($txId) {
                $tx = DB::table('transactions')->where('tx_id', $txId)->first();
                if ($tx) {
                    $userId       = $tx->user_id       ?? $userId;
                    $deploymentId = $tx->deployment_id ?? null;
                }
            }

            if (!$userId) {
                Log::warning('Usage logging skipped — no user_id', ['txId' => $txId, 'stage' => $stage]);
                return;
            }

            DB::table('usage_events')->insert([
                'user_id'       => $userId,
                'deployment_id' => $deploymentId,
                'worker_slug'   => 'ava',
                'tx_id'         => $txId,
                'stage'         => $stage,
                'tokens_input'  => $tokensIn,
                'tokens_output' => $tokensOut,
                'cost_usd'      => $cost,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Usage logging failed', ['error' => $e->getMessage(), 'txId' => $txId]);
        }
    }

    private function parseJson(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $decoded = json_decode(trim($text), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('LLM returned invalid JSON: ' . $text);
        }
        return $decoded;
    }
}
