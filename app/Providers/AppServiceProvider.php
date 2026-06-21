<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
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
