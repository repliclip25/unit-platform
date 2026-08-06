<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Platform\Services\PlatformVerificationService;
use App\Platform\Services\WorkerOnboardingService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Already verified — go to onboarding if not complete, otherwise dashboard.
            // app.dashboard itself redirects presale accounts to Memory Training.
            return $request->user()->hasCompletedOnboarding()
                ? redirect()->route('app.dashboard')
                : redirect()->route($this->landingRoute($request->user()));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Record email verification in platform_verifications table
        PlatformVerificationService::markVerified($request->user()->id, 'email');

        // Advance the active worker onboarding session past the verify-email step
        WorkerOnboardingService::advanceStepByName($request->user()->id, 'verify-email');

        // Presale accounts never enter the AVA wizard — same branch applied at
        // registration and OAuth. Everyone else lands on whichever route makes
        // sense given their current state.
        return $request->user()->isPresale()
            ? redirect()->route('presale.memory')
            : redirect()->route($this->landingRoute($request->user()));
    }

    /**
     * Someone already mid-way through the AVA wizard (an active onboarding
     * session exists) resumes it rather than getting bounced to the Memory
     * Hub — the hub is the default for undecided/fresh users, not a detour
     * for people who already chose a path.
     */
    private function landingRoute($user): string
    {
        return WorkerOnboardingService::activeSession($user->id)
            ? 'hire.ava.welcome'
            : 'hire.memory';
    }
}
