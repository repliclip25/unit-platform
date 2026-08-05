<?php

namespace App\Http\Middleware;

use App\Platform\Services\PlatformVerificationService;
use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Presale accounts (e.g. brand-video) never enter the AVA wizard or the
        // platform verification gate — they belong on their own dashboard.
        if ($user->isPresale()) {
            return redirect()->route('presale.dashboard');
        }

        // Platform gate: all required verifications must be complete
        if (!PlatformVerificationService::isPlatformReady($user->id)) {
            return redirect()->route('hire.ava.welcome');
        }

        // Worker onboarding gate: user must have completed or explicitly skipped
        if (!$user->hasCompletedOnboarding()) {
            return redirect()->route('hire.ava.welcome');
        }

        return $next($request);
    }
}
