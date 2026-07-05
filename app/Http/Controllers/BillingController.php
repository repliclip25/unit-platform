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
            ->whereIn('status', ['active', 'paused', 'stopped'])
            ->get();

        $billingRecords = DB::table('deployment_billing')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('deployment_id');

        // Subscription plans only — trial plans are never shown as upgrade options
        $pricingTiers = DB::table('worker_pricing')
            ->where('active', true)
            ->where('is_trial_plan', false)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('worker_slug');

        $promotions = DB::table('platform_promotions')
            ->where('active', true)
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->whereNull('code')
            ->get();

        try {
            $invoices = $user->stripe_id ? $user->invoices() : collect();
        } catch (\Throwable $e) {
            $invoices = collect();
        }

        $policyViolations = \App\Platform\Services\PolicyEngine::evaluateAll($user->id);

        return view('dashboard.billing', compact(
            'deployments', 'billingRecords', 'pricingTiers', 'promotions',
            'invoices', 'policyViolations'
        ));
    }

    // Unified checkout — adds a subscription item to the tenant's master subscription.
    // If no master subscription exists, creates one. Requires ?plan=starter|pro|enterprise
    public function checkout(Request $request, int $deploymentId)
    {
        $user       = $request->user();
        $deployment = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $planSlug = $request->input('plan', 'starter');

        $pricing = DB::table('worker_pricing')
            ->where('worker_slug', $deployment->worker_slug)
            ->where('plan_slug', $planSlug)
            ->where('active', true)
            ->first();

        if (!$pricing) {
            return back()->with('error', 'Plan not found for this worker.');
        }

        // Resolve billing mode — determines which Stripe environment and which price ID to use
        $billingMode = $pricing->billing_mode ?? 'test';
        $isLive      = $billingMode === 'live';
        $priceId     = $isLive
            ? ($pricing->stripe_flat_price_id ?? null)
            : ($pricing->stripe_test_price_id ?? $pricing->stripe_flat_price_id ?? null);

        if ($planSlug === 'enterprise' || !$priceId) {
            return redirect()->route('billing')
                ->with('info', 'Enterprise pricing is custom. Please contact ' . config('services.unit.support_email') . ' to get started.');
        }

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer(['name' => $user->name, 'email' => $user->email]);
        }

        // Apply any accrued referral credit balance as a Stripe customer balance credit.
        // Stripe deducts it automatically from the next invoice — no coupon required.
        $creditBalance = (float) DB::table('users')->where('id', $user->id)->value('referral_credit_balance');
        if ($creditBalance > 0) {
            try {
                $stripe = new \Stripe\StripeClient(config('cashier.secret'));
                // Stripe customer balance is in cents, negative = credit
                $stripe->customers->update($user->stripe_id, [
                    'balance' => (int) round(-$creditBalance * 100),
                ]);
                DB::table('users')->where('id', $user->id)->update([
                    'referral_credit_balance' => 0,
                    'updated_at'              => now(),
                ]);
                \Illuminate\Support\Facades\Log::info('Referral credit applied to Stripe customer', [
                    'user_id'    => $user->id,
                    'credit_usd' => $creditBalance,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to apply referral credit to Stripe', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        session(['pending_plan_slug_' . $deploymentId => $planSlug]);

        // If this deployment is still in trial, carry over the remaining days so Stripe shows
        // "X days free, then $Y/mo" and does not charge until the trial ends.
        $billing         = DB::table('deployment_billing')->where('deployment_id', $deploymentId)->first();
        $remainingTrial  = 0;
        if ($billing?->status === 'trial' && $billing->trial_ends_at) {
            $remainingTrial = max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($billing->trial_ends_at), false));
        }

        $existingSubId = DB::table('users')->where('id', $user->id)->value('stripe_subscription_id');

        if ($existingSubId) {
            $builder = $user->newSubscriptionItem($priceId);
        } else {
            $builder = $user->newSubscription('platform', $priceId);
        }

        // Apply Stripe coupon if one is configured for this plan
        if (!empty($pricing->stripe_coupon_id)) {
            $builder = $builder->withCoupon($pricing->stripe_coupon_id);
        } else {
            $builder = $builder->allowPromotionCodes();
        }

        if ($remainingTrial > 0) {
            $builder = $builder->trialDays($remainingTrial);
        }

        try {
            $session = $builder->checkout([
                'success_url' => route('billing.success', $deploymentId),
                'cancel_url'  => route('billing'),
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Coupon in DB doesn't exist in Stripe — retry without it
            if (str_contains($e->getMessage(), 'coupon')) {
                $builder2 = $existingSubId
                    ? $user->newSubscriptionItem($pricing->stripe_flat_price_id)
                    : $user->newSubscription('platform', $pricing->stripe_flat_price_id);
                $builder2 = $builder2->allowPromotionCodes();
                if ($remainingTrial > 0) $builder2 = $builder2->trialDays($remainingTrial);
                $session = $builder2->checkout([
                    'success_url' => route('billing.success', $deploymentId),
                    'cancel_url'  => route('billing'),
                ]);
            } else {
                throw $e;
            }
        }

        return redirect($session->url);
    }

    // After successful Stripe checkout — wire the subscription/item IDs to the deployment
    public function success(Request $request, int $deploymentId)
    {
        $user = $request->user();

        // Ownership check — prevent one tenant from activating another's deployment
        $deployment = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$deployment) {
            abort(403);
        }

        $planSlug = session('pending_plan_slug_' . $deploymentId, 'starter');
        session()->forget('pending_plan_slug_' . $deploymentId);

        // Resolve the Stripe subscription ID — either existing master or newly created
        $stripeSubId     = DB::table('users')->where('id', $user->id)->value('stripe_subscription_id');
        $stripeItemId    = null;

        try {
            // Get the most recent subscription for this user from Cashier
            $subscription = $user->subscriptions()->latest()->first();
            if ($subscription) {
                if (!$stripeSubId) {
                    // First worker — store master subscription on user
                    $stripeSubId = $subscription->stripe_id;
                    DB::table('users')->where('id', $user->id)->update([
                        'stripe_subscription_id' => $stripeSubId,
                        'updated_at'             => now(),
                    ]);
                }
                // Get the item for this price
                $pricing = DB::table('worker_pricing')
                    ->where('worker_slug', DB::table('worker_deployments')->where('id', $deploymentId)->value('worker_slug'))
                    ->where('plan_slug', $planSlug)
                    ->first();

                if ($pricing?->stripe_flat_price_id) {
                    $stripeItem = $subscription->items()
                        ->where('stripe_price', $pricing->stripe_flat_price_id)
                        ->first();
                    $stripeItemId = $stripeItem?->stripe_id;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Billing success: failed to resolve Stripe item', [
                'deployment_id' => $deploymentId,
                'error'         => $e->getMessage(),
            ]);
        }

        DB::table('deployment_billing')
            ->where('deployment_id', $deploymentId)
            ->update([
                'status'                    => 'active',
                'plan_slug'                 => $planSlug,
                'stripe_subscription_item_id' => $stripeItemId,
                'billing_period_start'      => now()->startOfMonth(),
                'unit_count'                => 0,
                'updated_at'                => now(),
            ]);

        \App\Platform\Services\ReferralService::handleConversion($user->id);
        \App\Platform\Services\InfluencerService::handleConversion($user->id);

        $planLabel = ucfirst($planSlug);
        return redirect()->route('workers.show', $deploymentId)
            ->with('success', ucfirst($deployment->worker_slug) . " {$planLabel} plan activated. You're fully operational.");
    }

    // Re-activate a canceled deployment — sends user back through checkout for a fresh subscription.
    // Workers canceled via Stripe portal land here via the worker-detail "Reactivate" button.
    public function reactivate(Request $request, int $deploymentId)
    {
        $user       = $request->user();
        $deployment = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $billing = DB::table('deployment_billing')
            ->where('deployment_id', $deploymentId)
            ->first();

        if (!$billing || $billing->status !== 'canceled') {
            return back()->with('error', 'This worker is not canceled.');
        }

        $planSlug = $request->input('plan', 'pro'); // default to pro on reactivation

        $pricing = DB::table('worker_pricing')
            ->where('worker_slug', $deployment->worker_slug)
            ->where('plan_slug', $planSlug)
            ->where('active', true)
            ->where('is_trial_plan', false)
            ->first();

        if (!$pricing) {
            return back()->with('error', 'Plan not found.');
        }

        $billingMode = $pricing->billing_mode ?? 'test';
        $priceId     = $billingMode === 'live'
            ? ($pricing->stripe_flat_price_id ?? null)
            : ($pricing->stripe_test_price_id ?? $pricing->stripe_flat_price_id ?? null);

        if (!$priceId) {
            return back()->with('error', 'No Stripe price configured for this plan.');
        }

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer(['name' => $user->name, 'email' => $user->email]);
        }

        // Restore worker to active state before checkout so pipeline is unblocked on success
        DB::table('worker_deployments')->where('id', $deploymentId)->update([
            'status'     => 'active',
            'updated_at' => now(),
        ]);

        session(['pending_plan_slug_' . $deploymentId => $planSlug]);

        $existingSubId = DB::table('users')->where('id', $user->id)->value('stripe_subscription_id');
        $builder = $existingSubId
            ? $user->newSubscriptionItem($priceId)
            : $user->newSubscription('platform', $priceId);
        $builder = $builder->allowPromotionCodes();

        $session = $builder->checkout([
            'success_url' => route('billing.success', $deploymentId),
            'cancel_url'  => route('workers.show', $deploymentId),
        ]);

        return redirect($session->url);
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

}
