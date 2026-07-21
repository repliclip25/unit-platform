<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TenantWelcome;
use App\Models\User;
use App\Platform\Services\InfluencerService;
use App\Platform\Services\PlatformVerificationService;
use App\Platform\Services\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    private const SUPPORTED = ['google']; // apple pending developer account approval

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::SUPPORTED), 404);

        // Preserve worker intent and referral across OAuth round-trip
        if (request()->query('worker')) session(['oauth_worker_intent' => request()->query('worker')]);
        if (request()->query('ref'))    session(['oauth_ref' => request()->query('ref')]);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::SUPPORTED), 404);

        try {
            $social = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::warning("OAuth callback failed [{$provider}]", ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['email' => 'OAuth sign-in failed. Please try again or use email and password.']);
        }

        $email    = $social->getEmail();
        $name     = $social->getName() ?: $social->getNickname() ?: explode('@', $email)[0];
        $idColumn = $provider . '_id';
        $socialId = $social->getId();

        // 1. Find by provider ID (returning user who signed in with this provider before)
        $user = User::where($idColumn, $socialId)->first();
        if ($user && !$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
        if ($user) {
            PlatformVerificationService::markVerified($user->id, 'email', ['provider' => $provider], $provider);
        }

        // 2. Find by email (existing email/password user — link their account)
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    $idColumn           => $socialId,
                    'avatar'            => $social->getAvatar() ?? $user->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(), // OAuth confirms ownership
                ]);
            }
        }

        // 3. New user — register them
        $isNewUser = false;
        if (!$user) {
            if (!$email) {
                return redirect()->route('register')->withErrors(['email' => 'Your ' . ucfirst($provider) . ' account did not share an email address. Please register with email instead.']);
            }

            $isNewUser = true;
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make(Str::random(32)), // OAuth users — random unguessable placeholder
                'email_verified_at' => now(), // OAuth = email already verified
                'referral_code'     => ReferralService::generateCode(0, $email),
                $idColumn           => $socialId,
                'avatar'            => $social->getAvatar(),
            ]);

            // Assign proper referral code with real user ID
            $user->update(['referral_code' => ReferralService::generateCode($user->id, $user->email)]);

            // Do NOT fire Registered — it triggers SendEmailVerificationNotification.
            // OAuth users are already verified by Google; fire Verified instead so
            // any listeners that need a "user is ready" signal still run.
            event(new \Illuminate\Auth\Events\Verified($user));
            PlatformVerificationService::markVerified($user->id, 'email', ['provider' => $provider], $provider);

            // Handle referral attribution
            $infSlug = session('influencer_slug') ?? session('oauth_ref_influencer');
            if ($infSlug) {
                InfluencerService::handleSignup($user->id, $infSlug);
                session()->forget(['influencer_slug', 'oauth_ref_influencer']);
            } else {
                $refCode = session('oauth_ref') ?? session('referral_code');
                if ($refCode) {
                    ReferralService::handleSignup($user->id, $refCode);
                    session()->forget(['oauth_ref', 'referral_code']);
                }
            }

            // Register in leads funnel
            DB::table('fast_track_leads')->insertOrIgnore([
                'name'        => $user->name,
                'email'       => $user->email,
                'worker_slug' => 'platform',
                'source'      => 'oauth_' . $provider,
                'user_id'     => $user->id,
                'subscribed'  => true,
                'flags'       => json_encode(['type' => 'tenant', 'provider' => $provider]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

        }

        Auth::login($user, remember: true);

        // Capture worker intent (from session or query param saved before redirect)
        $workerIntent = session('oauth_worker_intent');
        session()->forget('oauth_worker_intent');
        if ($workerIntent && in_array($workerIntent, ['ava', 'nova', 'rex', 'lena'])) {
            session(['onboarding_intent_worker' => $workerIntent]);
        }

        if ($isNewUser) {
            \App\Platform\Services\EmailDispatcher::send('welcome_tenant', $user->email, $user->name, $user->id);
            // Same consolidation as RegisteredUserController::store() — new
            // signups land in the v2 onboarding flow, not the old dispatcher,
            // unless intended() already has a better URL saved (e.g. they
            // clicked "Continue with Google" from within /hire/ava/welcome).
            return redirect()->intended(route('hire.ava.welcome'));
        }

        return redirect()->intended(route('app.dashboard'));
    }
}
