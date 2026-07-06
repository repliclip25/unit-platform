<?php

namespace App\Platform\SDK;

/**
 * Immutable input package UNIT assembles and hands to a worker job.
 * Workers never query the database directly — everything they need is here.
 */
final class WorkerInput
{
    public function __construct(
        public readonly string  $txId,
        public readonly int     $deploymentId,
        public readonly int     $userId,
        public readonly string  $workerSlug,
        public readonly ?string $persona,          // active persona slug for this deployment
        public readonly string  $queue,            // e.g. 'ava-4' — use for dispatching next job
        public readonly string  $source,           // 'gmail_webhook' | 'fast_track_test' | 'public_demo'
        public readonly array   $raw,              // original raw_input JSON
        public readonly array   $stages,           // keyed stage outputs: ['read' => [...], 'classify' => [...], ...]
        public readonly array   $memory,           // ['contacts'=>[], 'assets'=>[], 'templates'=>[], 'rules'=>[], 'clients'=>[]]
        public readonly ?object $credential,       // Gmail OAuth credential — never put on queue payload
        public readonly ?string $tenantEmail,      // tenant's own account email (fallback recipient)
        public readonly array   $pipelineConfig,   // per-stage config: ['read' => ['max_tokens'=>1024, 'timeout'=>90], ...]
        public readonly string  $aiModel        = 'claude-sonnet-4-6',          // deployment-level model override (legacy fallback)
        public readonly string  $classifyModel  = 'claude-haiku-4-5-20251001', // legacy: classify + memory + template stages
        public readonly string  $draftModel     = 'claude-sonnet-4-6',         // legacy: draft stage
        public readonly array   $stageModels    = [],                           // per-stage map: ['read'=>'...', 'draft'=>'...']
    ) {}

    /**
     * Resolve the Claude model for a specific pipeline stage key.
     * Priority: stage_models[key] → legacy classify/draft split → aiModel fallback.
     */
    public function modelFor(string $stage): string
    {
        if (!empty($this->stageModels[$stage])) {
            return $this->stageModels[$stage];
        }
        // Legacy two-model fallback
        return $stage === 'draft' ? $this->draftModel : $this->classifyModel;
    }

    public function stage(string $name): array
    {
        return $this->stages[$name] ?? [];
    }

    public function isFastTrack(): bool
    {
        return in_array($this->source, ['fast_track_test', 'public_demo'], true);
    }

    /** Max tokens for a pipeline stage — falls back to a sensible default. */
    public function maxTokens(string $stage, int $default = 1024): int
    {
        return (int) ($this->pipelineConfig[$stage]['max_tokens'] ?? $default);
    }

    /** Timeout seconds for a pipeline stage. */
    public function timeout(string $stage, int $default = 90): int
    {
        return (int) ($this->pipelineConfig[$stage]['timeout'] ?? $default);
    }

    /** Retry attempts for a pipeline stage. */
    public function tries(string $stage, int $default = 3): int
    {
        return (int) ($this->pipelineConfig[$stage]['tries'] ?? $default);
    }
}
