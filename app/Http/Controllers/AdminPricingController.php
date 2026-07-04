<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPricingController extends Controller
{
    public function index()
    {
        $plans = DB::table('worker_pricing')->orderBy('worker_slug')->orderBy('sort_order')->get();

        $registryWorkers = DB::table('worker_registry')
            ->orderBy('name')
            ->get(['slug', 'name', 'description']);

        // Build AI stages map keyed by worker_slug — drives the dynamic model-slot
        // labels in the pricing edit panel without any hardcoding in the blade.
        $aiStagesMap = [];
        foreach ($registryWorkers as $w) {
            try {
                $contract = WorkerRegistry::resolve($w->slug);
                $aiStagesMap[$w->slug] = $contract->aiStages();
            } catch (\Throwable) {
                $aiStagesMap[$w->slug] = [];
            }
        }

        $stripeTestMode = str_starts_with(config('cashier.secret', ''), 'sk_test_');
        $stripeDashBase = 'https://dashboard.stripe.com/' . ($stripeTestMode ? 'test/' : '');

        return view('admin.pricing', compact('plans', 'registryWorkers', 'aiStagesMap', 'stripeTestMode', 'stripeDashBase'));
    }

    /**
     * Verify a Stripe Price ID and return its details — called via JS fetch.
     * Uses test key if the request includes mode=test, live key otherwise.
     */
    public function verifyStripePrice(Request $request)
    {
        $priceId = trim($request->input('price_id', ''));
        if (!$priceId) {
            return response()->json(['error' => 'No price ID provided.'], 422);
        }

        $mode   = $request->input('mode', 'live');
        $secret = $mode === 'test'
            ? (config('services.stripe.test_secret') ?: config('cashier.secret'))
            : (config('services.stripe.live_secret') ?: config('cashier.secret'));

        if (!$secret) {
            return response()->json(['error' => 'Stripe not configured.'], 422);
        }
        try {
            $stripe = new \Stripe\StripeClient($secret);
            $price  = $stripe->prices->retrieve($priceId, ['expand' => ['product']]);
            return response()->json([
                'id'        => $price->id,
                'amount'    => ($price->unit_amount ?? 0) / 100,
                'currency'  => strtoupper($price->currency),
                'interval'  => $price->recurring?->interval ?? 'month',
                'active'    => $price->active,
                'nickname'  => $price->nickname ?? null,
                'product'   => is_object($price->product) ? ($price->product->name ?? $price->product->id) : $price->product,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Verify a Stripe Coupon ID and return its details — called via JS fetch.
     */
    public function verifyStripeCoupon(Request $request)
    {
        $couponId = trim($request->input('coupon_id', ''));
        if (!$couponId) {
            return response()->json(['error' => 'No coupon ID provided.'], 422);
        }
        if (!config('cashier.secret')) {
            return response()->json(['error' => 'Stripe not configured.'], 422);
        }
        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $coupon = $stripe->coupons->retrieve($couponId);
            $summary = $coupon->percent_off
                ? $coupon->percent_off . '% off'
                : '$' . number_format(($coupon->amount_off ?? 0) / 100, 2) . ' off';
            return response()->json([
                'id'       => $coupon->id,
                'summary'  => $summary,
                'duration' => $coupon->duration,
                'valid'    => $coupon->valid,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        if ($request->input('worker_slug') === '__custom__' || !$request->input('worker_slug')) {
            $request->merge(['worker_slug' => $request->input('worker_slug_custom', '')]);
        }
        if (!$request->filled('display_name')) {
            $request->merge(['display_name' => strtoupper($request->input('worker_slug', ''))]);
        }

        $data = $request->validate([
            'worker_slug'          => 'required|string|max:60',
            'plan_slug'            => 'required|string|max:60',
            'display_name'         => 'required|string|max:120',
            'tagline'              => 'nullable|string|max:255',
            'transaction_label'    => 'nullable|string|max:255',
            'worker_url'           => 'nullable|string|max:255',
            'accent_color'         => 'nullable|string|max:20',
            'sort_order'           => 'nullable|integer',
            'free_transactions'    => 'required|integer|min:0',
            'monthly_flat_rate'    => 'required|numeric|min:0',
            'transaction_limit'    => 'nullable|integer|min:0',
            'overage_price_per_tx' => 'nullable|numeric|min:0',
            'plan_highlights'      => 'nullable|string',
            'support_label'        => 'nullable|string|max:120',
            'billing_mode'           => 'nullable|in:live,test',
            'stripe_flat_price_id'   => 'nullable|string|max:120',
            'stripe_test_price_id'   => 'nullable|string|max:120',
            'stripe_coupon_id'       => 'nullable|string|max:120',
            'discount_pct'           => 'nullable|numeric|min:0|max:100',
            'promo_label'            => 'nullable|string|max:80',
            'promo_expires_at'       => 'nullable|date',
            'ai_tier'                => 'nullable|in:economy,standard,premium',
            'classify_model'         => 'nullable|string|max:80',
            'draft_model'            => 'nullable|string|max:80',
            'draft_model_threshold'  => 'nullable|integer|min:0',
            'stage_models'           => 'nullable|array',
            'stage_models.*'         => 'nullable|string|max:80',
        ]);

        $data['plan_highlights']       = $this->highlightsToJson($data['plan_highlights'] ?? '');
        $data['included_transactions'] = $data['transaction_limit'] ?? 0;
        $data['stage_models']          = !empty($data['stage_models'])
            ? json_encode(array_filter($data['stage_models']))
            : null;

        DB::table('worker_pricing')->insert(array_merge($data, [
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return redirect()->route('admin.pricing')->with('success', 'Plan created.');
    }

    public function update(Request $request, int $id)
    {
        DB::table('worker_pricing')->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'display_name'         => 'required|string|max:120',
            'tagline'              => 'nullable|string|max:255',
            'transaction_label'    => 'nullable|string|max:255',
            'worker_url'           => 'nullable|string|max:255',
            'accent_color'         => 'nullable|string|max:20',
            'sort_order'           => 'nullable|integer',
            'free_transactions'    => 'required|integer|min:0',
            'monthly_flat_rate'    => 'required|numeric|min:0',
            'transaction_limit'    => 'nullable|integer|min:0',
            'overage_price_per_tx' => 'nullable|numeric|min:0',
            'plan_highlights'      => 'nullable|string',
            'support_label'        => 'nullable|string|max:120',
            'billing_mode'           => 'nullable|in:live,test',
            'stripe_flat_price_id'   => 'nullable|string|max:120',
            'stripe_test_price_id'   => 'nullable|string|max:120',
            'stripe_coupon_id'       => 'nullable|string|max:120',
            'discount_pct'           => 'nullable|numeric|min:0|max:100',
            'promo_label'            => 'nullable|string|max:80',
            'promo_expires_at'       => 'nullable|date',
            'ai_tier'                => 'nullable|in:economy,standard,premium',
            'classify_model'         => 'nullable|string|max:80',
            'draft_model'            => 'nullable|string|max:80',
            'draft_model_threshold'  => 'nullable|integer|min:0',
            'stage_models'           => 'nullable|array',
            'stage_models.*'         => 'nullable|string|max:80',
        ]);

        $data['plan_highlights']       = $this->highlightsToJson($data['plan_highlights'] ?? '');
        $data['included_transactions'] = $data['transaction_limit'] ?? 0;
        $data['stage_models']          = !empty($data['stage_models'])
            ? json_encode(array_filter($data['stage_models']))
            : null;

        DB::table('worker_pricing')->where('id', $id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return redirect()->route('admin.pricing', ['editing' => $id])->with('success', 'Plan updated.');
    }

    public function toggle(int $id)
    {
        $plan = DB::table('worker_pricing')->find($id);
        if (!$plan) abort(404);
        DB::table('worker_pricing')->where('id', $id)->update([
            'active'     => !$plan->active,
            'updated_at' => now(),
        ]);
        return redirect()->route('admin.pricing')->with('success', $plan->active ? 'Plan hidden.' : 'Plan set live.');
    }

    /**
     * Set billing_mode for all plans under a worker slug in one shot.
     * Called from the pricing page billing mode toggle.
     */
    public function setBillingMode(Request $request, int $id)
    {
        $plan = DB::table('worker_pricing')->find($id);
        if (!$plan) abort(404);

        $mode = $request->input('billing_mode');
        if (!in_array($mode, ['live', 'test'])) abort(422);

        // Apply to all plans for this worker so Starter/Pro/Enterprise stay in sync
        DB::table('worker_pricing')
            ->where('worker_slug', $plan->worker_slug)
            ->update(['billing_mode' => $mode, 'updated_at' => now()]);

        return redirect()->route('admin.pricing', ['editing' => $id])
            ->with('success', strtoupper($plan->worker_slug) . ' billing switched to ' . strtoupper($mode) . ' mode.');
    }

    private function highlightsToJson(string $raw): string
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $raw))));
        return json_encode($lines);
    }
}
