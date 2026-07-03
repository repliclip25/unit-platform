<?php

namespace App\Platform\Services;

use App\Platform\Contracts\NullWorkerContract;
use App\Platform\Contracts\WorkerContract;
use Illuminate\Support\Facades\DB;

/**
 * WorkerRegistry — resolves worker slugs to WorkerContract implementations.
 *
 * Two sources, merged at runtime:
 *
 *   $map (platform-built)
 *     Hard-coded FQN strings for UNIT-owned workers. Always available even if
 *     the DB is down. Workers here do not need a worker_registry row to function,
 *     though a row is needed for lifecycle transitions (commission/decommission).
 *
 *   worker_registry table (externally installed)
 *     Rows with a worker_class column set are picked up automatically. An external
 *     developer packages their worker, an admin uploads it, the platform extracts
 *     the files to app/Workers/{Slug}/ and inserts a row. No $map change needed.
 *
 * Resolution order: $map wins if a slug appears in both (prevents external
 * packages from overriding platform-built workers).
 *
 * Lifecycle states (stored in worker_registry.lifecycle_status):
 *   active          — live, accepting new deployments and ingest
 *   testing         — only admin/test transactions; not billed, tagged is_test=true
 *   decommissioned  — no new deployments; ingest silently drops; data preserved
 *   removing        — RemoveWorkerJob running in background
 *   removed         — all tenant data soft-deleted; worker fully retired
 */
class WorkerRegistry
{
    /**
     * Platform-built workers — UNIT-owned, always available.
     * Add one line per worker when a new platform worker ships.
     * External / marketplace workers go in the DB, not here.
     */
    private static array $map = [
        'ava' => \App\Workers\AVA\AvaWorker::class,
        'nux' => \App\Workers\NUX\NuxWorker::class,
    ];

    // ── Resolution ───────────────────────────────────────────────────────────

    /**
     * Resolve a slug to its WorkerContract.
     * Always returns a contract — NullWorkerContract if unknown, missing, or removed.
     * Never throws. Callers check isNull() if they need to gate on availability.
     */
    public static function resolve(string $slug): WorkerContract
    {
        // 1. Platform-built $map takes priority
        if (isset(self::$map[$slug])) {
            return self::instantiate($slug, self::$map[$slug]);
        }

        // 2. DB-registered external worker
        try {
            $row = DB::table('worker_registry')
                ->where('slug', $slug)
                ->whereNotNull('worker_class')
                ->first();
        } catch (\Throwable) {
            $row = null;
        }

        if (!$row) return new NullWorkerContract($slug);

        return self::instantiate($slug, $row->worker_class);
    }

    /**
     * Resolve only if the worker is active or testing.
     * Returns NullWorkerContract for decommissioned, removing, removed, or unknown.
     * Use this in all ingest paths — never process for dead workers.
     */
    public static function resolveActive(string $slug): WorkerContract
    {
        if (in_array(self::status($slug), ['decommissioned', 'removing', 'removed'])) {
            return new NullWorkerContract($slug);
        }
        return self::resolve($slug);
    }

    /**
     * All resolvable workers — merges $map and DB-registered external workers.
     * Workers whose class file is missing are silently skipped.
     * Used by the marketplace listing, deploy wizard, and contract-driven scheduler.
     */
    public static function all(): array
    {
        $workers = [];

        // Platform-built workers from $map
        foreach (self::$map as $slug => $class) {
            $contract = self::instantiate($slug, $class);
            if (!self::isNull($contract)) {
                $workers[$slug] = $contract;
            }
        }

        // DB-registered external workers — skip any slug already in $map
        try {
            DB::table('worker_registry')
                ->whereNotNull('worker_class')
                ->whereNotIn('slug', array_keys(self::$map))
                ->whereNotIn('lifecycle_status', ['removed'])
                ->orderBy('id')
                ->get()
                ->each(function ($row) use (&$workers) {
                    $contract = self::instantiate($row->slug, $row->worker_class);
                    if (!self::isNull($contract)) {
                        $workers[$row->slug] = $contract;
                    }
                });
        } catch (\Throwable) {
            // DB unavailable or table not yet migrated — return platform-built workers only
        }

        return array_values($workers);
    }

    /**
     * All registered slugs — merges $map and DB.
     * Includes workers whose class file may be missing (slug still registered).
     */
    public static function slugs(): array
    {
        try {
            $dbSlugs = DB::table('worker_registry')
                ->whereNotNull('worker_class')
                ->whereNotIn('slug', array_keys(self::$map))
                ->whereNotIn('lifecycle_status', ['removed'])
                ->pluck('slug')
                ->toArray();
        } catch (\Throwable) {
            $dbSlugs = [];
        }

        return array_merge(array_keys(self::$map), $dbSlugs);
    }

