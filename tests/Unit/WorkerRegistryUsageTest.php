<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * WORKER.md is explicit: "Never use resolve() in ingest paths. Use
 * resolveActive() — it returns NullWorkerContract for dead workers so the
 * pipeline safely no-ops instead of crashing." That rule depends entirely on
 * every future contributor having read the doc — nothing in the code itself
 * enforces it. Found violated in two real, already-shipped job files
 * (ArchiveEvidenceJob, ClassifyEmailJob both called resolve() directly) while
 * auditing load-bearing code for exactly this kind of risk. Both fixed
 * alongside adding this test, so the same mistake can't silently ship again.
 *
 * Scoped to app/Workers/*\/Jobs — that's the ingest-path boundary the rule is
 * actually about. Controllers and admin tooling legitimately use resolve()
 * directly (a dead worker there should surface as a 404/error, not silently
 * no-op), so they're intentionally not covered by this check.
 */
class WorkerRegistryUsageTest extends TestCase
{
    public function test_no_job_class_calls_resolve_directly(): void
    {
        $files = glob(__DIR__ . '/../../app/Workers/*/Jobs/*.php');
        $this->assertNotEmpty($files, 'Expected to find worker job files under app/Workers/*/Jobs — glob pattern may be wrong.');

        $violations = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            // Match resolve( but not resolveActive( — a plain negative-lookahead
            // would need PCRE; simpler to check the character right after the
            // match isn't 'A' from "Active".
            if (preg_match_all('/WorkerRegistry::resolve\(/', $contents, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as [$match, $offset]) {
                    $following = substr($contents, $offset, strlen('WorkerRegistry::resolveActive('));
                    if ($following === 'WorkerRegistry::resolveActive(') continue;

                    $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                    $violations[] = basename($file) . ':' . $line;
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Found WorkerRegistry::resolve() called directly inside a job file, not resolveActive(): " . implode(', ', $violations) . ". " .
            "Per WORKER.md: 'Never use resolve() in ingest paths' — resolve() throws/returns a live contract even for a " .
            "decommissioned worker, so a job could run against dead worker code instead of safely no-op'ing via NullWorkerContract."
        );
    }
}
