<?php
namespace App\Platform\Services\LLM;

class ModelCatalog
{
    // provider_key => [label, driver_class, base_url, models => [id => [name, tier, cost_in, cost_out, recommended?]]]
    public const PROVIDERS = [
        'anthropic' => [
            'label'       => 'Anthropic',
            'driver'      => 'anthropic',
            'base_url'    => 'https://api.anthropic.com/v1',
            'dashboard'   => 'https://console.anthropic.com',
            'credits_url' => 'https://console.anthropic.com/settings/billing',
            'models'   => [
                'claude-haiku-4-5-20251001' => ['name' => 'Claude Haiku 4.5',  'tier' => 'Fast',      'cost_in' => 0.80,  'cost_out' => 4.00],
                'claude-sonnet-4-6'         => ['name' => 'Claude Sonnet 4.6', 'tier' => 'Balanced',  'cost_in' => 3.00,  'cost_out' => 15.00, 'recommended' => true],
                'claude-opus-4-7'           => ['name' => 'Claude Opus 4.7',   'tier' => 'Powerful',  'cost_in' => 15.00, 'cost_out' => 75.00],
            ],
        ],
        'openai' => [
            'label'       => 'OpenAI',
            'driver'      => 'openai',
            'base_url'    => 'https://api.openai.com/v1',
            'dashboard'   => 'https://platform.openai.com',
            'credits_url' => 'https://platform.openai.com/account/billing',
            'models'   => [
                'gpt-4o-mini' => ['name' => 'GPT-4o mini', 'tier' => 'Fast',      'cost_in' => 0.15,  'cost_out' => 0.60],
                'gpt-4o'      => ['name' => 'GPT-4o',      'tier' => 'Balanced',  'cost_in' => 2.50,  'cost_out' => 10.00, 'recommended' => true],
                'o3-mini'     => ['name' => 'o3-mini',     'tier' => 'Reasoning', 'cost_in' => 1.10,  'cost_out' => 4.40],
                'o1'          => ['name' => 'o1',           'tier' => 'Reasoning', 'cost_in' => 15.00, 'cost_out' => 60.00],
            ],
        ],
        'kimi' => [
            'label'       => 'KIMI (Moonshot)',
            'driver'      => 'openai',
            'base_url'    => 'https://api.moonshot.cn/v1',
            'dashboard'   => 'https://platform.moonshot.cn/console',
            'credits_url' => 'https://platform.moonshot.cn/console/recharge',
            'models'   => [
                'moonshot-v1-8k'   => ['name' => 'Moonshot 8K',   'tier' => 'Fast',     'cost_in' => 0.15, 'cost_out' => 0.15],
                'moonshot-v1-32k'  => ['name' => 'Moonshot 32K',  'tier' => 'Balanced', 'cost_in' => 0.60, 'cost_out' => 0.60, 'recommended' => true],
                'moonshot-v1-128k' => ['name' => 'Moonshot 128K', 'tier' => 'Powerful', 'cost_in' => 2.30, 'cost_out' => 2.30],
            ],
        ],
        'google' => [
            'label'       => 'Google Gemini',
            'driver'      => 'openai',
            'base_url'    => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'dashboard'   => 'https://aistudio.google.com',
            'credits_url' => 'https://console.cloud.google.com/billing',
            'models'   => [
                'gemini-2.0-flash'       => ['name' => 'Gemini 2.0 Flash',       'tier' => 'Fast',     'cost_in' => 0.10, 'cost_out' => 0.40, 'recommended' => true],
                'gemini-1.5-flash'       => ['name' => 'Gemini 1.5 Flash',       'tier' => 'Fast',     'cost_in' => 0.075,'cost_out' => 0.30],
                'gemini-1.5-pro'         => ['name' => 'Gemini 1.5 Pro',         'tier' => 'Powerful', 'cost_in' => 1.25, 'cost_out' => 5.00],
                'gemini-2.5-pro-preview' => ['name' => 'Gemini 2.5 Pro Preview', 'tier' => 'Powerful', 'cost_in' => 1.25, 'cost_out' => 10.00],
            ],
        ],
    ];

    public static function providerForModel(string $modelId): ?string
    {
        foreach (self::PROVIDERS as $providerKey => $provider) {
            if (isset($provider['models'][$modelId])) return $providerKey;
        }
        return null;
    }

    public static function pricing(string $modelId): array
    {
        foreach (self::PROVIDERS as $provider) {
            if (isset($provider['models'][$modelId])) {
                $m = $provider['models'][$modelId];
                return [$m['cost_in'] / 1_000_000, $m['cost_out'] / 1_000_000];
            }
        }
        return [3.00 / 1_000_000, 15.00 / 1_000_000]; // fallback to Sonnet pricing
    }

    public static function all(): array
    {
        return self::PROVIDERS;
    }

    public static function providerForModelFull(string $modelId): ?array
    {
        foreach (self::PROVIDERS as $key => $provider) {
            if (isset($provider['models'][$modelId])) {
                return array_merge($provider, ['key' => $key]);
            }
        }
        return null;
    }

    public static function allModelIds(): array
    {
        $ids = [];
        foreach (self::PROVIDERS as $provider) {
            $ids = array_merge($ids, array_keys($provider['models']));
        }
        return $ids;
    }

    public static function modelName(string $modelId): string
    {
        foreach (self::PROVIDERS as $provider) {
            if (isset($provider['models'][$modelId])) {
                return $provider['models'][$modelId]['name'];
            }
        }
        return $modelId;
    }
}
