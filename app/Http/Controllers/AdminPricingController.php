<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPricingController extends Controller
{
    public function index()
    {
        $plans = DB::table('worker_pricing')->orderBy('worker_slug')->orderBy('sort_order')->get();

        $existingSlugs   = $plans->pluck('worker_slug')->unique()->toArray();
        $registryWorkers = DB::table('worker_registry')
            ->whereNotIn('slug', $existingSlugs)
            ->orderBy('name')
            ->get(['slug', 'name', 'description']);

        $stripeTestMode = str_starts_with(config('cashier.secret', ''), 'sk_test_');
        $stripeDashBase = 'https://dashboard.stripe.com/' . ($stripeTestMode ? 'test/' : '');

        return view('admin.pricing', compact('plans', 'registryWorkers', 'stripeTestMode', 'stripeDashBase'));
    }

    /**
     * Verify a Stripe Price ID and return its details — called via JS fetch.
     */
    public function verifyStripePrice(Request $request)
    {
        $priceId = trim($request->input('price_id', ''));
        if (!$priceId) {
            return response()->json(['error' => 'No price ID provided.'], 422);
        }
        if (!config('cashier.secret')) {
            return response()->json(['error' => 'Stripe not configured.'], 422);
        }
        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
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
            'stripe_flat_price_id' => 'nullable|string|max:120',
            'stripe_coupon_id'     => 'nullable|string|max:120',
            'discount_pct'         => 'nullable|numeric|min:0|max:100',
            'promo_label'          => 'nullable|string|max:80',
            'promo_expires_at'     => 'nullable|date',
        ]);

        $data['plan_highlights']       = $this->highlightsToJson($data['plan_highlights'] ?? '');
        $data['included_transactions'] = $data['transaction_limit'] ?? 0;

        DB::table('worker_pricing')->insert(array_merge($data, [
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return redirect()->route('admin.pricing')->with('success', 'Worker pricing created.');
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
            'stripe_flat_price_id' => 'nullable|string|max:120',
            'stripe_coupon_id'     => 'nullable|string|max:120',
            'discount_pct'         => 'nullable|numeric|min:0|max:100',
            'promo_label'          => 'nullable|string|max:80',
            'promo_expires_at'     => 'nullable|date',
        ]);

        $data['plan_highlights']       = $this->highlightsToJson($data['plan_highlights'] ?? '');
        $data['included_transactions'] = $data['transaction_limit'] ?? 0;

        DB::table('worker_pricing')->where('id', $id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return redirect()->route('admin.pricing')->with('success', 'Pricing updated.');
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

    private function highlightsToJson(string $raw): string
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $raw))));
        return json_encode($lines);
    }
}
