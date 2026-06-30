<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminMessagingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminInfluencerController extends Controller
{
    public function index()
    {
        // Single aggregated query — replaces N+1 (one count query per influencer)
        $stats = DB::table('influencers')
            ->leftJoin(DB::raw('(SELECT influencer_id, COUNT(*) as conversions FROM referral_credits WHERE event = "paid_conversion" GROUP BY influencer_id) as conv'), 'conv.influencer_id', '=', 'influencers.id')
            ->leftJoin(DB::raw('(SELECT influencer_id, COUNT(*) as clicks FROM referral_clicks GROUP BY influencer_id) as clk'), 'clk.influencer_id', '=', 'influencers.id')
            ->select('influencers.*', DB::raw('COALESCE(conv.conversions, 0) as conversions'), DB::raw('COALESCE(clk.clicks, 0) as clicks'))
            ->orderByDesc('influencers.created_at')
            ->get();

        return view('admin.influencers', compact('stats'));
    }

    public function show(int $id)
    {
        $influencer = DB::table('influencers')->where('id', $id)->firstOrFail();
        $stats      = \App\Platform\Services\InfluencerService::getStats($id);
        $credits    = DB::table('referral_credits')
            ->where('influencer_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        $clicks = DB::table('referral_clicks')
            ->where('influencer_id', $id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        return view('admin.influencer-detail', compact('influencer', 'stats', 'credits', 'clicks'));
    }

    public function approve(int $id)
    {
        $influencer = DB::table('influencers')->where('id', $id)->firstOrFail();

        DB::table('influencers')->where('id', $id)->update([
            'status'      => 'active',
            'approved_at' => now(),
            'updated_at'  => now(),
        ]);

        try {
            $tpl = AdminMessagingController::getTemplate('influencer_approved');
            if ($tpl) {
                $appUrl      = config('app.url');
                $ratePercent = round($influencer->commission_rate * 100) . '%';
                $replacements = [
                    '{name}'            => $influencer->name,
                    '{app_url}'         => $appUrl,
                    '{referral_url}'    => $appUrl . '/r/' . $influencer->slug,
                    '{slug}'            => $influencer->slug,
                    '{commission_rate}' => $ratePercent,
                ];
                $body    = str_replace(array_keys($replacements), array_values($replacements), $tpl->body);
                $subject = str_replace(array_keys($replacements), array_values($replacements), $tpl->subject);
                Mail::raw($body, fn($m) => $m->to($influencer->email, $influencer->name)->subject($subject)->replyTo('hello@unit.report', $tpl->from_name));
            }
        } catch (\Throwable $e) {
            Log::error('Influencer approval email failed', ['id' => $id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Influencer approved and email sent.');
    }

    public function update(int $id, Request $request)
    {
        $request->validate([
            'status'          => 'required|in:pending,active,paused,rejected',
            'tier'            => 'required|in:starter,pro,elite',
            'commission_rate' => 'required|numeric|min:0.01|max:0.50',
            'payout_email'    => 'nullable|email',
            'payout_method'   => 'required|in:paypal,bank,stripe',
            'notes'           => 'nullable|string|max:1000',
        ]);
        DB::table('influencers')->where('id', $id)->update([
            'status'          => $request->status,
            'tier'            => $request->tier,
            'commission_rate' => $request->commission_rate,
            'payout_email'    => $request->payout_email,
            'payout_method'   => $request->payout_method,
            'notes'           => $request->notes,
            'updated_at'      => now(),
        ]);
        return back()->with('success', 'Influencer updated.');
    }

    public function payout(int $id, Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $influencer = DB::table('influencers')->where('id', $id)->firstOrFail();
        $amount     = min((float)$request->amount, (float)$influencer->pending_payout);
        DB::table('influencers')->where('id', $id)->update([
            'pending_payout' => DB::raw("pending_payout - $amount"),
            'paid_out'       => DB::raw("paid_out + $amount"),
            'updated_at'     => now(),
        ]);
        DB::table('referral_credits')
            ->where('influencer_id', $id)
            ->where('status', 'pending_payout')
            ->update(['status' => 'paid', 'updated_at' => now()]);
        return back()->with('success', "Payout of \${$amount} recorded for {$influencer->name}.");
    }
}
