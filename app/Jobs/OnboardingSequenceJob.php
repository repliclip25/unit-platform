<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OnboardingSequenceJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function handle(): void
    {
        $now = now();
        $appUrl = config('app.url');

        // Load all active templates once, grouped by sequence type
        $templates = DB::table('platform_email_templates')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();

        $workerOnboarding   = $templates->where('sequence', 'worker_onboarding');
        $platformOnboarding = $templates->where('sequence', 'platform_onboarding');
        $newsletter         = $templates->where('sequence', 'newsletter');

        $tenants = DB::table('users')
            ->where('role', 'tenant')
            ->whereNotNull('email_verified_at')
            ->whereNull('blocked_at')
            ->get();

        foreach ($tenants as $user) {
            $daysSince = (int) \Carbon\Carbon::parse($user->created_at)->diffInDays($now);

            // Already sent keys for this user (dedup guard)
            $sentKeys = DB::table('tenant_email_log')
                ->where('user_id', $user->id)
                ->pluck('template_key')
                ->toArray();

            $hasWorker  = DB::table('worker_deployments')->where('user_id', $user->id)->exists();
            $hasGmail   = DB::table('user_gmail_credentials')->where('user_id', $user->id)->exists();
            $hasTx      = DB::table('transactions')->where('user_id', $user->id)->exists();

            // ── Worker-specific onboarding ────────────────────────────────────
            // Only fires if tenant has a first_worker_slug and this template targets it
            if ($user->first_worker_slug) {
                $slug = $user->first_worker_slug;

                foreach ($workerOnboarding->where('worker_slug', $slug) as $tpl) {
                    if (in_array($tpl->key, $sentKeys)) continue;
                    if ($tpl->day_offset !== null && $daysSince !== $tpl->day_offset) continue;

                    // Evaluate the trigger_state condition
                    if (!$this->matchesTriggerState($tpl->trigger_state, $slug, $hasGmail, $hasTx, $hasWorker, $user)) continue;

                    $this->sendTemplate($tpl, $user, $appUrl);
                    break; // one email per run per tenant
                }
            }

            // ── Platform onboarding — never deployed any worker ───────────────
            if (!$hasWorker) {
                foreach ($platformOnboarding as $tpl) {
                    if (in_array($tpl->key, $sentKeys)) continue;
                    if ($tpl->day_offset !== null && $daysSince !== $tpl->day_offset) continue;

                    $this->sendTemplate($tpl, $user, $appUrl);
                    break;
                }
            }

            // ── Newsletter — goes to all active tenants ───────────────────────
            foreach ($newsletter as $tpl) {
                if (in_array($tpl->key, $sentKeys)) continue;
                if ($tpl->day_offset !== null && $daysSince !== $tpl->day_offset) continue;

                $this->sendTemplate($tpl, $user, $appUrl);
                break;
            }
        }
    }

    private function matchesTriggerState(
        ?string $state,
        string  $workerSlug,
        bool    $hasGmail,
        bool    $hasTx,
        bool    $hasWorker,
        object  $user
    ): bool {
        return match ($state) {
            'no_gmail'    => !$hasGmail,
            'no_tx'       => $hasGmail && !$hasTx,
            'no_worker'   => !$hasWorker,
            'no_activity' => $hasWorker && !$hasTx,
            'any', null   => true,
            default       => true,
        };
    }

    private function sendTemplate(object $tpl, object $user, string $appUrl): void
    {
        $body    = str_replace(['{name}', '{app_url}'], [$user->name, $appUrl], $tpl->body);
        $subject = str_replace(['{name}', '{app_url}'], [$user->name, $appUrl], $tpl->subject);

        try {
            Mail::raw($body, fn($m) => $m
                ->to($user->email, $user->name)
                ->subject($subject)
                ->replyTo(config('services.unit.noreply_email'), $tpl->from_name)
            );

            DB::table('tenant_email_log')->insert([
                'user_id'      => $user->id,
                'template_key' => $tpl->key,
                'sent_at'      => now(),
            ]);

            Log::info("Sequence email sent [{$tpl->key}]", ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            Log::error("Sequence email failed [{$tpl->key}]", ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
