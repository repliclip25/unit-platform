<?php

namespace App\Http\Controllers;

use App\Mail\AccountDeleted;
use App\Mail\DeletionScheduled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        // Hired employees
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $contracts = collect(\App\Platform\Services\WorkerRegistry::all())
            ->keyBy(fn($c) => $c->identity()['slug']);

        // Gmail credentials
        $gmailCredentials = DB::table('user_gmail_credentials')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Deployment credentials (which inboxes are wired to which deployments)
        $deploymentCredentials = DB::table('deployment_credentials')
            ->whereIn('deployment_id', $deployments->pluck('id'))
            ->get()
            ->groupBy('credential_id');

        // Active sessions
        $currentSessionId = session()->getId();
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($s) use ($currentSessionId) {
                $s->is_current = $s->id === $currentSessionId;
                $s->last_active_at = \Carbon\Carbon::createFromTimestamp($s->last_activity);
                $s->device = $this->parseUserAgent($s->user_agent ?? '');
                return $s;
            });

        // Deployment counts per slug (for team section)
        $depCounts = $deployments->groupBy('worker_slug')
            ->map(fn($g) => $g->count());

        // ── Value Clock cards ─────────────────────────────────────────────────
        // Worker clocks — one per deployment, driven by WorkerContract::valueClock()
        $clockCards = $deployments->map(function ($dep) use ($contracts) {
            $contract = $contracts->get($dep->worker_slug);
            if (!$contract || \App\Platform\Services\WorkerRegistry::isNull($contract)) return null;

            $clock = $contract->valueClock();
            if (empty($clock)) return null;

            $employee = $contract->employee();
            $owner    = strtoupper($employee['name'] ?? $dep->worker_slug);
            $resolved = \App\Platform\Services\ClockResolver::resolveWorker($dep->id, $clock);

            return [
                'owner'   => $owner,
                'label'   => $clock['label'],
                'value'   => $resolved['display'],
                'subtitle'=> $resolved['subtitle'],
                'formula' => $clock['formula'],
                'source'  => $clock['source'],
            ];
        })->filter()->values();

        // Platform clocks — referral, memory, etc.
        foreach (\App\Platform\Services\PlatformClockRegistry::all() as $key => $module) {
            $resolved  = ($module['resolver'])($user->id);
            $prefix    = $module['prefix'] ?? '';
            $display   = $prefix . $resolved['value'];
            $subtitle  = str_replace('{count}', number_format($resolved['count']), $module['subtitle']);

            $clockCards->push([
                'owner'   => $module['owner'],
                'label'   => $module['label'],
                'value'   => $display,
                'subtitle'=> $subtitle,
                'formula' => $module['formula'],
                'source'  => $module['source'],
            ]);
        }

        // Referral URL
        $referralUrl = $user->referral_code ? url('/register?ref=' . $user->referral_code) : null;

        return view('profile.show', compact(
            'user', 'deployments', 'contracts', 'gmailCredentials',
            'deploymentCredentials', 'sessions', 'depCounts',
            'clockCards', 'referralUrl'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        DB::table('users')->where('id', $request->user()->id)->update([
            'name'       => $validated['name'],
            'updated_at' => now(),
        ]);

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Users who signed up via OAuth may have no password
        if (!$user->password) {
            return redirect()->route('profile.show')->with('error', 'Password changes are not available for OAuth accounts.');
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated.');
    }

    public function revokeSession(Request $request, string $sessionId): RedirectResponse
    {
        // Never let a tenant revoke their own current session this way
        if ($sessionId === session()->getId()) {
            return redirect()->route('profile.show')->with('error', 'Use Sign Out to end your current session.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return redirect()->route('profile.show')->with('success', 'Session ended.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return redirect()->route('profile.show')->with('success', 'All other sessions ended.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_delete' => ['required', 'in:DELETE'],
        ]);

        $user         = $request->user();
        $deletionDate = now()->addDays(30)->format('F j, Y');

        // Mark pending deletion
        DB::table('users')->where('id', $user->id)->update([
            'deletion_requested_at' => now(),
            'updated_at'            => now(),
        ]);

        // Revoke all Gmail watches immediately — stop AVA processing
        $this->revokeAllGmailWatches($user->id);

        // Cancel Stripe subscription at period end
        $this->cancelStripeSubscription($user);

        // Log out all sessions
        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Send confirmation email to tenant
        \App\Platform\Services\EmailDispatcher::send('deletion_scheduled', $user->email, $user->name, $user->id, [
            '{deletion_date}' => $deletionDate,
        ]);

        // Alert admin
        \App\Platform\Services\UnitNotifier::adminAlert(
            "Account deletion scheduled: {$user->name}",
            "Tenant {$user->name} ({$user->email}) has scheduled their account for deletion on {$deletionDate}."
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('deletion_scheduled', true);
    }

    public function cancelDeletion(Request $request): RedirectResponse
    {
        DB::table('users')->where('id', $request->user()->id)->update([
            'deletion_requested_at' => null,
            'updated_at'            => now(),
        ]);

        return redirect()->route('profile.show')->with('success', 'Account deletion cancelled. Your account is fully restored.');
    }

    /**
     * Hard-delete a user and all associated data.
     * Called by PurgeScheduledDeletionsCommand after the 30-day grace period.
     */
    public static function hardDelete(int $userId): void
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) return;

        $depIds = DB::table('worker_deployments')->where('user_id', $userId)->pluck('id')->toArray();

        DB::transaction(function () use ($userId, $depIds, $user) {
            if ($depIds) {
                DB::table('deployment_credentials')->whereIn('deployment_id', $depIds)->delete();
                DB::table('deployment_billing')->whereIn('deployment_id', $depIds)->delete();
                DB::table('deployment_prompt_overrides')->whereIn('deployment_id', $depIds)->delete();
            }

            $userTables = [
                'transactions', 'transaction_stage_log', 'renewal_register',
                'usage_events', 'user_gmail_credentials', 'clients', 'contacts',
                'assets', 'memory_contributions', 'platform_verifications',
                'policy_enforcement_log', 'processed_messages', 'tenant_activity_log',
                'tenant_api_keys', 'tenant_custom_models', 'tenant_email_log',
                'subscriptions', 'worker_onboarding_sessions', 'ava_rules',
                'email_templates', 'notifications', 'sessions',
            ];

            foreach ($userTables as $table) {
                try {
                    DB::table($table)->where('user_id', $userId)->delete();
                } catch (\Throwable) {}
            }

            DB::table('worker_events')->where('source_user_id', $userId)->delete();
            DB::table('subscription_items')
                ->whereIn('subscription_id', DB::table('subscriptions')->where('user_id', $userId)->pluck('id'))
                ->delete();
            DB::table('worker_deployments')->where('user_id', $userId)->delete();
            DB::table('platform_events')->where('user_id', $userId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        });

        // Send final goodbye email
        \App\Platform\Services\EmailDispatcher::send('account_deleted', $user->email, $user->name, null);

        Log::info('[ProfileController] Hard-deleted user', ['id' => $userId, 'email' => $user->email]);
    }

    private function revokeAllGmailWatches(int $userId): void
    {
        $credentials = DB::table('user_gmail_credentials')
            ->where('user_id', $userId)
            ->where('watch_active', true)
            ->get();

        foreach ($credentials as $cred) {
            try {
                (new \App\Platform\Services\Gmail\GmailWatchService($cred))->stop();
            } catch (\Throwable $e) {
                Log::warning('[ProfileController] Gmail watch stop failed', ['cred' => $cred->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function cancelStripeSubscription(\App\Models\User $user): void
    {
        try {
            $sub = $user->subscription('platform');
            if ($sub && $sub->active()) {
                $sub->cancel(); // cancels at period end — tenant keeps access until billing cycle ends
            }
        } catch (\Throwable $e) {
            Log::warning('[ProfileController] Stripe cancel failed', ['user' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown browser';
        $os      = 'Unknown OS';

        if (str_contains($ua, 'Firefox'))       $browser = 'Firefox';
        elseif (str_contains($ua, 'Edg'))       $browser = 'Edge';
        elseif (str_contains($ua, 'Chrome'))    $browser = 'Chrome';
        elseif (str_contains($ua, 'Safari'))    $browser = 'Safari';
        elseif (str_contains($ua, 'curl'))      $browser = 'cURL';

        if (str_contains($ua, 'Windows'))       $os = 'Windows';
        elseif (str_contains($ua, 'Macintosh')) $os = 'macOS';
        elseif (str_contains($ua, 'iPhone'))    $os = 'iOS';
        elseif (str_contains($ua, 'Android'))   $os = 'Android';
        elseif (str_contains($ua, 'Linux'))     $os = 'Linux';

        return ['browser' => $browser, 'os' => $os];
    }
}
