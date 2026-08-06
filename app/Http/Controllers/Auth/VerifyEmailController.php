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
                : redirect()->route('hire.ava.welcome');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Record email verification in platform_verifications table
        PlatformVerificationService::markVerified($request->user()->id, 'email');

        // Advance the active worker onboarding session past the verify-email step
        WorkerOnboardingService::advanceStepByName($request->user()->id, 'verify-email');

        // Presale accounts never enter the AVA wizard — same branch applied at
        // registration and OAuth. Everyone else continues to onboarding.
        return $request->user()->isPresale()
            ? redirect()->route('presale.memory')
            : redirect()->route('hire.ava.welcome');
    }
}
