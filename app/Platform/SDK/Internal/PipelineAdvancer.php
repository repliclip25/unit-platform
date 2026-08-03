<?php

namespace App\Platform\SDK\Internal;

/**
 * The gate-scanning decision logic extracted out of UnitPlatform::advance(),
 * with zero DB/queue dependency — every input is a plain array or callable.
 * Existed only inline inside advance() before, which meant the only way to
 * test a scenario (a hard gate disabled by a tenant, three consecutive
 * disabled stages in a row, a branch stage with no job_class) was to build a
 * real transaction and actually dispatch it. This class exists so those
 * scenarios can be asserted directly, in milliseconds, with a synthetic
 * stage array — see PipelineAdvancerTest.
 *
 * UnitPlatform::advance() still owns everything DB/queue-shaped (loading the
 * contract, resolving the job's FQCN, actually dispatching, setting
 * fulfillment_stage) — this class only answers "given these stages, this
 * position, and this gate-check function, what should happen next?"
 */
final class PipelineAdvancer
{
    public const DISPATCH = 'dispatch';
    public const HALT     = 'halt';
    public const COMPLETE = 'complete';

    /**
     * @param array<int, array{key: string, job_class: ?string, gate_type?: ?string}> $stages
     *   Ordered pipeline stage definitions, same shape as a worker contract's
     *   pipelineStages() entries.
     * @param int $currentIndex Index (0-based) of the stage advance() is
     *   resuming FROM — the scan starts at $currentIndex + 1.
     * @param callable $gateEnabled fn(string $stageKey): bool — whether the
     *   given stage is enabled for this deployment. Injected rather than
     *   read from the DB directly, which is what makes this pure.
     *
     * @return array{type: string, index?: int, stage?: array}
     *   type = self::DISPATCH  → dispatch the job at ['stage']/['index']
     *   type = self::HALT      → hard gate reached; caller should record
     *                            fulfillment_stage and stop (waiting on a
     *                            human action to resume from here)
     *   type = self::COMPLETE  → no stage after $currentIndex has anything
     *                            left to do; the pipeline is done
     */
    public function resolveNextDispatch(array $stages, int $currentIndex, callable $gateEnabled): array
    {
        for ($i = $currentIndex + 1; $i < count($stages); $i++) {
            $stage = $stages[$i];

            // A tenant can disable specific stages from AVA Settings (Request
            // Invoice, Request Documents, Confirm Payment, Generate Closeout
            // Report) — a disabled stage never dispatches and never halts,
            // even a hard gate; scanning just continues past it, same as any
            // other job-less marker.
            if (!$gateEnabled($stage['key'])) {
                continue;
            }

            if (empty($stage['job_class'])) {
                if (($stage['gate_type'] ?? null) === 'hard') {
                    return ['type' => self::HALT, 'index' => $i, 'stage' => $stage];
                }
                continue;
            }

            return ['type' => self::DISPATCH, 'index' => $i, 'stage' => $stage];
        }

        return ['type' => self::COMPLETE];
    }
}
