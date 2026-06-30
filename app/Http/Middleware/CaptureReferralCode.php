<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CaptureReferralCode
{
    // Referral codes are stored in cookies for 30 days so they survive
    // across browser sessions, page refreshes, and email client redirects.

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Peer referral: ?ref=CODE — capture into cookie + session
        if ($request->has('ref') && !$request->cookie('referral_code')) {
            $code = $request->query('ref');
            session(['referral_code' => $code]);
            Cookie::queue('referral_code', $code, 60 * 24 * 30); // 30 days
        } elseif (!session('referral_code') && $request->cookie('referral_code')) {
            // Restore from cookie into session if session was lost
            session(['referral_code' => $request->cookie('referral_code')]);
        }

        // Influencer referral: ?via=slug
        if ($request->has('via') && !$request->cookie('influencer_slug')) {
            $slug   = $request->query('via');
            $exists = DB::table('influencers')->where('slug', $slug)->where('status', 'active')->exists();
            if ($exists) {
                session(['influencer_slug' => $slug]);
                Cookie::queue('influencer_slug', $slug, 60 * 24 * 30); // 30 days
            }
        } elseif (!session('influencer_slug') && $request->cookie('influencer_slug')) {
            session(['influencer_slug' => $request->cookie('influencer_slug')]);
        }

        return $response;
    }
}
