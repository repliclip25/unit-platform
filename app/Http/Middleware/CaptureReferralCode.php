<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaptureReferralCode
{
    public function handle(Request $request, Closure $next)
    {
        // Tenant peer referral: ?ref=XXXXXXXX
        if ($request->has('ref') && !session('referral_code')) {
            session(['referral_code' => $request->query('ref')]);
        }

        // Influencer referral: ?via=slug (set by /r/{slug} redirect)
        if ($request->has('via') && !session('influencer_slug')) {
            $slug = $request->query('via');
            $exists = DB::table('influencers')->where('slug', $slug)->where('status', 'active')->exists();
            if ($exists) {
                session(['influencer_slug' => $slug]);
            }
        }

        return $next($request);
    }
}
