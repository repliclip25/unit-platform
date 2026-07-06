<?php

namespace App\Console\Commands;

use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\MemoryHealthService;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NudgeMemoryEnrichment extends Command
{
    protected $signature   = 'ava:nudge-memory';
    protected $description = 'Send memory enrichment nudge emails to tenants below the health threshold';

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

                if (!$health['needs_memory']) continue;
                if ($health['healthy']) continue;

                $daysSince = (int) now()->diffInDays($user->onboarding_completed_at);
                $nudgeDay  = $this->nudgeDayFor($daysSince);
                if (!$nudgeDay) continue;

                $templateKey = "memory_nudge_d{$nudgeDay}";

                $alreadySent = DB::table('tenant_email_log')
                    ->where('user_id', $user->id)
                    ->where('template_key', $templateKey)
                    ->exists();

                if ($alreadySent) continue;

                // Resolve the user's active deployment and its persona
                $deployment = DB::table('worker_deployments')
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->orderByDesc('created_at')
                    ->first();

                $personaCopy = $this->resolvePersonaNudgeCopy(
                    $deployment,
                    $nudgeDay,
                    $user,
                    $health
                );

                if ($personaCopy) {
                    // Per-persona copy — send directly via Mail::raw()
                    Mail::raw($personaCopy['body'], function ($m) use ($user, $personaCopy) {
                        $m->to($user->email, $user->name)->subject($personaCopy['subject']);
                    });

                    // Log so we don't re-send
                    DB::table('tenant_email_log')->insert([
                        'user_id'      => $user->id,
                        'template_key' => $templateKey,
                        'sent_at'      => now(),
                    ]);
                } else {
                    // No persona set — fall back to generic DB template
                    EmailDispatcher::send($templateKey, $user->email, $user->name, $user->id, [
                        '{score}'     => $health['score'],
                        '{complete}'  => $health['complete'],
                        '{needed}'    => $health['needed'],
                        '{threshold}' => MemoryHealthService::HEALTHY_THRESHOLD,
                    ]);
                }

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

    private function nudgeDayFor(int $daysSince): ?int
    {
        foreach (self::NUDGE_DAYS as $day) {
            if ($daysSince === $day) return $day;
        }
        return null;
    }

    /**
     * Try to get per-persona nudge copy for this nudge day.
     * Returns ['subject' => ..., 'body' => ...] or null to fall back to generic.
     */
    private function resolvePersonaNudgeCopy(?object $deployment, int $nudgeDay, object $user, array $health): ?array
    {
        if (!$deployment) return null;

        $persona = $deployment->persona ?? null;
        if (!$persona) return null;

        try {
            $contract = WorkerRegistry::resolve($deployment->worker_slug ?? '');
            $personas = $contract->personas();

            if (empty($personas[$persona]['nudge_copy']["d{$nudgeDay}"])) return null;

            $copy = $personas[$persona]['nudge_copy']["d{$nudgeDay}"];

            $replacements = [
                '{name}'      => $user->name,
                '{score}'     => $health['score'],
                '{complete}'  => $health['complete'],
                '{needed}'    => $health['needed'],
                '{threshold}' => MemoryHealthService::HEALTHY_THRESHOLD,
                '{app_url}'   => config('app.url'),
            ];

            return [
                'subject' => str_replace(array_keys($replacements), array_values($replacements), $copy['subject']),
                'body'    => str_replace(array_keys($replacements), array_values($replacements), $copy['body']),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
