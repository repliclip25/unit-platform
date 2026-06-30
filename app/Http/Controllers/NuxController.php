<?php

namespace App\Http\Controllers;

use App\Workers\NUX\Jobs\ReadPostJob;
use App\Workers\NUX\Services\LinkedInService;
use App\Workers\NUX\Services\XService;
use App\Platform\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NuxController extends Controller
{
    // ── LinkedIn ─────────────────────────────────────────────────────────────

    public function linkedinAuthorize(LinkedInService $linkedin): RedirectResponse
    {
        $state = Str::random(32);
        session(['nux_linkedin_state' => $state]);

        return redirect($linkedin->authorizationUrl($state));
    }

    public function linkedinCallback(Request $request, LinkedInService $linkedin): RedirectResponse
    {
        $state = $request->query('state');
        $code  = $request->query('code');

        if ($request->query('error') || $state !== session('nux_linkedin_state')) {
            return redirect()->route('onboarding.step', 'credential')
                ->with('error', 'LinkedIn authorization failed. Please try again.');
        }

        $userId = auth()->id();
        $depId  = session('onboarding_deployment_id');

        $success = $linkedin->handleCallback($userId, $code, $depId);

        if (!$success) {
            return redirect()->route('onboarding.step', 'credential')
                ->with('error', 'LinkedIn connection failed. Please try again.');
        }

        session()->forget('nux_linkedin_state');

        return redirect()->route('onboarding.step', 'credential')
            ->with('success', 'LinkedIn connected successfully.');
    }

    // ── X (Twitter) ──────────────────────────────────────────────────────────

    public function xAuthorize(Request $request, XService $x): RedirectResponse
    {
        $state  = Str::random(32);
        $userId = auth()->id();

        session(['nux_x_state' => $state]);

        return redirect($x->authorizationUrl($userId, $state));
    }

    public function xCallback(Request $request, XService $x): RedirectResponse
    {
        $state = $request->query('state');
        $code  = $request->query('code');

        if ($request->query('error') || $state !== session('nux_x_state')) {
            return redirect()->route('onboarding.step', 'credential')
                ->with('error', 'X authorization failed. Please try again.');
        }

        $success = $x->handleCallback(auth()->id(), $code, $state);

        if (!$success) {
            return redirect()->route('onboarding.step', 'credential')
                ->with('error', 'X connection failed. Please try again.');
        }

        session()->forget('nux_x_state');

        return redirect()->route('onboarding.step', 'credential')
            ->with('success', 'X connected successfully.');
    }

    // ── Disconnect ───────────────────────────────────────────────────────────

    public function disconnectLinkedIn(LinkedInService $linkedin): RedirectResponse
    {
        $linkedin->disconnect(auth()->id());
        return back()->with('success', 'LinkedIn disconnected.');
    }

    public function disconnectX(XService $x): RedirectResponse
    {
        $x->disconnect(auth()->id());
        return back()->with('success', 'X disconnected.');
    }

    // ── Idea submission ──────────────────────────────────────────────────────

    public function submitIdea(Request $request, int $id, TransactionService $txService): JsonResponse
    {
        $request->validate([
            'idea_text'         => 'required|string|min:10|max:5000',
            'target_channels'   => 'required|array|min:1',
            'target_channels.*' => 'string|in:linkedin,x',
        ]);

        $userId = auth()->id();

        $dep = DB::table('worker_deployments')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$dep) {
            return response()->json(['error' => 'Deployment not found.'], 404);
        }

        $tx = $txService->create('nux', [
            'user_id'         => $userId,
            'deployment_id'   => $id,
            'source'          => 'idea',
            'idea_text'       => $request->input('idea_text'),
            'target_channels' => $request->input('target_channels'),
            'author'          => $request->user()->name,
        ]);

        ReadPostJob::dispatch($tx->tx_id)->onQueue("nux-{$id}");

        return response()->json([
            'success' => true,
            'tx_id'   => $tx->tx_id,
            'message' => 'Idea submitted — NUX will draft your post shortly.',
        ]);
    }

    // ── Performance feedback ─────────────────────────────────────────────────

    public function submitPerformance(Request $request, int $registerId): JsonResponse
    {
        $request->validate([
            'tracking_day'     => 'required|integer|in:7,14,30,90',
            'impressions'      => 'nullable|integer|min:0',
            'likes'            => 'nullable|integer|min:0',
            'comments'         => 'nullable|integer|min:0',
            'shares'           => 'nullable|integer|min:0',
            'clicks'           => 'nullable|integer|min:0',
            'reach'            => 'nullable|integer|min:0',
            'platform_post_url'=> 'nullable|url|max:500',
            'notes'            => 'nullable|string|max:2000',
        ]);

        $userId = auth()->id();

        $reg = DB::table('nux_register')
            ->where('id', $registerId)
            ->where('user_id', $userId)
            ->first();

        if (!$reg) {
            return response()->json(['error' => 'Register entry not found.'], 404);
        }

        $log = DB::table('nux_performance_log')
            ->where('nux_register_id', $registerId)
            ->where('tracking_day', $request->input('tracking_day'))
            ->where('user_id', $userId)
            ->first();

        if (!$log) {
            return response()->json(['error' => 'No tracking slot found for this day.'], 404);
        }

        if ($log->submitted_at !== null) {
            return response()->json(['error' => 'Feedback already submitted for this period.'], 409);
        }

        DB::table('nux_performance_log')->where('id', $log->id)->update([
            'impressions'       => $request->input('impressions'),
            'likes'             => $request->input('likes'),
            'comments'          => $request->input('comments'),
            'shares'            => $request->input('shares'),
            'clicks'            => $request->input('clicks'),
            'reach'             => $request->input('reach'),
            'platform_post_url' => $request->input('platform_post_url'),
            'notes'             => $request->input('notes'),
            'submitted_at'      => now(),
            'updated_at'        => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Performance data saved. NUX will use this to improve future posts.',
        ]);
    }
}
