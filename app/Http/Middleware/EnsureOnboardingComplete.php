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
