<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Platform\Services\PlatformDefaults;

class AdminTenantController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tenantsQuery = DB::table('users')
            ->leftJoin('deployment_billing as db', 'users.id', '=', 'db.user_id')
            ->leftJoin('worker_deployments as wd', 'db.deployment_id', '=', 'wd.id')
            ->select(
                'users.id', 'users.name', 'users.email',
                'users.blocked_at', 'users.block_reason', 'users.monthly_spend_cap',
                DB::raw('COUNT(DISTINCT wd.id) as deployment_count'),
                DB::raw('SUM(CASE WHEN db.status = "trial" THEN 1 ELSE 0 END) as trial_count'),
                DB::raw('SUM(CASE WHEN db.status = "active" THEN 1 ELSE 0 END) as active_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.blocked_at', 'users.block_reason', 'users.monthly_spend_cap')
            ->orderBy('users.id');

        if ($search) {
            $tenantsQuery->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $tenants = $tenantsQuery->paginate(50)->withQueryString();

        // Attach this-month spend per user (keyed collection — one query for all users)
        $spends = DB::table('usage_events')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(cost_usd) as month_spend'), DB::raw('SUM(tokens_input+tokens_output) as month_tokens'))
            ->get()->keyBy('user_id');

        $tenants->through(function ($t) use ($spends) {
            $t->month_spend  = (float) ($spends[$t->id]->month_spend  ?? 0);
            $t->month_tokens = (int)   ($spends[$t->id]->month_tokens ?? 0);
            return $t;
        });

        // ── Leaderboards (all-time, platform-wide) ────────────────────────────

        // Top 5 spenders this month
        $topSpenders = DB::table('usage_events')
            ->join('users', 'users.id', '=', 'usage_events.user_id')
            ->whereYear('usage_events.created_at', now()->year)
            ->whereMonth('usage_events.created_at', now()->month)
            ->groupBy('usage_events.user_id', 'users.name', 'users.email')
            ->select('usage_events.user_id', 'users.name', 'users.email', DB::raw('SUM(cost_usd) as total_spend'))
            ->orderByDesc('total_spend')
            ->limit(5)->get();

        // Top 5 token consumers this month
        $topTokens = DB::table('usage_events')
            ->join('users', 'users.id', '=', 'usage_events.user_id')
            ->whereYear('usage_events.created_at', now()->year)
            ->whereMonth('usage_events.created_at', now()->month)
            ->groupBy('usage_events.user_id', 'users.name', 'users.email')
            ->select('usage_events.user_id', 'users.name', 'users.email', DB::raw('SUM(tokens_input+tokens_output) as total_tokens'))
            ->orderByDesc('total_tokens')
            ->limit(5)->get();

        // 5 newest registrants
        $newestTenants = DB::table('users')
            ->where('role', 'tenant')
            ->orderByDesc('created_at')
            ->select('id', 'name', 'email', 'created_at')
            ->limit(5)->get();

        // Top 5 by deployment count
        $topDeployments = DB::table('users')
            ->join('worker_deployments as wd', 'wd.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(wd.id) as dep_count'))
            ->orderByDesc('dep_count')
            ->limit(5)->get();

        // Top 5 by referrals (successful conversions)
        $topReferrers = DB::table('users')
            ->join('referral_credits as rc', 'rc.referrer_id', '=', 'users.id')
            ->where('rc.event', 'signup')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select('users.id', 'users.name', 'users.email', DB::raw('COUNT(rc.id) as referral_count'))
            ->orderByDesc('referral_count')
            ->limit(5)->get();

        // ── Rest of page data ─────────────────────────────────────────────────

        // Recent automated enforcement actions (last 50)
        $enforcementLog = DB::table('policy_enforcement_log')
            ->join('users', 'users.id', '=', 'policy_enforcement_log.user_id')
            ->select('policy_enforcement_log.*', 'users.name as tenant_name', 'users.email as tenant_email')
            ->orderByDesc('policy_enforcement_log.created_at')
            ->limit(50)->get();

        // Deployments with no billing record (orphaned)
        $orphanedDeployments = DB::table('worker_deployments as wd')
            ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
            ->leftJoin('users', 'users.id', '=', 'wd.user_id')
            ->whereNull('db.deployment_id')
            ->whereIn('wd.status', ['active', 'paused'])
            ->select('wd.id', 'wd.name', 'wd.worker_slug', 'wd.user_id', 'users.name as tenant_name', 'users.email as tenant_email')
            ->get();

        return view('admin.tenants', compact(
            'tenants', 'search', 'enforcementLog', 'orphanedDeployments',
            'topSpenders', 'topTokens', 'newestTenants', 'topDeployments', 'topReferrers'
        ));
    }

    public function show(int $id)
    {
        $tenant = DB::table('users')->where('id', $id)->firstOrFail();

        // Deployments with billing info
        $deployments = DB::table('worker_deployments as wd')
            ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
            ->leftJoin('worker_pricing as wp', 'wp.worker_slug', '=', 'wd.worker_slug')
            ->where('wd.user_id', $id)
            ->select(
                'wd.id', 'wd.name', 'wd.worker_slug', 'wd.status', 'wd.created_at as deployed_at',
                'db.status as billing_status', 'db.trial_transactions_used', 'db.trial_transactions_limit',
                'db.stripe_subscription_id', 'wp.monthly_flat_rate'
            )
            ->orderByDesc('wd.created_at')
            ->get();

        // Fast track history (from fast_track_leads tied to this user)
        $fastTracks = DB::table('fast_track_leads as ftl')
            ->leftJoin('worker_deployments as wd', 'wd.id', '=', DB::raw(
                '(SELECT id FROM worker_deployments WHERE worker_slug = ftl.worker_slug AND user_id = ftl.user_id LIMIT 1)'
            ))
            ->where('ftl.user_id', $id)
            ->select('ftl.*', 'wd.name as deployment_name')
            ->orderByDesc('ftl.created_at')
            ->limit(50)
            ->get();

        // Monthly spend (all time, last 6 months)
        $monthlySpend = DB::table('usage_events')
            ->where('user_id', $id)
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(cost_usd) as spend, SUM(tokens_input+tokens_output) as tokens")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Total all-time spend
        $totalSpend = DB::table('usage_events')->where('user_id', $id)->sum('cost_usd');

        // Referral stats
        $referralStats = \App\Platform\Services\ReferralService::getStats($id);
        $referralCode  = \App\Platform\Services\ReferralService::ensureCode($id);
        $referralList  = DB::table('referral_credits as rc')
            ->join('users as u', 'u.id', '=', 'rc.referee_id')
            ->where('rc.referrer_id', $id)
            ->select('rc.*', 'u.name as referee_name', 'u.email as referee_email')
            ->orderByDesc('rc.created_at')->get();
        $referredBy = null;
        $tenant2 = DB::table('users')->where('id', $id)->first();
        if ($tenant2->referred_by_code) {
            $referredBy = DB::table('users')->where('referral_code', $tenant2->referred_by_code)->first();
        }

        // Recent transactions
        $recentTx = DB::table('transactions')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Stripe subscriptions
        $subscriptions = DB::table('subscriptions')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->get();

        // Subscription items
        $subItems = $subscriptions->isNotEmpty()
            ? DB::table('subscription_items')
                ->whereIn('subscription_id', $subscriptions->pluck('id'))
                ->get()
            : collect();

        // Monthly spend — last 8 months
        $monthlySpend = DB::table('usage_events')
            ->where('user_id', $id)
            ->whereDate('created_at', '>=', now()->subMonths(8)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(cost_usd) as spend, SUM(tokens_input+tokens_output) as tokens, COUNT(*) as calls")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // API keys (providers configured)
        $apiKeys = DB::table('tenant_api_keys')->where('user_id', $id)->get();

        // Policy enforcement log for this tenant
        $policyLog = DB::table('policy_enforcement_log')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Sessions (active)
        $sessions = DB::table('sessions')
            ->where('user_id', $id)
            ->orderByDesc('last_activity')
            ->limit(5)
            ->get();

        // Usage map — page visit breakdown (last 30 days)
        $usageMap = DB::table('tenant_activity_log')
            ->where('user_id', $id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw("section, page, COUNT(*) as visits, MAX(created_at) as last_seen")
            ->groupBy('section', 'page')
            ->orderByDesc('visits')
            ->limit(30)
            ->get();

        // Total page views + device breakdown
        $totalViews    = DB::table('tenant_activity_log')->where('user_id', $id)->count();
        $viewsThisWeek = DB::table('tenant_activity_log')->where('user_id', $id)->whereDate('created_at', '>=', now()->subDays(7))->count();
        $lastActivity  = DB::table('tenant_activity_log')->where('user_id', $id)->max('created_at');
        $lastLogin     = DB::table('sessions')->where('user_id', $id)->max('last_activity');
        $daysSinceLogin = $lastLogin ? now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastLogin)) : null;

        // Transaction success rate
        $txStats = DB::table('transactions')->where('user_id', $id)
            ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->get()->keyBy('status');
        $txTotal       = $txStats->sum('cnt');
        $txCompleted   = ($txStats['completed']->cnt ?? 0) + ($txStats['draft_ready']->cnt ?? 0) + ($txStats['approved']->cnt ?? 0);
        $txFailed      = $txStats['failed']->cnt ?? 0;
        $txSuccessRate = $txTotal > 0 ? round(($txCompleted / $txTotal) * 100) : null;

        // Avg tokens/cost per call
        $usageAvg = DB::table('usage_events')->where('user_id', $id)
            ->selectRaw('AVG(tokens_input+tokens_output) as avg_tokens, AVG(cost_usd) as avg_cost')
            ->first();

        // Sent message history
        $messageLog = DB::table('admin_message_log')->where('tenant_id', $id)
            ->orderByDesc('sent_at')->limit(10)->get();

        // Churn risk signals
        $churnSignals = [];
        if ($daysSinceLogin !== null && $daysSinceLogin >= 7) {
            $churnSignals[] = "No login for {$daysSinceLogin} days";
        }
        if ($txFailed > 0 && $txTotal > 0 && ($txFailed / $txTotal) > 0.3) {
            $churnSignals[] = round(($txFailed/$txTotal)*100) . "% transaction failure rate";
        }
        $trialNearEnd = $deployments->filter(fn($d) =>
            ($d->billing_status ?? '') === 'trial' &&
            ($d->trial_transactions_used ?? 0) >= (($d->trial_transactions_limit ?? 10) * 0.8)
        );
        if ($trialNearEnd->isNotEmpty()) {
            $churnSignals[] = "Trial ≥80% used on " . $trialNearEnd->count() . " worker(s)";
        }
        if ($viewsThisWeek === 0 && $totalViews > 0) {
            $churnSignals[] = "Zero activity this week";
        }

        return view('admin.tenant-detail', compact(
            'tenant', 'deployments', 'fastTracks', 'monthlySpend',
            'totalSpend', 'recentTx', 'apiKeys', 'policyLog', 'sessions',
            'subscriptions', 'subItems', 'usageMap', 'totalViews', 'viewsThisWeek',
            'lastLogin', 'daysSinceLogin', 'txStats', 'txTotal', 'txCompleted',
            'txFailed', 'txSuccessRate', 'usageAvg', 'messageLog', 'churnSignals', 'lastActivity',
            'referralStats', 'referralCode', 'referralList', 'referredBy'
        ));
    }

    public function block(int $id, Request $request)
    {
        $validCodes = array_keys(\App\Platform\Services\PolicyEngine::POLICIES);
        $request->validate([
            'reason'      => 'required|string|max:500',
            'policy_code' => 'required|string|in:' . implode(',', $validCodes),
        ]);
        \App\Platform\Services\UsageGuard::blockUser($id, $request->reason, $request->policy_code);
        return back()->with('success', "Tenant #{$id} blocked under policy {$request->policy_code}.");
    }

    public function unblock(int $id)
    {
        \App\Platform\Services\UsageGuard::unblockUser($id);
        return back()->with('success', "Tenant #{$id} unblocked.");
    }

    public function setSpendCap(int $id, Request $request)
    {
        $request->validate(['cap' => 'nullable|numeric|min:0']);
        \App\Platform\Services\UsageGuard::setSpendCap($id, $request->cap ? (float) $request->cap : null);
        return back()->with('success', 'Spend cap updated.');
    }

    public function resetTrial(int $id, Request $request)
    {
        $depId = $request->input('deployment_id');
        DB::table('deployment_billing')
            ->where('user_id', $id)
            ->when($depId, fn($q) => $q->where('deployment_id', $depId))
            ->update(['trial_transactions_used' => 0, 'updated_at' => now()]);
        return back()->with('success', 'Trial counter reset.');
    }

    public function resetPassword(int $id, Request $request)
    {
        $request->validate(['password' => 'required|min:8']);
        DB::table('users')->where('id', $id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Password updated for tenant.');
    }

    public function sendMessage(int $id, Request $request)
    {
        $tenant = DB::table('users')->where('id', $id)->firstOrFail();
        $request->validate([
            'subject' => 'required|string|max:200',
            'body'    => 'required|string|max:5000',
        ]);
        Mail::send([], [], function ($m) use ($tenant, $request) {
            $cta  = $request->cta_label ? "\n\n→ {$request->cta_label}: {$request->cta_url}" : '';
            $html = nl2br(e($request->body . $cta));
            if ($request->cta_label && $request->cta_url) {
                $html .= '<br><br><a href="' . e($request->cta_url) . '" style="display:inline-block;background:#f3c531;color:#1a1404;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;">' . e($request->cta_label) . '</a>';
            }
            $m->to($tenant->email, $tenant->name)
              ->from(config('mail.from.address'), 'UNIT Platform')
              ->subject($request->subject)
              ->html($html);
        });
        DB::table('admin_message_log')->insert([
            'tenant_id' => $id,
            'sent_by'   => auth()->id(),
            'template'  => $request->template ?? 'custom',
            'subject'   => $request->subject,
            'body'      => $request->body,
            'sent_at'   => now(),
        ]);
        return back()->with('success', "Message sent to {$tenant->email}.");
    }

    public function sendAiMessage(int $id, Request $request)
    {
        $tenant      = DB::table('users')->where('id', $id)->firstOrFail();
        $goal        = $request->input('goal', 'feedback');
        $deployments = DB::table('worker_deployments as wd')
            ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
            ->where('wd.user_id', $id)->select('wd.name','wd.worker_slug','db.status as billing_status','db.trial_transactions_used','db.trial_transactions_limit')->get();
        $txStats = DB::table('transactions')->where('user_id', $id)
            ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->get()->keyBy('status');
        $totalSpend = DB::table('usage_events')->where('user_id', $id)->sum('cost_usd');
        $lastLogin  = DB::table('sessions')->where('user_id', $id)->max('last_activity');
        $daysSince  = $lastLogin ? now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastLogin)) : 'unknown';
        $viewsWeek  = DB::table('tenant_activity_log')->where('user_id', $id)->whereDate('created_at', '>=', now()->subDays(7))->count();

        $context = "Tenant: {$tenant->name} ({$tenant->email})\n"
            . "Member since: {$tenant->created_at}\n"
            . "Workers: " . $deployments->map(fn($d) => "{$d->name} [{$d->billing_status}, {$d->trial_transactions_used}/{$d->trial_transactions_limit} trial tx]")->implode(', ') . "\n"
            . "Total AI spend: $" . number_format($totalSpend, 4) . "\n"
            . "Transactions — completed: " . (($txStats['completed']->cnt ?? 0) + ($txStats['draft_ready']->cnt ?? 0)) . ", failed: " . ($txStats['failed']->cnt ?? 0) . "\n"
            . "Days since last login: {$daysSince}\n"
            . "Page views this week: {$viewsWeek}";

        $goalInstructions = match($goal) {
            'feedback'     => "Write a warm, conversational email asking for feedback on their experience. Ask 1-2 specific questions about what's working and what could be better. Keep it short and genuine.",
            'upsell'       => "Write a compelling upsell email encouraging them to upgrade from trial to a paid plan. Highlight the value they've already gotten and what they'll unlock. Include urgency if trial is nearly exhausted.",
            'reengagement' => "Write a re-engagement email for a tenant who hasn't logged in recently. Remind them of the value, offer help, and invite them back. Warm and non-pushy.",
            'check_in'     => "Write a friendly check-in email from a founder/team perspective. Personal, curious, and supportive.",
            default        => "Write a helpful, professional email.",
        };

        $prompt = "You are writing an email from UNIT Platform (an AI-powered worker automation SaaS) to one of our tenants.\n\n"
            . "Tenant context:\n{$context}\n\n"
            . "Goal: {$goalInstructions}\n\n"
            . "Return ONLY valid JSON with two fields: {\"subject\": \"...\", \"body\": \"...\"}. "
            . "The body should use \\n for line breaks. Sign off as 'The UNIT Team'. Do not add a CTA button — that will be added separately.";

        $response = Http::withToken(config('services.claude.api_key'))
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 600,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
                'system'     => 'You are a concise email copywriter. Return only valid JSON.',
            ])
            ->json();

        $text = $response['content'][0]['text'] ?? '{}';
        $text = preg_replace('/^```json\s*/m', '', $text);
        $text = preg_replace('/^```\s*/m', '', $text);
        $data = json_decode(trim($text), true) ?? ['subject' => '', 'body' => ''];

        return response()->json($data);
    }

    public function toggleBlock(int $id, Request $request)
    {
        $tenant = DB::table('users')->where('id', $id)->firstOrFail();
        if ($tenant->blocked_at) {
            \App\Platform\Services\UsageGuard::unblockUser($id);
            return back()->with('success', 'Tenant unblocked.');
        }
        $request->validate(['reason' => 'required|string|max:500']);
        \App\Platform\Services\UsageGuard::blockUser($id, $request->reason, 'manual_admin_block');
        return back()->with('success', 'Tenant blocked.');
    }

    public function backfillBilling(int $id)
    {
        $dep    = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
        $exists = DB::table('deployment_billing')->where('deployment_id', $id)->exists();
        if ($exists) {
            return back()->with('error', 'Billing record already exists for this deployment.');
        }
        DB::table('deployment_billing')->insert([
            'user_id'                  => $dep->user_id,
            'deployment_id'            => $id,
            'worker_slug'              => $dep->worker_slug,
            'status'                   => 'trial',
            'trial_transactions_used'  => 0,
            'trial_transactions_limit' => PlatformDefaults::freeTransactionsFor($dep->worker_slug),
            'billing_period_start'     => now()->startOfMonth(),
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
        return back()->with('success', "Billing record created for deployment #{$id} ({$dep->name}) — status: trial.");
    }

    public function setBillingStatus(int $id, Request $request)
    {
        $request->validate(['status' => 'required|in:trial,active,paused,canceled,past_due']);
        $dep     = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
        $updated = DB::table('deployment_billing')
            ->where('deployment_id', $id)
            ->update(['status' => $request->status, 'updated_at' => now()]);
        if (!$updated) {
            return back()->with('error', 'No billing record found. Backfill first.');
        }
        return back()->with('success', "Deployment #{$id} ({$dep->name}) billing status set to {$request->status}.");
    }

    public function voidInvoice(string $invoiceId, Request $request)
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = \App\Models\User::findOrFail($request->user_id);

        if (!$user->stripe_id) {
            return back()->with('error', 'This tenant has no Stripe customer record.');
        }

        try {
            $stripe  = new \Stripe\StripeClient(config('cashier.secret'));
            $invoice = $stripe->invoices->retrieve($invoiceId);

            // Verify the invoice belongs to this customer
            if ($invoice->customer !== $user->stripe_id) {
                return back()->with('error', 'Invoice does not belong to this tenant.');
            }

            if ($invoice->status === 'void') {
                return back()->with('error', 'Invoice is already voided.');
            }

            if ($invoice->status === 'paid') {
                return back()->with('error', 'Invoice is already paid — use Stripe dashboard to issue a refund instead.');
            }

            $stripe->invoices->voidInvoice($invoiceId);

            return back()->with('success', "Invoice {$invoiceId} voided. The tenant will no longer owe this amount.");
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->with('error', 'Stripe error: ' . $e->getMessage());
        }
    }

    public function fastTrackReset(int $id)
    {
        $dep    = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
        $config = json_decode($dep->config ?? '{}', true) ?: [];
        $config['fast_track_uses'] = 0;
        DB::table('worker_deployments')->where('id', $id)->update([
            'config'     => json_encode($config),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Fast Track counter reset.');
    }

    public function flush(int $id, Request $request)
    {
        $tenant = \App\Models\User::findOrFail($id);
        $scopes = $request->input('scopes', []);

        $log    = [];
        $errors = [];

        $run = function (string $label, callable $fn) use (&$log, &$errors) {
            try {
                $fn();
            } catch (\Throwable $e) {
                $errors[] = "{$label}: " . $e->getMessage();
            }
        };

        if (in_array('transactions', $scopes)) {
            $run('transactions', function () use ($id, &$log) {
                $n = DB::table('transactions')->where('user_id', $id)->count();
                DB::table('transactions')->where('user_id', $id)->delete();
                DB::table('processed_messages')->where('user_id', $id)->delete();
                DB::table('renewal_register')->where('user_id', $id)->delete();
                $log[] = "Deleted {$n} transactions + processed messages + renewal register";
            });
        }

        if (in_array('memory', $scopes)) {
            $run('memory', function () use ($id, &$log) {
                $clients  = DB::table('clients')->where('user_id', $id)->count();
                $contacts = DB::table('contacts')->where('user_id', $id)->count();
                $assets   = DB::table('assets')->where('user_id', $id)->count();
                DB::table('clients')->where('user_id', $id)->delete();
                DB::table('contacts')->where('user_id', $id)->delete();
                DB::table('assets')->where('user_id', $id)->delete();
                DB::table('ava_rules')->where('user_id', $id)->delete();
                DB::table('email_templates')->where('user_id', $id)->delete();
                $log[] = "Deleted {$clients} clients, {$contacts} contacts, {$assets} assets, all rules & templates";
            });
        }

        if (in_array('billing', $scopes)) {
            $run('billing', function () use ($id, &$log) {
                $deps = DB::table('worker_deployments')->where('user_id', $id)->pluck('id');
                foreach ($deps as $depId) {
                    DB::table('deployment_billing')->where('deployment_id', $depId)->update([
                        'status'                  => 'trial',
                        'trial_transactions_used' => 0,
                        'trial_transactions_limit'=> 6,
                        'stripe_subscription_id'  => null,
                        'updated_at'              => now(),
                    ]);
                }
                DB::table('usage_events')->where('user_id', $id)->delete();
                DB::table('users')->where('id', $id)->update(['monthly_spend_cap' => null, 'blocked_at' => null, 'block_reason' => null]);
                $depCount   = $deps->count();
                $trialLimit = $depCount * 6;
                $log[] = "Reset {$depCount} deployment billing to trial (0/{$trialLimit} used), cleared usage events, unblocked";
            });
        }

        if (in_array('desk', $scopes)) {
            $run('desk', function () use ($id, &$log) {
                DB::table('user_desk_cards')->where('user_id', $id)->delete();
                $log[] = "Cleared desk card preferences (will re-seed on next dashboard load)";
            });
        }

        if (in_array('onboarding', $scopes)) {
            $run('onboarding', function () use ($id, &$log) {
                DB::table('users')->where('id', $id)->update([
                    'onboarding_completed_at' => null,
                    'onboarding_skipped'      => false,
                ]);
                DB::table('platform_verifications')->where('user_id', $id)->delete();

                // Re-seed email verification if the user's email is already verified
                // (OAuth sign-ups have email_verified_at set — don't gate them again)
                $emailVerifiedAt = DB::table('users')->where('id', $id)->value('email_verified_at');
                if ($emailVerifiedAt) {
                    DB::table('platform_verifications')->insert([
                        'user_id'     => $id,
                        'type'        => 'email',
                        'verified_at' => $emailVerifiedAt,
                        'data'        => json_encode([]),
                        'verified_by' => 'system',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                $log[] = "Reset onboarding state and platform verifications";
            });

            // Auto-reset trial when onboarding is reset (unless billing scope already handled it)
            // A user re-entering onboarding should never be blocked by an exhausted trial
            if (!in_array('billing', $scopes) && $request->input('reset_trial_with_onboarding') !== '0') {
                $run('trial-auto-reset', function () use ($id, &$log) {
                    $deps = DB::table('worker_deployments')->where('user_id', $id)->pluck('id');
                    if ($deps->isNotEmpty()) {
                        DB::table('deployment_billing')
                            ->whereIn('deployment_id', $deps)
                            ->update([
                                'trial_transactions_used' => 0,
                                'updated_at'              => now(),
                            ]);
                        $log[] = "Auto-reset trial counters on {$deps->count()} deployment(s) (onboarding reset implies fresh trial)";
                    }
                });
            }
        }

        if (in_array('gmail', $scopes)) {
            $run('gmail', function () use ($id, &$log) {
                DB::table('deployment_credentials')->whereIn(
                    'deployment_id',
                    DB::table('worker_deployments')->where('user_id', $id)->pluck('id')
                )->delete();
                DB::table('user_gmail_credentials')->where('user_id', $id)->delete();
                $log[] = "Disconnected all Gmail accounts and credentials";
            });
        }

        if (in_array('deployments', $scopes)) {
            $run('deployments', function () use ($id, &$log) {
                DB::table('worker_deployments')->where('user_id', $id)->delete();
                $log[] = "Removed all worker deployments";
            });
        }

        if (in_array('referral', $scopes)) {
            $run('referral', function () use ($id, &$log) {
                DB::table('referral_credits')->where('referrer_id', $id)->delete();
                DB::table('users')->where('id', $id)->update(['referral_code' => null]);
                $log[] = "Cleared referral credits and referral code";
            });
        }

        if (in_array('queue', $scopes)) {
            $run('queue', function () use ($id, &$log) {
                // Delete failed jobs whose payload references this user_id
                $deleted = DB::table('failed_jobs')
                    ->get()
                    ->filter(fn($j) => str_contains($j->payload ?? '', '"user_id":' . $id))
                    ->each(fn($j) => DB::table('failed_jobs')->where('id', $j->id)->delete())
                    ->count();
                $log[] = "Cleared {$deleted} failed queue jobs for this tenant";
            });
        }

        // Log the admin action
        try {
            DB::table('platform_events')->insert([
                'user_id'     => $id,
                'type'        => 'admin_flush',
                'worker_slug' => 'platform',
                'event'       => 'admin_flush',
                'payload'     => json_encode(['scopes' => $scopes, 'log' => $log, 'errors' => $errors, 'admin_id' => auth()->id()]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable) {
            // audit log failure should never block the flush
        }

        // Send flush notice to the tenant if anything was actually cleared
        if (!empty($log)) {
            $scopeLabels = [
                'transactions' => 'Pipeline transactions & renewal register',
                'memory'       => 'Memory (clients, contacts, assets, rules, templates)',
                'billing'      => 'Billing reset to trial',
                'desk'         => 'Desk card preferences',
                'onboarding'   => 'Onboarding state',
                'gmail'        => 'Gmail connections',
                'deployments'  => 'Worker deployments',
                'referral'     => 'Referral credits & code',
            ];
            $flushedLabels = collect($scopes)
                ->filter(fn($s) => isset($scopeLabels[$s]))
                ->map(fn($s) => $scopeLabels[$s])
                ->values();

            Mail::send([], [], function ($m) use ($tenant, $flushedLabels) {
                $listHtml = $flushedLabels->map(fn($l) => "<li style='margin:4px 0;'>{$l}</li>")->implode('');
                $html = "
                    <div style='font-family:Inter,Arial,sans-serif;max-width:580px;margin:0 auto;color:#1a1a1a;'>
                        <div style='background:#f1d362;padding:28px 32px 20px;border-radius:12px 12px 0 0;'>
                            <span style='font-size:22px;font-weight:700;color:#1a1404;letter-spacing:-0.5px;'>UNIT</span>
                        </div>
                        <div style='background:#ffffff;padding:32px;border-radius:0 0 12px 12px;border:1px solid #e8e8e6;border-top:none;'>
                            <p style='margin:0 0 16px;font-size:16px;'>Hi {$tenant->name},</p>
                            <p style='margin:0 0 16px;font-size:15px;line-height:1.6;color:#444;'>
                                Our team performed a fresh-start reset on your UNIT account. This is typically done to clear test data or give you a clean slate during onboarding.
                            </p>
                            <p style='margin:0 0 8px;font-size:14px;font-weight:600;color:#111;'>The following was cleared:</p>
                            <ul style='margin:0 0 20px;padding-left:20px;font-size:14px;line-height:1.7;color:#555;'>
                                {$listHtml}
                            </ul>
                            <p style='margin:0 0 16px;font-size:14px;line-height:1.6;color:#444;'>
                                Everything else on your account remains intact — your login, settings, and anything not listed above are untouched.
                            </p>
                            <p style='margin:0 0 24px;font-size:14px;line-height:1.6;color:#444;'>
                                If you didn't expect this, or have any questions at all, just reply to this email and we'll sort it out right away.
                            </p>
                            <a href='https://unit.report/dashboard' style='display:inline-block;background:#f1d362;color:#1a1404;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;'>Go to your dashboard →</a>
                            <p style='margin:28px 0 0;font-size:13px;color:#888;'>— The UNIT Team</p>
                        </div>
                    </div>
                ";
                $m->to($tenant->email, $tenant->name)
                  ->from(config('mail.from.address'), 'UNIT')
                  ->subject('Your UNIT account has been reset')
                  ->html($html);
            });
        }

        if (!empty($errors)) {
            $errorMsg = implode("\n", $errors);
            return back()->with('error', "Flush completed with errors:\n{$errorMsg}\n\nCompleted:\n" . (empty($log) ? 'none' : implode("\n", $log)));
        }
        $summary = empty($log) ? 'No scopes selected.' : implode("\n", $log);
        return back()->with('success', "Account flushed:\n" . $summary);
    }
}
