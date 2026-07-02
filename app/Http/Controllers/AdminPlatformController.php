<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Platform\Services\WorkerRegistry;
use App\Platform\Services\Gmail\GmailWatchService;

class AdminPlatformController extends Controller
{
    public function index()
    {
        return view('admin.platform', [
            'alerts'       => $this->alerts(),
            'aiHealth'     => $this->aiHealth(),
            'queueHealth'  => $this->queueHealth(),
            'stripeHealth' => $this->stripeHealth(),
            'smtpHealth'   => $this->smtpHealth(),
            'tenantStats'  => $this->tenantStats(),
            'workerStats'  => $this->workerStats(),
            'pipelineStats'=> $this->pipelineStats(),
            'gmailWatches' => $this->gmailWatches(),
            'connectors'   => $this->connectors(),
            'businessStats'=> $this->businessStats(),
            'recentAlerts' => $this->recentPolicyEvents(),
            'failedJobs'   => $this->failedJobs(),
            'msgTemplates' => $this->messagingTemplates(),
        ]);
    }

    // ── Alerts ──────────────────────────────────────────────────────────────

    private function alerts(): array
    {
        $alerts = [];

        // AI credit balance
        $ai = $this->aiHealth();
        if ($ai['credit_usd'] !== null && $ai['credit_usd'] < 10) {
            $alerts[] = ['level' => 'critical', 'icon' => '🤖', 'message' => 'Anthropic balance critical: $' . number_format($ai['credit_usd'], 2) . ' remaining', 'action' => 'https://console.anthropic.com/settings/billing', 'action_label' => 'Add credits'];
        }

        // Failed jobs
        $failedCount = DB::table('failed_jobs')->count();
        if ($failedCount > 0) {
            $alerts[] = ['level' => 'warning', 'icon' => '⚠️', 'message' => $failedCount . ' failed job(s) in queue', 'action' => route('admin.platform.section', 'queue'), 'action_label' => 'View jobs'];
        }

        // Expired Gmail watches
        $expiredWatches = DB::table('user_gmail_credentials')
            ->where('watch_active', 1)
            ->where('watch_expires_at', '<', now())
            ->count();
        if ($expiredWatches > 0) {
            $alerts[] = ['level' => 'critical', 'icon' => '📬', 'message' => $expiredWatches . ' Gmail watch(es) expired — AVA is blind on those inboxes', 'action' => route('admin.platform.section', 'connectors'), 'action_label' => 'View watches'];
        }

        // Watches expiring within 24h
        $expiringWatches = DB::table('user_gmail_credentials')
            ->where('watch_active', 1)
            ->where('watch_expires_at', '>', now())
            ->where('watch_expires_at', '<', now()->addHours(24))
            ->count();
        if ($expiringWatches > 0) {
            $alerts[] = ['level' => 'warning', 'icon' => '⏱️', 'message' => $expiringWatches . ' Gmail watch(es) expiring within 24 hours'];
        }

        // Blocked tenants
        $blocked = DB::table('users')->where('role', 'tenant')->whereNotNull('blocked_at')->count();
        if ($blocked > 0) {
            $alerts[] = ['level' => 'info', 'icon' => '🔒', 'message' => $blocked . ' tenant(s) currently blocked', 'action' => route('admin.tenants'), 'action_label' => 'Manage tenants'];
        }

        // Transactions stuck in processing for more than 30 min
        $stuck = DB::table('transactions')
            ->whereIn('status', ['received', 'reading', 'classifying', 'memory_lookup', 'templating', 'drafting', 'pushing'])
            ->where('updated_at', '<', now()->subMinutes(30))
            ->count();
        if ($stuck > 0) {
            $alerts[] = ['level' => 'warning', 'icon' => '🔄', 'message' => $stuck . ' transaction(s) stuck in processing for >30 min'];
        }

        // Stripe webhook - check last stripe event
        $lastStripe = DB::table('platform_events')
            ->where('event', 'like', 'stripe.%')
            ->orderByDesc('created_at')
            ->value('created_at');
        if ($lastStripe && now()->diffInHours($lastStripe) > 72) {
            $alerts[] = ['level' => 'warning', 'icon' => '💳', 'message' => 'No Stripe webhook received in 72+ hours'];
        }

        return $alerts;
    }

    // ── AI Health ────────────────────────────────────────────────────────────

