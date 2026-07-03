<?php

namespace App\Platform\Contracts;

/**
 * NullWorkerContract — safe fallback returned by WorkerRegistry when a worker
 * is decommissioned, not yet registered, or its class file is missing.
 *
 * Every method returns a safe empty value so the platform never crashes.
 * Callers that need to check if they have a real worker should use:
 *   $contract instanceof NullWorkerContract
 * or the helper:
 *   WorkerRegistry::isNull($contract)
 */
class NullWorkerContract implements WorkerContract
{
    public function __construct(private string $slug = 'unknown') {}

    public function identity(): array
    {
        return [
            'name'        => $this->slug,
            'slug'        => $this->slug,
            'version'     => '0.0.0',
            'description' => 'Worker not available.',
            'tagline'     => '',
            'icon'        => null,
            'color'       => '#6b7280',
        ];
    }

    public function employee(): array         { return []; }
    public function org(): array              { return []; }
    public function demoPayload(): array      { return []; }
    public function platformRequirements(): array { return []; }
    public function onboardingSteps(): array  { return []; }
    public function instances(): array        { return ['multiple' => false, 'min' => 0, 'max' => 0]; }
    public function credential(): array       { return []; }
    public function deploymentFields(): array { return []; }
    public function trainSchema(): array      { return []; }
    public function tags(): array             { return []; }
    public function media(): array            { return []; }
    public function fastTrack(): array        { return []; }
    public function fastTrackOutcome(): array { return []; }
    public function pipelineStages(): array   { return []; }

    public function ingestJobClass(): string
    {
        // NullWorkerContract should never be dispatched to — the platform
        // checks WorkerRegistry::isNull() before dispatching ingest jobs.
        return '';
    }

    public function input(): array            { return []; }
    public function pipeline(): array         { return []; }
    public function emit(): array             { return []; }
    public function subscriptions(): array    { return []; }
    public function commit(): ?array          { return null; }
    public function output(): array           { return []; }
    public function memory(): array           { return ['shared' => [], 'owned' => []]; }
    public function qa(): array               { return []; }
    public function qaRequirements(): array   { return []; }
    public function prompts(): array          { return []; }
    public function notifications(): array    { return []; }
    public function overview(): array         { return ['panels' => []]; }
    public function valueClock(): array       { return []; }
    public function dashboard(): array        { return []; }
    public function owner(): array            { return []; }
    public function versionChangelog(): array { return []; }
    public function subscriptions_list(): array { return []; }
    public function scheduledJobs(): array    { return []; }
    public function fastTrackJobClass(): string { return ''; }
    public function stuckRecoveryMap(): array  { return []; }
}
