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