    private function aiHealth(): array
    {
        $creditUsd   = null;
        $creditError = null;

        try {
            $resp = Cache::remember('anthropic_credit_balance', 300, function () {
                return Http::withHeaders([
                    'x-api-key'         => config('services.claude.api_key'),
                    'anthropic-version' => '2023-06-01',
                ])->get('https://api.anthropic.com/v1/organizations/me/usage');
            });

            // Anthropic doesn't have a direct credit balance API — fetch via billing
            $creditResp = Http::withHeaders([
                'x-api-key'         => config('services.claude.api_key'),
                'anthropic-version' => '2023-06-01',
            ])->get('https://api.anthropic.com/v1/organizations/me/credit_grants');

            if ($creditResp->successful()) {
                $grants = $creditResp->json('data') ?? [];
                $creditUsd = collect($grants)->sum(fn($g) => ($g['balance']['value'] ?? 0) / 100);
            }
        } catch (\Throwable $e) {
            $creditError = $e->getMessage();
        }

        // Cost today and this month from our usage_events
        $todayCost = DB::table('usage_events')
            ->whereDate('created_at', today())
            ->sum('cost_usd');

        $monthCost = DB::table('usage_events')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('cost_usd');

        $monthTokens = DB::table('usage_events')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('SUM(tokens_input) as input, SUM(tokens_output) as output')
            ->first();

        $totalCalls = DB::table('usage_events')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $activeModel = config('services.claude.model', 'claude-sonnet-4-6');

        // Tenant-provided keys
        $customKeys = DB::table('tenant_api_keys')->count();
        $customModels = DB::table('tenant_custom_models')->count();

        return [
            'credit_usd'    => $creditUsd,
            'credit_error'  => $creditError,
            'today_cost'    => (float) $todayCost,
            'month_cost'    => (float) $monthCost,
            'month_tokens'  => $monthTokens,
            'total_calls'   => $totalCalls,
            'active_model'  => $activeModel,
            'custom_keys'   => $customKeys,
            'custom_models' => $customModels,
        ];
    }

    // ── Queue Health ─────────────────────────────────────────────────────────

    private function queueHealth(): array
    {
        $failedJobs  = DB::table('failed_jobs')->count();
        $pendingJobs = DB::table('jobs')->count();

        // Recent failed jobs breakdown by last 24h
        $recentFailed = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours(24))
            ->count();

        // Jobs processed today (approximation via usage_events — each AI call = 1 job roughly)
        $processedToday = DB::table('usage_events')->whereDate('created_at', today())->count();

        // Queue depth per queue name from Redis
        $queueDepths = [];
        try {
            $redis = app('redis');
            $keys  = $redis->keys('*queues:*');
            foreach ($keys as $key) {
                $name = preg_replace('/.*queues:/', '', $key);
                if (str_contains($name, ':reserved') || str_contains($name, ':notify')) continue;
                $depth = $redis->llen($key);
                if ($depth > 0) $queueDepths[$name] = $depth;
            }
        } catch (\Throwable $e) {}

        $workerRunning = (bool) shell_exec('pgrep -f "queue:work" 2>/dev/null');
        $horizonRunning = (bool) shell_exec('pgrep -f "horizon" 2>/dev/null');

