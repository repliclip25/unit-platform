<?php

namespace App\Console\Commands;

use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\MemoryHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NudgeMemoryEnrichment extends Command
{
    protected $signature   = 'ava:nudge-memory';
    protected $description = 'Send memory enrichment nudge emails to tenants below the health threshold';

    // Days after onboarding_completed_at to send each nudge
    private const NUDGE_DAYS = [1, 3, 7];

    public function handle(): void
    {
        $users = DB::table('users')
            ->where('role', 'tenant')
            ->whereNotNull('onboarding_completed_at')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            try {
                $health = MemoryHealthService::score($user->id);

                // Already healthy — nothing to nudge
                if ($health['healthy']) continue;

                $daysSince = (int) now()->diffInDays($user->onboarding_completed_at);

                $nudgeDay = $this->nudgeDayFor($daysSince);
                if (!$nudgeDay) continue;

                $templateKey = "memory_nudge_d{$nudgeDay}";

                // Don't send the same nudge twice
                $alreadySent = DB::table('tenant_email_log')
                    ->where('user_id', $user->id)
                    ->where('template_key', $templateKey)
                    ->exists();

                if ($alreadySent) continue;

                EmailDispatcher::send($templateKey, $user->email, $user->name, $user->id, [
                    '{score}'    => $health['score'],
                    '{complete}' => $health['complete'],
                    '{needed}'   => $health['needed'],
                    '{threshold}'=> MemoryHealthService::HEALTHY_THRESHOLD,
                ]);

                $sent++;

            } catch (\Throwable $e) {
                Log::error('[NudgeMemoryEnrichment] failed for user', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->info("Memory nudges sent: {$sent}");
    }

    // Returns the nudge day (1, 3, or 7) if today qualifies, null otherwise
    private function nudgeDayFor(int $daysSince): ?int
    {
        foreach (self::NUDGE_DAYS as $day) {
            if ($daysSince === $day) return $day;
        }
        return null;
    }
}
