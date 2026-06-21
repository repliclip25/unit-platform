<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    // Billing overview page
    public function index(Request $request)
    {
        $user        = $request->user();
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'paused'])
            ->get();

        $billingRecords = DB::table('deployment_billing')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('deployment_id');

        $pricing = DB::table('worker_pricing')->get()->keyBy('worker_slug');

        $promotions = DB::table('platform_promotions')
            ->where('active', true)
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->whereNull('code') // auto-applied only
            ->get();

        $monthlyUsage = DB::table('usage_events')
            ->where('user_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('SUM(cost_usd) as total_cost, SUM(tokens_input + tokens_output) as total_tokens')
            ->first();

        // Per-worker spend this month
        $workerSpend = DB::table('usage_events')
            ->where('user_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->groupBy('deployment_id')
            ->selectRaw('deployment_id, SUM(cost_usd) as cost, SUM(tokens_input+tokens_output) as tokens')
            ->get()->keyBy('deployment_id');

        // Per-stage breakdown
        $stageBreakdown = DB::table('usage_events')
            ->where('user_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNotNull('stage')
            ->groupBy('stage')
            ->selectRaw('stage, SUM(cost_usd) as cost, SUM(tokens_input+tokens_output) as tokens, COUNT(*) as calls')
            ->orderByRaw('SUM(cost_usd) DESC')
            ->get();

        // Daily spend last 30 days
        $dailySpend = DB::table('usage_events')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(cost_usd) as cost')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('cost', 'day');

        try {
            $invoices = $user->stripe_id ? $user->invoices() : collect();
        } catch (\Throwable $e) {
            $invoices = collect();
        }

        $spendCap        = (float) ($user->monthly_spend_cap ?? 0);
        $policyViolations = \App\Platform\Services\PolicyEngine::evaluateAll($user->id);

        return view('dashboard.billing', compact(
            'deployments', 'billingRecords', 'pricing', 'promotions',
            'monthlyUsage', 'invoices', 'workerSpend', 'stageBreakdown',
            'dailySpend', 'spendCap', 'policyViolations'
        ));
    }

    // Stripe Checkout — called when worker is deployed
    public function checkout(Request $request, int $deploymentId)
    {
        $user       = $request->user();
        $deployment = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $pricing = DB::table('worker_pricing')
            ->where('worker_slug', $deployment->worker_slug)
            ->where('active', true)
            ->first();

        if (!$pricing) {
            return back()->with('error', 'No pricing found for this worker.');
        }

        // Ensure user exists as a Stripe customer
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer([
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        }

        $session = $user->newSubscription('worker_' . $deploymentId, $pricing->stripe_flat_price_id)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success', $deploymentId),
                'cancel_url'  => route('workers.show', $deploymentId),
            ]);

        return redirect($session->url);
    }

    // After successful Stripe checkout
    public function success(Request $request, int $deploymentId)
    {
        DB::table('deployment_billing')
            ->where('deployment_id', $deploymentId)
            ->update(['status' => 'active', 'billing_period_start' => now()->startOfMonth(), 'updated_at' => now()]);

        // Fire referral conversion (handles both peer and influencer)
        $userId = $request->user()->id;
        \App\Platform\Services\ReferralService::handleConversion($userId);
        \App\Platform\Services\InfluencerService::handleConversion($userId);

        return redirect()->route('workers.show', $deploymentId)
            ->with('success', 'Subscription activated. AVA is fully operational.');
    }

    // Billing portal — manage payment method, invoices, cancel
    public function portal(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer([
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        }

        return $user->redirectToBillingPortal(route('billing'));
    }

    // Get highest active auto-applied discount for a worker
    private function getActiveDiscount(string $workerSlug): float
    {
        $discount = DB::table('platform_promotions')
            ->where('active', true)
            ->whereNull('code')
            ->where(function ($q) use ($workerSlug) {
                $q->where('applies_to', 'all')
                  ->orWhere(function ($q2) use ($workerSlug) {
                      $q2->where('applies_to', 'worker')->where('worker_slug', $workerSlug);
                  });
            })
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->orderByDesc('discount_pct')
            ->value('discount_pct');

        return (float) ($discount ?? 0);
    }
}
