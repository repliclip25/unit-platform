<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class PlatformVerificationService
{
    /**
     * Returns all active required verification types.
     */
    public static function requiredTypes(): array
    {
        return DB::table('verification_requirements')
            ->where('required', true)
            ->where('blocks_onboarding', true)
            ->orderBy('sort_order')
            ->pluck('type')
            ->all();
    }

    /**
     * Returns the types this user has completed.
     */
    public static function completedTypes(int $userId): array
    {
        return DB::table('platform_verifications')
            ->where('user_id', $userId)
            ->whereNotNull('verified_at')
            ->pluck('type')
            ->all();
    }

    /**
     * Returns the required types the user has NOT yet completed.
     */
    public static function pendingTypes(int $userId): array
    {
        $required  = self::requiredTypes();
        $completed = self::completedTypes($userId);
        return array_values(array_diff($required, $completed));
    }

    /**
     * True when all required, blocking verifications are done.
     */
    public static function isPlatformReady(int $userId): bool
    {
        return empty(self::pendingTypes($userId));
    }

    /**
     * Record a completed verification (idempotent).
     */
    public static function markVerified(int $userId, string $type, array $data = [], string $verifiedBy = 'self'): void
    {
        DB::table('platform_verifications')->upsert([
            'user_id'     => $userId,
            'type'        => $type,
            'verified_at' => now(),
            'data'        => json_encode($data),
            'verified_by' => $verifiedBy,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], ['user_id', 'type'], ['verified_at', 'data', 'verified_by', 'updated_at']);

        // Keep users.onboarding_completed_at in sync when all platform checks pass
        if (self::isPlatformReady($userId)) {
            DB::table('users')->where('id', $userId)
                ->whereNull('onboarding_completed_at')
                ->update(['onboarding_completed_at' => now(), 'updated_at' => now()]);
        }
    }

    /**
     * Full status map for a user — used in admin UI and verification screens.
     */
    public static function statusFor(int $userId): array
    {
        $requirements = DB::table('verification_requirements')->orderBy('sort_order')->get();
        $completed    = collect(self::completedTypes($userId));

        return $requirements->map(fn($r) => [
            'type'              => $r->type,
            'label'             => $r->label,
            'description'       => $r->description,
            'required'          => (bool) $r->required,
            'blocks_onboarding' => (bool) $r->blocks_onboarding,
            'verified'          => $completed->contains($r->type),
        ])->all();
    }
}
