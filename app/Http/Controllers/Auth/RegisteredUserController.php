<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AdminMessagingController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Platform\Services\InfluencerService;
use App\Platform\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        return view('auth.register', [
            'refCode'      => $request->query('ref'),
            'workerIntent' => $request->query('worker'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'referral_code' => ReferralService::generateCode(0, $request->email), // temp, updated below
        ]);

        // Assign proper referral code using real user ID
        $code = ReferralService::generateCode($user->id, $user->email);
        $user->update([
            'referral_code' => $code,
            'profile_code'  => \App\Http\Controllers\MemoryAccessController::generateProfileCode(),
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Handle influencer referral (takes priority over peer referral)
        $infSlug = session('influencer_slug');
        if ($infSlug) {
            InfluencerService::handleSignup($user->id, $infSlug);
            session()->forget('influencer_slug');
        } else {
            // Handle peer referral
            $refCode = $request->input('ref') ?: session('referral_code');
            if ($refCode) {
                ReferralService::handleSignup($user->id, $refCode);
                session()->forget('referral_code');
            }
        }

        // Register in leads funnel on signup — source: signup, subscribed by default
        DB::table('fast_track_leads')->insertOrIgnore([
            'name'        => $user->name,
            'email'       => $user->email,
            'worker_slug' => 'platform',
            'source'      => 'signup',
            'user_id'     => $user->id,
            'subscribed'  => true,
            'flags'       => json_encode(['type' => 'tenant']),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Send welcome email — referred tenants get a distinct welcome acknowledging the referral
        $isReferred = DB::table('referral_credits')->where('referee_id', $user->id)->where('event', 'signup')->exists();
        $welcomeKey = $isReferred ? 'referral_welcome_tenant' : 'welcome_tenant';
        \App\Platform\Services\EmailDispatcher::send($welcomeKey, $user->email, $user->name, $user->id, [
            '{bonus_tx}' => (string) \App\Platform\Services\ReferralService::REFEREE_BONUS_TX,
        ]);

        // Capture worker intent from form (passed from /register?worker=ava)
        $workerIntent = $request->input('worker');
        if ($workerIntent && in_array($workerIntent, ['ava', 'nova', 'rex', 'lena'])) {
            session(['onboarding_intent_worker' => $workerIntent]);
        }

        // If user arrived via a hire flow (e.g. /hire/ava/welcome), send them there
        return redirect()->intended(route('onboarding'));
    }
}
