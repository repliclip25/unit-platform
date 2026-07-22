<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $userId      = auth()->id();
        $referral    = \App\Platform\Services\ReferralService::getStats($userId);
        $referralCode = \App\Platform\Services\ReferralService::ensureCode($userId);
        $referralUrl  = url('/register?ref=' . $referralCode);

        $credits = DB::table('referral_credits as rc')
            ->leftJoin('users as u', 'u.id', '=', 'rc.referee_id')
            ->where('rc.referrer_id', $userId)
            ->orderByDesc('rc.created_at')
            ->limit(20)
            ->select('rc.*', 'u.email as referred_email')
            ->get();

        $shell = \App\Platform\Services\WorkerShellService::build($userId, '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        // These numbers must track ReferralService's actual constants and the
        // real trial default (PlatformDefaults), not be copy-pasted separately
        // — see the "20 free transactions / double the trial" bug this fixed.
        $referralCreditUsd = \App\Platform\Services\ReferralService::REFERRER_CREDIT_USD;
        $refereeBonusTx    = \App\Platform\Services\ReferralService::REFEREE_BONUS_TX;
        $refereeBaseTx     = \App\Platform\Services\PlatformDefaults::freeTransactionsFor('ava');
        $refereeTotalTx    = $refereeBaseTx + $refereeBonusTx;

        return view('referral.index', compact(
            'referral', 'referralCode', 'referralUrl', 'credits',
            'workerCatalog', 'tokenTotal', 'firstName',
            'referralCreditUsd', 'refereeBonusTx', 'refereeBaseTx', 'refereeTotalTx'
        ));
    }

    public function influencerRedirect(string $slug, Request $request)
    {
        $influencer = \App\Platform\Services\InfluencerService::findBySlug($slug);
        if ($influencer) {
            \App\Platform\Services\InfluencerService::trackClick($slug, $request);
        }
        // Redirect to homepage with ?via= so CaptureReferralCode middleware stores it
        return redirect('/?via=' . $slug);
    }

    public function fastTrackSubmit(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'worker_slug' => 'required|string|max:100',
            'source'      => 'nullable|string|max:100',
        ]);

        // Log lead
        DB::table('fast_track_leads')->insertOrIgnore([
            'name'        => $request->name,
            'email'       => $request->email,
            'worker_slug' => $request->worker_slug,
            'source'      => $request->source ?? 'homepage',
            'user_id'     => null,
            'subscribed'  => false,
            'flags'       => json_encode(['type' => 'public_fasttrack']),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'preview' => 'Sample renewal queued for ' . $request->name . '. We\'re sending the full output to ' . $request->email . ' — click the deploy link inside to go live in minutes.',
        ]);
    }
}
