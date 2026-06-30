<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as Socialite;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Fail loudly on startup if critical env vars are missing — better than silent mid-pipeline failures
        if ($this->app->environment('production', 'staging') && !$this->app->runningInConsole()) {
            $required = [
                'APP_KEY', 'DB_PASSWORD',
                'GMAIL_CLIENT_ID', 'GMAIL_CLIENT_SECRET', 'GMAIL_REDIRECT_URI', 'GMAIL_PUBSUB_TOPIC',
                'ANTHROPIC_API_KEY',
                'STRIPE_KEY', 'STRIPE_SECRET', 'STRIPE_WEBHOOK_SECRET',
                'REDIS_HOST',
                'MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD',
            ];
            $missing = array_filter($required, fn($key) => empty(env($key)));
            if ($missing) {
                throw new \RuntimeException('Missing required environment variables: ' . implode(', ', $missing));
            }
        }


        $this->app['events']->listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', AppleProvider::class);
        });

        View::composer('layouts.app', function ($view) {
            $deployments = collect();
            if (auth()->check()) {
                $deployments = DB::table('worker_deployments')
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['active', 'paused'])
                    ->orderBy('created_at')
                    ->get();
            }
            $view->with('deployments', $deployments);
        });
    }
}