        return [
            'failed'          => $failedJobs,
            'pending'         => $pendingJobs,
            'recent_failed'   => $recentFailed,
            'processed_today' => $processedToday,
            'queue_depths'    => $queueDepths,
            'worker_running'  => $workerRunning,
            'horizon_running' => $horizonRunning,
        ];
    }

    // ── Stripe Health ────────────────────────────────────────────────────────

    private function stripeHealth(): array
    {
        $activeSubscriptions = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->count();

        $trialSubscriptions = DB::table('subscriptions')
            ->where('stripe_status', 'trialing')
            ->count();

        $pastDue = DB::table('subscriptions')
            ->where('stripe_status', 'past_due')
            ->count();

        // MRR estimate from deployment_billing
        $activeBilling = DB::table('deployment_billing')
            ->join('worker_pricing', 'worker_pricing.worker_slug', '=', 'deployment_billing.worker_slug')
            ->where('deployment_billing.status', 'active')
            ->sum('worker_pricing.monthly_flat_rate');

        // Last Stripe webhook event
        $lastWebhook = DB::table('platform_events')
            ->where('event', 'like', 'stripe.%')
            ->orderByDesc('created_at')
            ->first();

        // Stripe connected?
        $stripeConfigured = !empty(config('cashier.key')) && !empty(config('cashier.secret'));

        return [
            'active_subs'  => $activeSubscriptions,
            'trial_subs'   => $trialSubscriptions,
            'past_due'     => $pastDue,
            'mrr_estimate' => (float) $activeBilling,
            'last_webhook' => $lastWebhook,
            'configured'   => $stripeConfigured,
            'key_prefix'   => substr(config('cashier.key', ''), 0, 7),
        ];
    }

    // ── SMTP Health ──────────────────────────────────────────────────────────

    private function smtpHealth(): array
    {
        $lastSent = DB::table('admin_message_log')->orderByDesc('sent_at')->first();

        $config = DB::table('platform_configs')->where('key', 'smtp_routes')->first();
        $routes = $config ? json_decode($config->value, true) : [];

        return [
            'host'      => config('mail.mailers.smtp.host'),
            'port'      => config('mail.mailers.smtp.port'),
            'from'      => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'encryption'=> config('mail.mailers.smtp.encryption'),
            'last_sent' => $lastSent,
            'routes'    => $routes,
        ];
    }

    // ── Messaging Templates ──────────────────────────────────────────────────

    private function messagingTemplates(): array
    {
        $stored = DB::table('platform_configs')
            ->where('group', 'messaging')
            ->get()
            ->keyBy(fn($r) => str_replace('msg_template_', '', $r->key));

        $templates = [
            ['key' => 'verify-email',      'category' => 'Auth',       'label' => 'Email Verification',     'desc' => 'Sent on signup — contains verification link',          'view' => 'emails.verify-email',      'vars' => ['name', 'url']],
            ['key' => 'password-changed',  'category' => 'Auth',       'label' => 'Password Changed',       'desc' => 'Security confirmation after password update',          'view' => 'emails.password-changed',  'vars' => ['name']],
            ['key' => 'tenant-welcome',    'category' => 'Onboarding', 'label' => 'Welcome Email',          'desc' => 'First email after account creation',                   'view' => 'emails.tenant-welcome',    'vars' => ['name']],
            ['key' => 'gmail-connected',   'category' => 'Onboarding', 'label' => 'Gmail Connected',        'desc' => 'Sent when Gmail inbox is successfully linked',          'view' => 'emails.gmail-connected',   'vars' => ['name', 'gmailAddress']],
            ['key' => 'worker-deployed',   'category' => 'Onboarding', 'label' => 'Worker Deployed',        'desc' => 'Confirmation when a worker goes live',                  'view' => 'emails.worker-deployed',   'vars' => ['name', 'workerName', 'workerSlug', 'deploymentId', 'trialEndsAt', 'workerDesc']],
            ['key' => 'draft-ready',       'category' => 'Pipeline',   'label' => 'Draft Ready',            'desc' => 'Notifies tenant a draft is waiting for review',         'view' => 'emails.draft-ready',       'vars' => ['name', 'asset', 'client', 'contactName', 'subject', 'confidence', 'txId']],
            ['key' => 'daily-summary',     'category' => 'Pipeline',   'label' => 'Daily Summary',          'desc' => 'AVA daily digest — sent by DailySummaryJob',            'view' => 'emails.daily-summary',     'vars' => ['summarySubject', 'date', 'total', 'urgent', 'items']],
            ['key' => 'trial-ending',      'category' => 'Billing',    'label' => 'Trial Ending',           'desc' => 'Warning when trial transactions are ≥80% used',         'view' => null,                       'vars' => ['name', 'used', 'limit', 'workerName']],
            ['key' => 'trial-exhausted',   'category' => 'Billing',    'label' => 'Trial Exhausted',        'desc' => 'Upgrade prompt when trial hits limit',                  'view' => null,                       'vars' => ['name', 'workerName']],
            ['key' => 'referral-earned',   'category' => 'Growth',     'label' => 'Referral Credit Earned', 'desc' => 'Credit notification when referral converts',            'view' => null,                       'vars' => ['name', 'credit', 'refereeName']],
            ['key' => 'promo-code',        'category' => 'Growth',     'label' => 'Promo / Discount',       'desc' => 'Promotional email with a Stripe coupon code',           'view' => null,                       'vars' => ['name', 'promoCode', 'discount', 'expires']],
            ['key' => 'spend-cap-warning', 'category' => 'Alerts',     'label' => 'Spend Cap Warning',      'desc' => 'Alert when tenant reaches 80% of monthly spend cap',    'view' => null,                       'vars' => ['name', 'spent', 'cap']],
            ['key' => 'account-blocked',   'category' => 'Alerts',     'label' => 'Account Blocked',        'desc' => 'Notification when account is blocked by policy',        'view' => null,                       'vars' => ['name', 'reason', 'policyCode']],
            ['key' => 'reengagement',      'category' => 'Growth',     'label' => 'Re-engagement',          'desc' => 'Sent to tenants who have been inactive 7+ days',        'view' => null,                       'vars' => ['name', 'daysSince', 'missedCount']],
        ];

        return array_map(function ($tpl) use ($stored) {
            $override = $stored->get($tpl['key']);
            if ($override) {
                $data = json_decode($override->value, true) ?? [];
                $tpl['subject_override'] = $data['subject'] ?? null;
                $tpl['body_override']    = $data['body'] ?? null;
            } else {
                $tpl['subject_override'] = null;
                $tpl['body_override']    = null;
            }
            return $tpl;
        }, $templates);
    }

    public function saveMessageTemplate(Request $request, string $key)
    {
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'body'    => 'nullable|string',
        ]);

        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'msg_template_' . $key],
            [
                'group'       => 'messaging',
                'value'       => json_encode(['subject' => $request->subject, 'body' => $request->body]),
                'type'        => 'json',
                'label'       => $key,
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        return back()->with('ct_success', "Template [{$key}] saved.");
    }

    public function previewMessageTemplate(string $key)
    {
        $templates = collect($this->messagingTemplates());
        $tpl = $templates->firstWhere('key', $key);
        if (!$tpl || !$tpl['view']) {
            return response('No preview available for this template.', 404);
        }
        // Provide dummy vars for preview
        $vars = collect($tpl['vars'])->mapWithKeys(fn($v) => [$v => "[{$v}]"])->toArray();
        return view($tpl['view'], $vars);
    }

    // ── Tenant Stats ─────────────────────────────────────────────────────────

    private function tenantStats(): array
    {
        $total   = DB::table('users')->where('role', 'tenant')->count();
        $blocked = DB::table('users')->where('role', 'tenant')->whereNotNull('blocked_at')->count();
        $newThisMonth = DB::table('users')->where('role', 'tenant')
            ->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $newLastMonth = DB::table('users')->where('role', 'tenant')
            ->whereYear('created_at', now()->subMonth()->year)->whereMonth('created_at', now()->subMonth()->month)->count();

        $activeBilling = DB::table('deployment_billing')->where('status', 'active')
            ->distinct('user_id')->count('user_id');
        $trialBilling  = DB::table('deployment_billing')->where('status', 'trial')
            ->distinct('user_id')->count('user_id');

        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->whereNotNull('user_id')
            ->distinct('user_id')->count();

        return [
            'total'         => $total,
            'blocked'       => $blocked,
            'new_month'     => $newThisMonth,
            'new_last_month'=> $newLastMonth,
            'paying'        => $activeBilling,
            'trial'         => $trialBilling,
            'active_now'    => $activeSessions,
        ];
    }

    // ── Worker Stats ─────────────────────────────────────────────────────────

    private function workerStats(): array
    {
        $registry = collect(WorkerRegistry::all())->map(function ($worker, $slug) {
            $identity = $worker->identity();
            $owner    = $worker->owner();

            $deployments = DB::table('worker_deployments')
                ->where('worker_slug', $slug)
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')->get()->keyBy('status');

            $txToday = DB::table('transactions')
                ->where('worker_slug', $slug)
                ->whereDate('created_at', today())->count();

            $txFailed = DB::table('transactions')
                ->where('worker_slug', $slug)
                ->where('status', 'failed')
                ->whereDate('created_at', today())->count();

            $totalTx = DB::table('transactions')->where('worker_slug', $slug)->count();

            $costMonth = DB::table('usage_events')
                ->where('worker_slug', $slug)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('cost_usd');

            return [
                'slug'        => $slug,
                'name'        => $identity['name'],
                'version'     => $identity['version'],
                'owner'       => $owner['name'],
                'verified'    => $owner['verified'],
                'deployments' => $deployments,
                'tx_today'    => $txToday,
                'tx_failed'   => $txFailed,
                'tx_total'    => $totalTx,
                'cost_month'  => (float) $costMonth,
                'error_rate'  => $txToday > 0 ? round(($txFailed / $txToday) * 100) : 0,
            ];
        });

        return ['workers' => $registry];
    }

    // ── Pipeline Stats ───────────────────────────────────────────────────────

    private function pipelineStats(): array
    {
        $statuses = DB::table('transactions')
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')->get()->keyBy('status');

        $total      = $statuses->sum('cnt');
        $successful = collect(['draft_ready','approved','sent'])->sum(fn($s) => $statuses[$s]->cnt ?? 0);
        $failed     = $statuses['failed']->cnt ?? 0;
        $processing = collect(['received','ingesting','reading','classifying','memory_lookup','templating','drafting','pushing'])
            ->sum(fn($s) => $statuses[$s]->cnt ?? 0);

        // Stage failure breakdown from platform_events
        $stageFailures = DB::table('platform_events')
            ->where('event', 'job_failed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.job')) as job, COUNT(*) as cnt")
            ->groupBy('job')
            ->orderByDesc('cnt')
            ->limit(5)
            ->get();

        // Avg transaction age (processing time) for completed ones — last 7 days
        $avgMinutes = DB::table('transactions')
            ->whereIn('status', ['draft_ready','approved','sent'])
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds')
            ->value('avg_seconds');

        // Daily volume last 7 days
        $dailyVolume = DB::table('transactions')
            ->whereDate('created_at', '>=', now()->subDays(6))
            ->selectRaw("DATE(created_at) as day, COUNT(*) as cnt")
            ->groupBy('day')->orderBy('day')->get();

        return [
            'total'         => $total,
            'successful'    => $successful,
            'failed'        => $failed,
            'processing'    => $processing,
            'success_rate'  => $total > 0 ? round(($successful / $total) * 100) : 0,
            'stage_failures'=> $stageFailures,
            'avg_minutes'   => $avgMinutes ? round($avgMinutes / 60, 1) : null,
            'daily_volume'  => $dailyVolume,
            'statuses'      => $statuses,
        ];
    }

    // ── Gmail Watches ────────────────────────────────────────────────────────

    private function gmailWatches(): array
    {
        $watches = DB::table('user_gmail_credentials as gc')
            ->join('users', 'users.id', '=', 'gc.user_id')
            ->leftJoin('deployment_credentials as dc', 'dc.credential_id', '=', 'gc.id')
            ->leftJoin('worker_deployments as wd', 'wd.id', '=', 'dc.deployment_id')
            ->select(
                'gc.id', 'gc.gmail_address', 'gc.watch_active', 'gc.watch_expires_at', 'gc.watch_expires_at',
                'users.name as tenant_name', 'users.id as user_id',
                'wd.name as deployment_name', 'wd.status as deployment_status'
            )
            ->orderBy('gc.watch_expires_at')
            ->get()
            ->map(function ($w) {
                $expiresAt = $w->watch_expires_at ?? $w->watch_expires_at;
                $w->expires_at     = $expiresAt;
                $w->is_expired     = $expiresAt && now()->isAfter($expiresAt);
                $w->expires_soon   = $expiresAt && !$w->is_expired && now()->diffInHours($expiresAt) < 24;
                $w->hours_left     = $expiresAt ? max(0, now()->diffInHours($expiresAt, false)) : null;

                // Last webhook received for this address
                $w->last_webhook = DB::table('platform_events')
                    ->where('worker_slug', 'ava')
                    ->whereRaw("JSON_EXTRACT(payload, '$.gmail') = ?", [$w->gmail_address])
                    ->orderByDesc('created_at')
                    ->value('created_at');

                return $w;
            });

        $pubsubTopic    = config('services.gmail.pubsub_topic');
        $webhookUrl     = config('app.url') . '/workers/ava/gmail/webhook';

        return [
            'watches'        => $watches,
            'pubsub_topic'   => $pubsubTopic,
            'webhook_url'    => $webhookUrl,
            'total'          => $watches->count(),
            'healthy'        => $watches->where('is_expired', false)->where('watch_active', 1)->count(),
            'expired'        => $watches->where('is_expired', true)->count(),
            'expiring_soon'  => $watches->where('expires_soon', true)->count(),
        ];
    }

    // ── Connectors ───────────────────────────────────────────────────────────

    private function connectors(): array
    {
        return [
            [
                'name'    => 'Anthropic (Claude)',
                'type'    => 'ai',
                'icon'    => '🤖',
                'status'  => !empty(config('services.claude.api_key')) ? 'connected' : 'missing',
                'detail'  => 'Model: ' . config('services.claude.model'),
                'config'  => 'CLAUDE_API_KEY · CLAUDE_MODEL',
            ],
            [
                'name'    => 'Google Pub/Sub',
                'type'    => 'messaging',
                'icon'    => '📡',
                'status'  => !empty(config('services.gmail.pubsub_topic')) ? 'connected' : 'missing',
                'detail'  => config('services.gmail.pubsub_topic'),
                'config'  => 'GMAIL_PUBSUB_TOPIC',
            ],
            [
                'name'    => 'Gmail OAuth',
                'type'    => 'oauth',
                'icon'    => '📬',
                'status'  => !empty(config('services.gmail.client_id')) ? 'connected' : 'missing',
                'detail'  => 'Client: ' . substr(config('services.gmail.client_id', ''), 0, 20) . '...',
                'config'  => 'GMAIL_CLIENT_ID · GMAIL_CLIENT_SECRET',
            ],
            [
                'name'    => 'Stripe',
                'type'    => 'billing',
                'icon'    => '💳',
                'status'  => !empty(config('cashier.key')) ? 'connected' : 'missing',
                'detail'  => 'Key: ' . substr(config('cashier.key', ''), 0, 7) . '...',
                'config'  => 'STRIPE_KEY · STRIPE_SECRET · STRIPE_WEBHOOK_SECRET',
            ],
            [
                'name'    => 'SMTP (unit.report)',
                'type'    => 'email',
                'icon'    => '✉️',
                'status'  => !empty(config('mail.mailers.smtp.host')) ? 'connected' : 'missing',
                'detail'  => config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'),
                'config'  => 'MAIL_HOST · MAIL_PORT · MAIL_USERNAME',
            ],
            [
                'name'    => 'Redis',
                'type'    => 'queue',
                'icon'    => '⚡',
                'status'  => $this->checkRedis(),
                'detail'  => config('database.redis.default.host') . ':' . config('database.redis.default.port'),
                'config'  => 'REDIS_HOST · REDIS_PORT',
            ],
        ];
    }

    private function checkRedis(): string
    {
        try {
            app('redis')->ping();
            return 'connected';
        } catch (\Throwable $e) {
            return 'error';
        }
    }

    // ── Business Stats ───────────────────────────────────────────────────────

    private function businessStats(): array
    {
        $totalRevenue = DB::table('usage_events')->sum('cost_usd'); // platform cost side

        $mrr = DB::table('deployment_billing')
            ->join('worker_pricing', 'worker_pricing.worker_slug', '=', 'deployment_billing.worker_slug')
            ->where('deployment_billing.status', 'active')
            ->sum('worker_pricing.monthly_flat_rate');

        // Activation funnel
        $totalSignups    = DB::table('users')->where('role', 'tenant')->count();
        $deployedAny     = DB::table('users')->where('role', 'tenant')
            ->whereExists(fn($q) => $q->from('worker_deployments')->whereColumn('user_id', 'users.id'))->count();
        $completedTrial  = DB::table('users')->where('role', 'tenant')
            ->whereExists(fn($q) => $q->from('deployment_billing')->whereColumn('user_id', 'users.id')->where('trial_transactions_used', '>=', 5))->count();
        $converted       = DB::table('users')->where('role', 'tenant')
            ->whereExists(fn($q) => $q->from('deployment_billing')->whereColumn('user_id', 'users.id')->where('status', 'active'))->count();

        // Referral stats
        $totalReferrals  = DB::table('referral_credits')->count();
        $creditIssued    = DB::table('referral_credits')->sum('credit_usd');

        // Influencer count
        $influencers = DB::table('influencers')->where('status', 'approved')->count();

        return [
            'mrr'              => (float) $mrr,
            'total_ai_cost'    => (float) $totalRevenue,
            'funnel_signups'   => $totalSignups,
            'funnel_deployed'  => $deployedAny,
            'funnel_trial_used'=> $completedTrial,
            'funnel_converted' => $converted,
            'referrals_total'  => $totalReferrals,
            'credits_issued'   => (float) $creditIssued,
            'influencers'      => $influencers,
        ];
    }

    // ── Recent Policy Events ─────────────────────────────────────────────────

    private function recentPolicyEvents(): array
    {
        return DB::table('policy_enforcement_log as p')
            ->join('users', 'users.id', '=', 'p.user_id')
            ->select('p.*', 'users.name as tenant_name', 'users.email as tenant_email')
            ->orderByDesc('p.created_at')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // ── Failed Jobs ──────────────────────────────────────────────────────────

    private function failedJobs(): array
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload  = json_decode($job->payload, true);
                $job->job_name = $payload['displayName'] ?? basename(str_replace('\\', '/', $payload['job'] ?? ''));
                $job->short_error = substr($job->exception, 0, 200);
                return $job;
            })
            ->toArray();
    }

    // ── Section view ─────────────────────────────────────────────────────────

    public function section(string $section)
    {
        return redirect()->route('admin.platform')->withFragment($section);
    }

    // ── Queue Actions ────────────────────────────────────────────────────────

    public function queueRetryAll()
    {
        $count = DB::table('failed_jobs')->count();
        if ($count === 0) return back()->with('ct_success', 'No failed jobs to retry.');

        DB::table('jobs')->insertUsing(
            ['queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'],
            DB::table('failed_jobs')->select(
                DB::raw("'default'"),
                'payload',
                DB::raw('0'),
                DB::raw('NULL'),
                DB::raw('UNIX_TIMESTAMP()'),
                DB::raw('UNIX_TIMESTAMP()')
            )
        );
        DB::table('failed_jobs')->delete();
        return back()->with('ct_success', "Re-queued {$count} failed job(s).");
    }

    public function queueClearFailed()
    {
        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->delete();
        return back()->with('ct_success', "Cleared {$count} failed job(s) permanently.");
    }

    public function queueClearStuck()
    {
        $count = DB::table('transactions')
            ->whereIn('status', ['received','ingesting','reading','classifying','memory_lookup','templating','drafting','pushing'])
            ->where('updated_at', '<', now()->subMinutes(30))
            ->count();
        DB::table('transactions')
            ->whereIn('status', ['received','ingesting','reading','classifying','memory_lookup','templating','drafting','pushing'])
            ->where('updated_at', '<', now()->subMinutes(30))
            ->update(['status' => 'failed', 'updated_at' => now()]);
        return back()->with('ct_success', "Marked {$count} stuck transaction(s) as failed.");
    }

    // ── AI Model Actions ─────────────────────────────────────────────────────

    public function switchModel(Request $request)
    {
        $allowed = ['claude-haiku-4-5-20251001', 'claude-sonnet-4-6', 'claude-opus-4-8'];
        $model   = $request->input('model');
        if (!in_array($model, $allowed)) return back()->with('ct_error', 'Invalid model selection.');

        $envPath = base_path('.env');
        $content = file_get_contents($envPath);
        $content = preg_replace('/^CLAUDE_MODEL=.*/m', 'CLAUDE_MODEL=' . $model, $content);
        file_put_contents($envPath, $content);

        // Clear config cache so new model takes effect
        \Artisan::call('config:clear');

        return back()->with('ct_success', "AI model switched to {$model}. Config cache cleared.");
    }

    // ── Gmail Watch Actions ──────────────────────────────────────────────────

    public function renewWatch(int $credentialId)
    {
        $credential = DB::table('user_gmail_credentials')->where('id', $credentialId)->firstOrFail();
        try {
            $watchService = app(GmailWatchService::class, ['credential' => $credential]);
            $result       = $watchService->watch(config('services.gmail.pubsub_topic'));
            $expiresAt    = date('Y-m-d H:i:s', (int)($result['expiration'] / 1000));
            DB::table('user_gmail_credentials')->where('id', $credentialId)->update([
                'watch_active'    => true,
                'watch_expires_at'=> $expiresAt,
                'updated_at'      => now(),
            ]);
            return back()->with('ct_success', "Watch renewed for {$credential->gmail_address} — expires {$expiresAt}.");
        } catch (\Throwable $e) {
            return back()->with('ct_error', "Watch renewal failed: " . $e->getMessage());
        }
    }

    public function renewAllWatches()
    {
        $credentials = DB::table('user_gmail_credentials')->where('watch_active', 1)->get();
        $renewed = 0; $errors = [];
        foreach ($credentials as $cred) {
            try {
                $watchService = app(GmailWatchService::class, ['credential' => $cred]);
                $result       = $watchService->watch(config('services.gmail.pubsub_topic'));
                $expiresAt    = date('Y-m-d H:i:s', (int)($result['expiration'] / 1000));
                DB::table('user_gmail_credentials')->where('id', $cred->id)->update([
                    'watch_active'    => true,
                    'watch_expires_at'=> $expiresAt,
                    'updated_at'      => now(),
                ]);
                $renewed++;
            } catch (\Throwable $e) {
                $errors[] = $cred->gmail_address . ': ' . $e->getMessage();
            }
        }
        $msg = "Renewed {$renewed} watch(es).";
        if ($errors) $msg .= ' Errors: ' . implode('; ', $errors);
        return back()->with($errors ? 'ct_error' : 'ct_success', $msg);
    }

    public function deactivateWatch(int $credentialId)
    {
        $credential = DB::table('user_gmail_credentials')->where('id', $credentialId)->firstOrFail();
        DB::table('user_gmail_credentials')->where('id', $credentialId)->update([
            'watch_active' => false,
            'updated_at'   => now(),
        ]);
        return back()->with('ct_success', "Watch deactivated for {$credential->gmail_address}. AVA will no longer monitor this inbox.");
    }

    // ── SMTP Route Edit ──────────────────────────────────────────────────────

    public function updateSmtpRoute(Request $request, string $routeKey)
    {
        $request->validate([
            'name'         => 'required|string|max:60',
            'purpose'      => 'required|string|max:200',
            'host'         => 'required|string|max:200',
            'port'         => 'required|integer',
            'encryption'   => 'required|in:ssl,tls,starttls,none',
            'username'     => 'required|string|max:200',
            'from_address' => 'required|email',
            'from_name'    => 'required|string|max:100',
        ]);

        $config = DB::table('platform_configs')->where('key', 'smtp_routes')->first();
        $routes = $config ? json_decode($config->value, true) : [];

        $idx = collect($routes)->search(fn($r) => $r['key'] === $routeKey);
        if ($idx === false) {
            $routes[] = ['key' => $routeKey];
            $idx = count($routes) - 1;
        }

        $routes[$idx] = array_merge($routes[$idx], [
            'key'          => $routeKey,
            'name'         => $request->name,
            'purpose'      => $request->purpose,
            'host'         => $request->host,
            'port'         => (int) $request->port,
            'encryption'   => $request->encryption,
            'username'     => $request->username,
            'from_address' => $request->from_address,
            'from_name'    => $request->from_name,
            'active'       => $request->boolean('active', true),
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $routes[$idx]['password'] = $request->password;
        }

        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'smtp_routes'],
            ['value' => json_encode(array_values($routes)), 'updated_at' => now()]
        );

        return back()->with('ct_success', "SMTP route [{$routeKey}] updated.");
    }

    public function addSmtpRoute(Request $request)
    {
        $request->validate(['key' => 'required|string|alpha_dash|max:40']);
        $config = DB::table('platform_configs')->where('key', 'smtp_routes')->first();
        $routes = $config ? json_decode($config->value, true) : [];

        if (collect($routes)->firstWhere('key', $request->key)) {
            return back()->with('ct_error', "Route key [{$request->key}] already exists.");
        }

        $routes[] = [
            'key'          => $request->key,
            'name'         => ucfirst($request->key),
            'purpose'      => '',
            'host'         => config('mail.mailers.smtp.host'),
            'port'         => config('mail.mailers.smtp.port', 465),
            'encryption'   => 'ssl',
            'username'     => config('mail.mailers.smtp.username'),
            'password'     => '',
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
            'active'       => true,
        ];

        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'smtp_routes'],
            ['value' => json_encode($routes), 'updated_at' => now()]
        );

        return back()->with('ct_success', "SMTP route [{$request->key}] created. Edit it to set credentials.");
    }

    public function deleteSmtpRoute(string $routeKey)
    {
        $config = DB::table('platform_configs')->where('key', 'smtp_routes')->first();
        $routes = $config ? json_decode($config->value, true) : [];
        $routes = array_values(array_filter($routes, fn($r) => $r['key'] !== $routeKey));
        DB::table('platform_configs')->where('key', 'smtp_routes')->update(['value' => json_encode($routes), 'updated_at' => now()]);
        return back()->with('ct_success', "SMTP route [{$routeKey}] deleted.");
    }

    // ── Worker Deployment Actions ────────────────────────────────────────────

    public function pauseAllDeployments(string $workerSlug)
    {
        $count = DB::table('worker_deployments')
            ->where('worker_slug', $workerSlug)
            ->where('status', 'active')
            ->update(['status' => 'paused', 'updated_at' => now()]);
        return back()->with('ct_success', "Paused {$count} active deployment(s) for worker [{$workerSlug}].");
    }

    public function resumeAllDeployments(string $workerSlug)
    {
        $count = DB::table('worker_deployments')
            ->where('worker_slug', $workerSlug)
            ->where('status', 'paused')
            ->update(['status' => 'active', 'updated_at' => now()]);
        return back()->with('ct_success', "Resumed {$count} paused deployment(s) for worker [{$workerSlug}].");
    }

    public function stopAllDeployments(string $workerSlug)
    {
        $count = DB::table('worker_deployments')
            ->where('worker_slug', $workerSlug)
            ->whereIn('status', ['active', 'paused'])
            ->update(['status' => 'stopped', 'updated_at' => now()]);
        return back()->with('ct_success', "Stopped {$count} deployment(s) for worker [{$workerSlug}]. No new transactions will process.");
    }

    public function startAllDeployments(string $workerSlug)
    {
        $count = DB::table('worker_deployments')
            ->where('worker_slug', $workerSlug)
            ->where('status', 'stopped')
            ->update(['status' => 'active', 'updated_at' => now()]);
        return back()->with('ct_success', "Started {$count} stopped deployment(s) for worker [{$workerSlug}].");
    }

    // ── SMTP Test ────────────────────────────────────────────────────────────

    public function toggleTrialGate(Request $request)
    {
        $value = $request->input('value', 'false');
        $value = in_array($value, ['true', 'false']) ? $value : 'false';
        DB::table('platform_configs')->where('key', 'trial_payment_required')->update([
            'value'      => $value,
            'updated_at' => now(),
        ]);
        $label = $value === 'true' ? 'Trial now requires a payment method.' : 'Trial is now free — no card required.';
        return back()->with('success', $label);
    }

    public function testSmtp(Request $request)
    {
        $to = $request->input('to', auth()->user()->email);
        try {
            Mail::raw('This is a test email from the UNIT Platform Control Tower. SMTP is working correctly.', function ($m) use ($to) {
                $m->to($to)
                  ->from(config('mail.from.address'), config('mail.from.name'))
                  ->subject('UNIT SMTP Test — ' . now()->format('H:i:s'));
            });
            return back()->with('ct_success', "Test email sent to {$to}. Check your inbox.");
        } catch (\Throwable $e) {
            return back()->with('ct_error', 'SMTP test failed: ' . $e->getMessage());
        }
    }

    // ── Cache / Config Actions ───────────────────────────────────────────────

    public function clearCaches()
    {
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        Cache::flush();
        return back()->with('ct_success', 'Config, route, view caches cleared. Application cache flushed.');
    }

    public function resetHistoryId(int $credentialId)
    {
        $cred = DB::table('user_gmail_credentials')->where('id', $credentialId)->firstOrFail();
        try {
            $watchService = app(GmailWatchService::class, ['credential' => $cred]);
            // Use reflection to call private getAccessToken
            $r = new \ReflectionClass($watchService);
            $tokenProp = $r->getProperty('accessToken');
            $tokenProp->setAccessible(true);
            $token = $tokenProp->getValue($watchService);

            $profile = Http::withToken($token)
                ->get('https://gmail.googleapis.com/gmail/v1/users/me/profile')
                ->json();

            $historyId = $profile['historyId'];
            DB::table('ava_state')->upsert(
                ['key' => 'gmail_history_id_' . $cred->id, 'value' => $historyId, 'updated_at' => now(), 'created_at' => now()],
                ['key'], ['value', 'updated_at']
            );
            return back()->with('ct_success', "History ID reset to {$historyId} for {$cred->gmail_address}. AVA will only process new emails from now.");
        } catch (\Throwable $e) {
            return back()->with('ct_error', 'Reset failed: ' . $e->getMessage());
        }
    }
}
