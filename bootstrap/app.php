<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies — Forge runs Laravel behind nginx with SSL termination.
        // Without this, Laravel generates http:// URLs even when behind HTTPS.
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'workers/ava/test',
            'workers/ava/gmail/webhook',
            'stripe/webhook',
        ]);

        $middleware->alias([
            'admin'            => \App\Http\Middleware\EnsureAdmin::class,
            'onboarded'        => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'not-pending-del'  => \App\Http\Middleware\EnsureNotPendingDeletion::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\CaptureReferralCode::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Flare exception reporting (https://flareapp.io)
        // Install:  composer require spatie/laravel-flare
        // Activate: add FLARE_KEY=your_key to .env
        if (app()->isProduction() && config('flare.key') && class_exists(\Spatie\LaravelFlare\Facades\Flare::class)) {
            \Spatie\LaravelFlare\Facades\Flare::register($exceptions);
        }
    })->create();
