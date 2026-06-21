<?php

use App\Workers\AVA\Jobs\DailySummaryJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// AVA daily summary — fires at 5PM for every active AVA deployment
Schedule::call(function () {
    $deployments = \Illuminate\Support\Facades\DB::table('worker_deployments')
        ->where('worker_slug', 'ava')
        ->where('status', 'active')
        ->pluck('id');

    foreach ($deployments as $deploymentId) {
        DailySummaryJob::dispatch($deploymentId)->onQueue('ava');
    }
})->dailyAt('17:00')->name('ava.daily.summary');

// Renew Gmail inbox watch every 6 days (expires after 7) — loops all tenant credentials
Schedule::call(function () {
    $credentials = \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')
        ->get();

    foreach ($credentials as $credential) {
        try {
            $watchService = new \App\Workers\AVA\Services\GmailWatchService($credential);
            $watchService->watch(config('services.gmail.pubsub_topic'));
            \Illuminate\Support\Facades\Log::info('AVA Gmail watch renewed', ['credential_id' => $credential->id, 'email' => $credential->gmail_address]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AVA Gmail watch renewal failed', ['credential_id' => $credential->id, 'error' => $e->getMessage()]);
        }
    }
})->weekly()->name('ava.gmail.watch.renew');

// Manual trigger: php artisan ava:watch:renew
Artisan::command('ava:watch:renew', function () {
    $credentials = \Illuminate\Support\Facades\DB::table('user_gmail_credentials')
        ->whereNotNull('refresh_token')
        ->get();

    foreach ($credentials as $credential) {
        try {
            $watchService = new \App\Workers\AVA\Services\GmailWatchService($credential);
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
    DailySummaryJob::dispatch($id)->onQueue('ava');
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
