<?php

namespace App\Http\Middleware;

use App\Platform\Services\PlatformVerificationService;
use App\Platform\Services\WorkerOnboardingService;
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
            return redirect()->route('presale.memory');
        }

        // Platform gate: all required verifications must be complete
        if (!PlatformVerificationService::isPlatformReady($user->id)) {
            return redirect()->route($this->landingRoute($user));
        }

        // Worker onboarding gate: user must have completed or explicitly skipped
        if (!$user->hasCompletedOnboarding()) {
            return redirect()->route($this->landingRoute($user));
        }

        return $next($request);
    }

    /**
     * Someone already mid-way through the AVA wizard (an active onboarding
     * session exists) resumes it rather than getting bounced to the Memory
     * Hub on every gated page they try to hit — the hub is the default
     * landing for undecided/fresh users, not a detour for people who
     * already chose a path.
     */
    private function landingRoute($user): string
    {
        return WorkerOnboardingService::activeSession($user->id)
            ? 'hire.ava.welcome'
            : 'hire.memory';
    }
}
