<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Platform\Services\EmailDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs hourly. Fires abandonment emails when a user gets stuck mid-onboarding.
 *
 * Unlike OnboardingSequenceJob (day-offset based), this job uses delay_hours
 * and milestone timestamps on users (onboarding_gmail_at, onboarding_clients_at,
 * onboarding_fasttrack_at) to calculate how long a user has been stuck at each step.
 *
 * Trigger states (worker-configurable via admin/messaging):
 *   no_gmail       — user deployed a worker but never connected Gmail
 *   no_clients     — Gmail connected but no clients added to memory
 *   no_fast_track  — clients added but fast-track test never run
 *
 * Each email is sent at most once per user (dedup via tenant_email_log).
 * Only fires while onboarding_completed_at IS NULL (user still in onboarding).
 */
class OnboardingAbandonmentJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $now = now();

        // Load all active abandonment templates (delay_hours based, not day_offset)
        $templates = DB::table('platform_email_templates')
            ->where('active', true)
            ->whereNotNull('delay_hours')
            ->where('sequence', 'worker_onboarding')
            ->orderBy('sort_order')
            ->get();

        if ($templates->isEmpty()) return;

        // Users still in onboarding — not completed, not skipped, verified
        $users = DB::table('users')
            ->where('role', 'tenant')
            ->whereNotNull('email_verified_at')
            ->whereNull('blocked_at')
            ->whereNull('onboarding_completed_at')
            ->where('onboarding_skipped', false)
            ->get();

        foreach ($users as $user) {
            $workerSlug = $user->first_worker_slug ?? null;
            if (!$workerSlug) continue;

            $sentKeys = DB::table('tenant_email_log')
                ->where('user_id', $user->id)
                ->pluck('template_key')
                ->toArray();

            $hasGmail    = DB::table('user_gmail_credentials')->where('user_id', $user->id)->exists();
            $hasClients  = DB::table('clients')->where('user_id', $user->id)->exists();
            $hasFastTrack = !is_null($user->onboarding_fasttrack_at);

            // Only evaluate templates for this user's worker slug
            $workerTemplates = $templates->where('worker_slug', $workerSlug);

            foreach ($workerTemplates as $tpl) {
                if (in_array($tpl->key, $sentKeys)) continue;

                $eligible = match ($tpl->trigger_state) {
                    'no_gmail' => !$hasGmail
                        && !is_null($user->created_at)
                        && $now->diffInHours($user->created_at) >= $tpl->delay_hours,

                    'no_clients' => $hasGmail
                        && !$hasClients
                        && !is_null($user->onboarding_gmail_at)
                        && $now->diffInHours($user->onboarding_gmail_at) >= $tpl->delay_hours,

                    'no_fast_track' => $hasClients
                        && !$hasFastTrack
                        && !is_null($user->onboarding_clients_at)
                        && $now->diffInHours($user->onboarding_clients_at) >= $tpl->delay_hours,

                    default => false,
                };

                if (!$eligible) continue;

                $this->sendTemplate($tpl, $user);
                break; // one abandonment email per run per user
            }
        }
    }

    private function sendTemplate(object $tpl, object $user): void
    {
        EmailDispatcher::send($tpl->key, $user->email, $user->name, $user->id);
        Log::info("Abandonment email sent [{$tpl->key}]", ['user_id' => $user->id]);
    }
}