    public static function isNull(WorkerContract $contract): bool
    {
        return $contract instanceof NullWorkerContract;
    }

    // ── Lifecycle status ─────────────────────────────────────────────────────

    /**
     * Returns the current lifecycle status from worker_registry table.
     * Falls back to 'active' if no row exists — platform-built workers in $map
     * are assumed active unless explicitly decommissioned via a DB row.
     */
    public static function status(string $slug): string
    {
        try {
            return DB::table('worker_registry')->where('slug', $slug)->value('lifecycle_status') ?? 'active';
        } catch (\Throwable) {
            return 'active';
        }
    }

    public static function isActive(string $slug): bool        { return in_array(self::status($slug), ['active', 'testing']); }
    public static function isDecommissioned(string $slug): bool { return self::status($slug) === 'decommissioned'; }
    public static function isTesting(string $slug): bool        { return self::status($slug) === 'testing'; }
    public static function isRemoved(string $slug): bool        { return self::status($slug) === 'removed'; }
    public static function isRemoving(string $slug): bool       { return self::status($slug) === 'removing'; }

    // ── Lifecycle transitions ─────────────────────────────────────────────────

    public static function commission(string $slug): void
    {
        DB::table('worker_registry')->where('slug', $slug)->update([
            'lifecycle_status'  => 'active',
            'commissioned_at'   => now(),
            'decommissioned_at' => null,
            'updated_at'        => now(),
        ]);
    }

    public static function setTesting(string $slug): void
    {
        DB::table('worker_registry')->where('slug', $slug)->update([
            'lifecycle_status' => 'testing',
            'updated_at'       => now(),
        ]);
    }

    public static function decommission(string $slug): void
    {
        DB::table('worker_registry')->where('slug', $slug)->update([
            'lifecycle_status'  => 'decommissioned',
            'decommissioned_at' => now(),
            'updated_at'        => now(),
        ]);

        DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->whereIn('status', ['active', 'paused'])
            ->update(['status' => 'decommissioned', 'updated_at' => now()]);

        DB::table('deployment_billing')
            ->whereIn('deployment_id', function ($q) use ($slug) {
                $q->select('id')->from('worker_deployments')->where('worker_slug', $slug);
            })
            ->whereNotIn('status', ['canceled', 'decommissioned'])
            ->update(['status' => 'decommissioned', 'updated_at' => now()]);
    }

    /**
     * Initiate removal — blocks all ingest immediately, then dispatches
     * RemoveWorkerJob to soft-delete all tenant data in the background.
     * Worker must be decommissioned first.
     */
    public static function remove(string $slug, int $adminId): void
    {
        abort_unless(
            in_array(self::status($slug), ['decommissioned', 'removed']),
            422,
            'Worker must be decommissioned before removal.'
        );

        DB::table('worker_registry')->where('slug', $slug)->update([
            'lifecycle_status' => 'removing',
            'updated_at'       => now(),
        ]);

        \App\Jobs\RemoveWorkerJob::dispatch($slug, $adminId)->onQueue('default');
    }

    /**
     * Register an externally-built worker into the DB registry.
     * Called by the admin worker installer after extracting the ZIP and
     * verifying the WorkerContract is satisfied.
     */
    public static function register(
        string $slug,
        string $workerClass,
        string $installedBy,
        ?string $licenseKey = null
    ): void {
        DB::table('worker_registry')->upsert(
            [
                'slug'             => $slug,
                'worker_class'     => $workerClass,
                'name'             => $slug,
                'lifecycle_status' => 'active',
                'installed_by'     => $installedBy,
                'installed_at'     => now(),
                'license_key'      => $licenseKey,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            ['slug'],
            ['worker_class', 'lifecycle_status', 'installed_by', 'installed_at', 'license_key', 'updated_at']
        );

        // Hydrate name/version/description from the contract itself
        $contract = self::resolve($slug);
        if (!self::isNull($contract)) {
            $identity = $contract->identity();
            DB::table('worker_registry')->where('slug', $slug)->update([
                'name'        => $identity['name'] ?? $slug,
                'version'     => $identity['version'] ?? '1.0',
                'description' => $identity['description'] ?? null,
                'updated_at'  => now(),
            ]);
        }
    }

    // ── Testing access gate ───────────────────────────────────────────────────

    public static function canAccessTesting(object $user): bool
    {
        return $user->role === 'admin' || !empty($user->testing_access);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private static function instantiate(string $slug, string $class): WorkerContract
    {
        try {
            return @new $class();
        } catch (\Throwable) {
            return new NullWorkerContract($slug);
        }
    }
}
