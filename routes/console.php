<?php

use App\Jobs\OnboardingSequenceJob;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// Contract-driven worker scheduler — each registered worker declares its own scheduled jobs.
// Adding or removing a worker automatically adjusts what runs here with no code changes.
foreach (WorkerRegistry::all() as $contract) {
    $slug = $contract->identity()['slug'];

    foreach ($contract->scheduledJobs() as $scheduled) {
        $jobClass       = $scheduled['job'];
        $queue          = $scheduled['queue'];
        $perDeployment  = $scheduled['per_deployment'] ?? false;
        $scheduleName   = $slug . '.' . ($scheduled['name'] ?? class_basename($jobClass));

        Schedule::call(function () use ($slug, $jobClass, $queue, $perDeployment) {
            if ($perDeployment) {
                DB::table('worker_deployments')
                    ->where('worker_slug', $slug)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->pluck('id')
                    ->each(fn($id) => $jobClass::dispatch($id)->onQueue($queue));
            } else {
                $jobClass::dispatch()->onQueue($queue);
            }
        })->cron($scheduled['cron'])->name($scheduleName);
    }
}

// Renew Gmail inbox watch every 6 days (expires after 7) — loops all tenant credentials
// Runs every 6 days at 02:00 so a single missed run cannot cause watch expiry
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')
        ->orderBy('id')
        ->chunkById(50, function ($credentials) {
            foreach ($credentials as $credential) {
                try {
                    $watchService = new \App\Platform\Services\Gmail\GmailWatchService($credential);
                    $watchService->watch(config('services.gmail.pubsub_topic'));
                    \Illuminate\Support\Facades\Log::info('AVA Gmail watch renewed', ['credential_id' => $credential->id, 'email' => $credential->gmail_address]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('AVA Gmail watch renewal failed', ['credential_id' => $credential->id, 'error' => $e->getMessage()]);
                    \Illuminate\Support\Facades\DB::table('platform_events')->insert([
                        'event_type' => 'gmail_watch_renew_failed',
                        'payload'    => json_encode(['credential_id' => $credential->id, 'email' => $credential->gmail_address, 'error' => $e->getMessage()]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
})->cron('0 2 */6 * *')->name('ava.gmail.watch.renew');

// Manual trigger: php artisan ava:watch:renew
Artisan::command('ava:watch:renew', function () {
    $credentials = \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')
        ->get();

    foreach ($credentials as $credential) {
        try {
            $watchService = new \App\Platform\Services\Gmail\GmailWatchService($credential);
            $watchService->watch(config('services.gmail.pubsub_topic'));
            $this->info("Renewed watch for {$credential->gmail_address} (credential #{$credential->id})");
        } catch (\Throwable $e) {
            $this->error("Failed for {$credential->gmail_address}: " . $e->getMessage());
        }
    }
})->purpose('Renew Gmail inbox watch for all tenant credentials');

// Manual trigger for testing: php artisan ava:summary {deploymentId}
Artisan::command('ava:summary {deployment}', function () {
    $id = (int) $this->argument('deployment');
    \App\Workers\AVA\Jobs\DailySummaryJob::dispatch($id)->onQueue('ava');
    $this->info("Daily summary job dispatched for deployment #{$id}.");
})->purpose('Manually trigger AVA daily summary for a deployment');

// ── Automated policy enforcement ─────────────────────────────────────────────

// Runs every hour — evaluates all tenants and auto-blocks based on policy violations
Schedule::call(function () {
    \App\Platform\Services\PolicyEnforcer::run();
})->hourly()->name('platform.policy.enforce');

// Manual trigger: php artisan policy:enforce
Artisan::command('policy:enforce', function () {
    $results = \App\Platform\Services\PolicyEnforcer::run();
    $this->info("Policy enforcement complete.");
    $this->table(['Action', 'User ID', 'Policy', 'Reason'], $results);
})->purpose('Evaluate all tenants and auto-block policy violations');

// ── Onboarding email sequence ─────────────────────────────────────────────────

// Runs daily at 9AM — sends Day 3 and Day 7 contextual nudges based on activation state
Schedule::job(new OnboardingSequenceJob)->dailyAt('09:00')->name('platform.onboarding.sequence');

// Manual trigger: php artisan onboarding:sequence
Artisan::command('onboarding:sequence', function () {
    (new OnboardingSequenceJob)->handle();
    $this->info('Onboarding sequence processed.');
})->purpose('Run onboarding email sequence for all tenants');

// ── Monthly billing period reset ─────────────────────────────────────────────

// Runs on the 1st of each month at midnight — resets unit_count for all active subscriptions
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('deployment_billing')
        ->where('status', 'active')
        ->update([
            'unit_count'           => 0,
            'billing_period_start' => now()->startOfMonth(),
            'updated_at'           => now(),
        ]);
    \Illuminate\Support\Facades\Log::info('Billing period reset: unit_count zeroed for all active deployments.');
})->monthlyOn(1, '00:05')->name('billing.period.reset');

// ── Gmail token expiry check ──────────────────────────────────────────────────

// Runs daily — validates each credential's refresh token is still usable.
// Alerts tenant + admin if a token is revoked or expired.
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')
        ->orderBy('id')
        ->chunkById(50, function ($credentials) {
    foreach ($credentials as $credential) {
        try {
            $rawToken = $credential->refresh_token;
            try {
                $refreshToken = \Illuminate\Support\Facades\Crypt::decryptString($rawToken);
            } catch (\Throwable) {
                $refreshToken = $rawToken;
            }

            // Attempt to exchange refresh token for a new access token
            $response = \Illuminate\Support\Facades\Http::asForm()->post(
                'https://oauth2.googleapis.com/token',
                [
                    'client_id'     => config('services.gmail.client_id'),
                    'client_secret' => config('services.gmail.client_secret'),
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token',
                ]
            );

            if ($response->failed() || $response->json('error')) {
                // Token is invalid — alert tenant and admin
                $user = \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $credential->user_id)->first();

                if ($user) {
                    \App\Platform\Services\EmailDispatcher::send(
                        'gmail_token_expired',
                        $user->email,
                        $user->name,
                        $user->id,
                        ['{gmail_address}' => $credential->gmail_address]
                    );
                }

                \App\Platform\Services\UnitNotifier::adminAlert(
                    "Gmail Token Expired: {$credential->gmail_address}",
                    "Credential #{$credential->id} for user #{$credential->user_id} ({$credential->gmail_address}) has an invalid refresh token.\nError: " . ($response->json('error_description') ?? $response->body())
                );

                \Illuminate\Support\Facades\Log::warning('Gmail token expired', [
                    'credential_id' => $credential->id,
                    'email'         => $credential->gmail_address,
                    'error'         => $response->json('error'),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gmail token check failed', [
                'credential_id' => $credential->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }
        }); // end chunkById
})->dailyAt('08:00')->name('gmail.token.check');

Artisan::command('gmail:check-tokens', function () {
    $credentials = \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')->get();

    $this->info("Checking {$credentials->count()} credential(s)...");

    foreach ($credentials as $credential) {
        try {
            $rawToken = $credential->refresh_token;
            try { $refreshToken = \Illuminate\Support\Facades\Crypt::decryptString($rawToken); } catch (\Throwable) { $refreshToken = $rawToken; }
            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id'     => config('services.gmail.client_id'),
                'client_secret' => config('services.gmail.client_secret'),
                'refresh_token' => $refreshToken,
                'grant_type'    => 'refresh_token',
            ]);
            $status = ($response->successful() && !$response->json('error')) ? '✓ valid' : '✗ EXPIRED';
            $this->line("  #{$credential->id} {$credential->gmail_address}: {$status}");
        } catch (\Throwable $e) {
            $this->error("  #{$credential->id} {$credential->gmail_address}: ERROR — {$e->getMessage()}");
        }
    }
})->purpose('Check all Gmail credentials are still valid');

// ── Data archival ─────────────────────────────────────────────────────────────

// Runs weekly — prunes platform_events and usage_events older than 90 days
// Deletes in batches of 500 to avoid long table locks
Schedule::call(function () {
    $cutoff  = now()->subDays(90);
    $deleted = ['platform_events' => 0, 'usage_events' => 0];

    foreach (['platform_events', 'usage_events'] as $table) {
        do {
            $count = \Illuminate\Support\Facades\DB::table($table)
                ->where('created_at', '<', $cutoff)
                ->limit(500)
                ->delete();
            $deleted[$table] += $count;
        } while ($count > 0);
    }

    \Illuminate\Support\Facades\Log::info('Data archival complete', $deleted);
})->weeklyOn(0, '03:00')->name('platform.data.archival');

Artisan::command('platform:prune', function () {
    $cutoff  = now()->subDays(90);
    $deleted = ['platform_events' => 0, 'usage_events' => 0];
    foreach (['platform_events', 'usage_events'] as $table) {
        do {
            $count = \Illuminate\Support\Facades\DB::table($table)
                ->where('created_at', '<', $cutoff)->limit(500)->delete();
            $deleted[$table] += $count;
        } while ($count > 0);
    }
    $this->info("Pruned: platform_events={$deleted['platform_events']}, usage_events={$deleted['usage_events']}");
})->purpose('Prune platform_events and usage_events older than 90 days');


// Purge accounts whose 30-day deletion grace period has expired — runs once daily at 2am
Schedule::command('users:purge-deletions')->dailyAt('02:00');
