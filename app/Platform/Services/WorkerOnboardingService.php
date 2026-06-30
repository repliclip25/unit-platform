<?php

namespace App\Platform\Services;

use App\Platform\Services\PlatformVerificationService;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Support\Facades\DB;

class WorkerOnboardingService
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_SKIPPED     = 'skipped';
    public const STATUS_ABANDONED   = 'abandoned';

    /**
     * Platform verification type → step name and label.
     */
    private static array $platformStepMeta = [
        'email'    => ['name' => 'verify-email',    'label' => 'Verify Email',    'type' => 'platform'],
        'phone'    => ['name' => 'verify-phone',    'label' => 'Verify Phone',    'type' => 'platform'],
        'kyc'      => ['name' => 'verify-kyc',      'label' => 'Identity Check',  'type' => 'platform'],
        'location' => ['name' => 'verify-location', 'label' => 'Location',        'type' => 'platform'],
        'business' => ['name' => 'verify-business', 'label' => 'Business Verify', 'type' => 'platform'],
    ];

    /**
     * Compute the full step sequence for a given user + worker.
     * Prepends unverified platform requirements before worker-specific steps.
     * Result is stored in steps_data.sequence at session start.
     *
     * e.g. for AVA where email is unverified:
     *   [
     *     ['name' => 'verify-email', 'type' => 'platform', 'label' => 'Verify Email'],
     *     ['name' => 'credential',   'type' => 'worker',   'label' => 'Connect Gmail', 'optional' => false],
     *     ['name' => 'memory',       'type' => 'worker',   'label' => 'Load Memory',   'optional' => true],
     *     ['name' => 'fast-track',   'type' => 'worker',   'label' => 'Live Test',     'optional' => false],
     *   ]
     */
    public static function resolveStepSequence(int $userId, string $workerSlug): array
    {
        $contract = WorkerRegistry::resolve($workerSlug);
        $verified = PlatformVerificationService::completedTypes($userId);

        $steps = [];

        // Inject platform steps for any unverified requirements this worker needs
        $platformReqs = $contract?->platformRequirements() ?? ['email'];
        foreach ($platformReqs as $type) {
            if (!in_array($type, $verified)) {
                $meta = self::$platformStepMeta[$type] ?? [
                    'name'  => 'verify-' . $type,
                    'label' => ucwords(str_replace('-', ' ', $type)),
                    'type'  => 'platform',
                ];
                $steps[] = $meta;
            }
        }

        // Append worker-specific steps
        $workerSteps = $contract?->onboardingSteps() ?? [];
        foreach ($workerSteps as $step) {
            $steps[] = array_merge(['type' => 'worker'], $step);
        }

        return $steps;
    }

    /**
     * Returns the active (in_progress) session for this user, or null.
     */
    public static function activeSession(int $userId): ?object
    {
        return DB::table('worker_onboarding_sessions')
            ->where('user_id', $userId)
            ->where('status', self::STATUS_IN_PROGRESS)
            ->first();
    }

    /**
     * Start a new worker onboarding session with a pre-resolved step sequence.
     * Abandons any existing in_progress session first.
     */
    public static function start(int $userId, string $workerSlug): object
    {
        DB::table('worker_onboarding_sessions')
            ->where('user_id', $userId)
            ->where('status', self::STATUS_IN_PROGRESS)
            ->update([
                'status'       => self::STATUS_ABANDONED,
                'abandoned_at' => now(),
                'updated_at'   => now(),
            ]);

        $sequence   = self::resolveStepSequence($userId, $workerSlug);
        $firstStep  = $sequence[0]['name'] ?? 'credential';

        $id = DB::table('worker_onboarding_sessions')->insertGetId([
            'user_id'      => $userId,
            'worker_slug'  => $workerSlug,
            'status'       => self::STATUS_IN_PROGRESS,
            'current_step' => $firstStep,
            'steps_data'   => json_encode(['sequence' => $sequence, 'steps' => []]),
            'started_at'   => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return DB::table('worker_onboarding_sessions')->find($id);
    }

    /**
     * Resume existing in_progress session for same worker, or start a fresh one.
     */
    public static function resume(int $userId, string $workerSlug): object
    {
        $existing = self::activeSession($userId);

        if ($existing && $existing->worker_slug === $workerSlug) {
            self::syncToSession($existing);
            return $existing;
        }

        $session = self::start($userId, $workerSlug);
        self::syncToSession($session);
        return $session;
    }

    /**
     * Advance past completedStep to the next step in the stored sequence.
     */
    public static function advanceStep(int $sessionId, string $completedStep, array $stepData = []): void
    {
        $session = DB::table('worker_onboarding_sessions')->find($sessionId);
        if (!$session) return;

        $data     = json_decode($session->steps_data ?? '{}', true);
        $sequence = $data['sequence'] ?? [];
        $steps    = $data['steps']    ?? [];

        $steps[$completedStep] = array_merge(['completed_at' => now()->toISOString()], $stepData);

        $names      = array_column($sequence, 'name');
        $currentIdx = array_search($completedStep, $names);
        $nextStep   = $names[$currentIdx + 1] ?? $completedStep;

        $data['steps'] = $steps;

        DB::table('worker_onboarding_sessions')->where('id', $sessionId)->update([
            'current_step' => $nextStep,
            'steps_data'   => json_encode($data),
            'updated_at'   => now(),
        ]);

        $updated = DB::table('worker_onboarding_sessions')->find($sessionId);
        self::syncToSession($updated);
    }

    /**
     * Advance by step name without knowing the session ID (for external actors
     * like VerifyEmailController).
     */
    public static function advanceStepByName(int $userId, string $completedStep, array $stepData = []): void
    {
        $session = self::activeSession($userId);
        if (!$session) return;

        // Only advance if the session is currently on this step
        if ($session->current_step === $completedStep) {
            self::advanceStep($session->id, $completedStep, $stepData);
        }
    }

    /**
     * Attach a deployment_id once the worker deployment is created.
     */
    public static function attachDeployment(int $sessionId, int $deploymentId): void
    {
        DB::table('worker_onboarding_sessions')->where('id', $sessionId)->update([
            'deployment_id' => $deploymentId,
            'updated_at'    => now(),
        ]);
    }

    /**
     * Mark session complete.
     */
    public static function complete(int $sessionId): void
    {
        $session = DB::table('worker_onboarding_sessions')->find($sessionId);
        $data    = json_decode($session?->steps_data ?? '{}', true);
        $seq     = $data['sequence'] ?? [];
        $last    = end($seq)['name'] ?? 'fast-track';

        DB::table('worker_onboarding_sessions')->where('id', $sessionId)->update([
            'status'       => self::STATUS_COMPLETED,
            'current_step' => $last,
            'completed_at' => now(),
            'updated_at'   => now(),
        ]);
        session()->forget(self::sessionKey());
    }

    /**
     * Mark session skipped.
     */
    public static function skip(int $sessionId): void
    {
        DB::table('worker_onboarding_sessions')->where('id', $sessionId)->update([
            'status'     => self::STATUS_SKIPPED,
            'skipped_at' => now(),
            'updated_at' => now(),
        ]);
        session()->forget(self::sessionKey());
    }

    /**
     * Sync DB session into PHP session cache.
     */
    public static function syncToSession(object $dbSession): void
    {
        $data = json_decode($dbSession->steps_data ?? '{}', true);

        session([
            self::sessionKey() => [
                'id'            => $dbSession->id,
                'worker_slug'   => $dbSession->worker_slug,
                'deployment_id' => $dbSession->deployment_id,
                'current_step'  => $dbSession->current_step,
                'status'        => $dbSession->status,
                'sequence'      => $data['sequence'] ?? [],
                'steps'         => $data['steps'] ?? [],
            ],
        ]);

        // Legacy keys kept for any remaining references
        session([
            'onboarding_worker_slug'   => $dbSession->worker_slug,
            'onboarding_deployment_id' => $dbSession->deployment_id,
        ]);
    }

    public static function fromSession(): ?array
    {
        return session(self::sessionKey());
    }

    /**
     * Load from session cache, or fall back to DB.
     */
    public static function load(int $userId): ?object
    {
        $cached = self::fromSession();
        // Invalidate cache if it's missing fields added after it was stored
        if ($cached && isset($cached['id'], $cached['status'], $cached['sequence'])) {
            return (object) $cached;
        }

        // Cache missing or stale — reload from DB
        $db = self::activeSession($userId);
        if ($db) {
            self::syncToSession($db);
        }
        return $db;
    }

    /**
     * Returns the sequence array for a loaded session object (whether from cache or DB).
     */
    public static function getSequence(object $session): array
    {
        if (isset($session->sequence)) {
            return $session->sequence; // from session cache
        }
        $data = json_decode($session->steps_data ?? '{}', true);
        return $data['sequence'] ?? [];
    }

    /**
     * Returns the 0-based index of stepName in the sequence, or -1 if not found.
     */
    public static function stepIndex(object $session, string $stepName): int
    {
        $seq   = self::getSequence($session);
        $names = array_column($seq, 'name');
        $idx   = array_search($stepName, $names);
        return $idx !== false ? (int) $idx : -1;
    }

    private static function sessionKey(): string
    {
        return 'worker_onboarding';
    }
}
