<?php

namespace App\Http\Controllers;

use App\Platform\Services\UnitNotifier;
use App\Platform\Services\WorkerRegistry;
use App\Workers\AVA\Services\GmailWatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    // Steps in order
    private const STEPS = [1, 2, 3, 4, 5];

    public function index()
    {
        if ($this->isComplete()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('onboarding.step', $this->currentStep());
    }

    public function show(int $step)
    {
        if ($this->isComplete()) {
            return redirect()->route('dashboard');
        }

        // Don't allow skipping ahead beyond the furthest reached step
        $reached = $this->currentStep();
        if ($step > $reached) {
            return redirect()->route('onboarding.step', $reached);
        }

        $data = $this->stepData($step);
        return view("onboarding.step-{$step}", array_merge($data, ['step' => $step]));
    }

    // ── Step handlers ──────────────────────────────────────────────────────

    // Step 1: Welcome — just advance
    public function step1(Request $request)
    {
        $this->advanceTo(2);
        return redirect()->route('onboarding.step', 2);
    }

    // Step 2: Pick worker → store slug, create deployment
    public function step2(Request $request)
    {
        $request->validate(['worker_slug' => 'required|string']);

        $slug   = $request->worker_slug;
        $worker = DB::table('workers')->where('slug', $slug)->firstOrFail();

        // Create deployment immediately so Gmail callback can auto-watch
        $depId = DB::table('worker_deployments')->insertGetId([
            'user_id'     => auth()->id(),
            'worker_slug' => $slug,
            'name'        => $worker->name,
            'status'      => 'active',
            'config'      => json_encode(['capture_scope' => 'All incoming emails', 'capture_keywords' => []]),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Copy platform default rules
        $platformRules = DB::table('ava_rules')->whereNull('user_id')->whereNull('deployment_id')->get();
        foreach ($platformRules as $rule) {
            DB::table('ava_rules')->insert([
                'user_id'           => auth()->id(),
                'deployment_id'     => $depId,
                'rule_id'           => $rule->rule_id,
                'condition'         => $rule->condition,
                'priority'          => $rule->priority,
                'action'            => $rule->action,
                'approval_required' => $rule->approval_required,
                'notes'             => $rule->notes,
                'active'            => true,
                'is_platform'       => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // Create trial billing record
        $pricing = DB::table('worker_pricing')->where('worker_slug', $slug)->first();
        DB::table('deployment_billing')->insert([
            'user_id'                  => auth()->id(),
            'deployment_id'            => $depId,
            'worker_slug'              => $slug,
            'status'                   => 'trial',
            'trial_transactions_used'  => 0,
            'trial_transactions_limit' => $pricing?->free_transactions ?? 10,
            'trial_ends_at'            => now()->addDays(14),
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        session(['onboarding_deployment_id' => $depId, 'onboarding_worker_slug' => $slug]);

        UnitNotifier::workerDeployed($depId);

        $this->advanceTo(3);
        return redirect()->route('onboarding.step', 3);
    }

    // Step 3: Connect Gmail — redirects to OAuth, handled by callback
    public function step3(Request $request)
    {
        // Mark that Gmail callback should return to onboarding step 4
        session(['onboarding_gmail_return' => true]);
        return redirect()->route('ava.gmail.authorize');
    }

    // Step 3 skip
    public function step3Skip()
    {
        $this->advanceTo(4);
        return redirect()->route('onboarding.step', 4);
    }

    // Step 4: Memory — handled via existing memory import routes, just advance
    public function step4(Request $request)
    {
        $this->advanceTo(5);
        return redirect()->route('onboarding.step', 5);
    }

    // Step 5: Fast track — dispatch and redirect to completion
    public function step5(Request $request)
    {
        $depId = session('onboarding_deployment_id');
        $dep   = $depId ? DB::table('worker_deployments')->where('id', $depId)->first() : null;

        if (!$dep) {
            return redirect()->route('onboarding.step', 2);
        }

        // Build fast-track payload using sample email
        $sampleEmail = $request->input('sample_email',
            "Subject: Domain Renewal Notice — example.com\n\n"
            . "Hi,\n\nThis is a reminder that your domain example.com is due for renewal in 30 days. "
            . "Please renew at your earliest convenience to avoid any service interruption.\n\n"
            . "Best regards,\nDomain Services Team"
        );

        $txId = 'onb-' . auth()->id() . '-' . now()->timestamp;

        DB::table('transactions')->insert([
            'tx_id'         => $txId,
            'user_id'       => auth()->id(),
            'deployment_id' => $dep->id,
            'worker_slug'   => $dep->worker_slug,
            'status'        => 'received',
            'raw_input'     => json_encode([
                'source'     => 'fast_track',
                'fast_track' => true,
                'raw_email'  => $sampleEmail,
            ]),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        \App\Workers\AVA\Jobs\ReadEmailJob::dispatch($txId)->onQueue("{$dep->worker_slug}-{$dep->id}");

        session(['onboarding_fast_track_tx' => $txId]);

        $this->advanceTo(5); // Stay on 5 until user clicks finish
        return redirect()->route('onboarding.step', 5)->with('fast_track_running', $txId);
    }

    // Finish onboarding
    public function complete()
    {
        session(['onboarding_complete' => true]);
        return redirect()->route('dashboard')->with('success', 'You\'re all set! Your workspace is ready.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function currentStep(): int
    {
        return session('onboarding_step', 1);
    }

    private function advanceTo(int $step): void
    {
        if ($step > $this->currentStep()) {
            session(['onboarding_step' => $step]);
        }
    }

    private function isComplete(): bool
    {
        return session('onboarding_complete', false);
    }

    private function stepData(int $step): array
    {
        $slug     = session('onboarding_worker_slug', 'ava');
        $contract = WorkerRegistry::resolve($slug);

        return match ($step) {
            1 => ['userName' => auth()->user()->name],
            2 => [
                'workers'   => DB::table('workers')->get(),
                'contracts' => collect(WorkerRegistry::all())->keyBy(fn($c) => $c->identity()['slug']),
            ],
            3 => [
                'contract'       => $contract,
                'credentialInfo' => $contract?->credential() ?? [],
                'hasCredential'  => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists(),
                'credentialEmail'=> DB::table('user_gmail_credentials')->where('user_id', auth()->id())->value('gmail_address'),
            ],
            4 => [
                'contract'     => $contract,
                'trainSchema'  => $contract?->trainSchema() ?? [],
                'depId'        => session('onboarding_deployment_id'),
                'clientCount'  => DB::table('clients')->where('user_id', auth()->id())->count(),
                'contactCount' => DB::table('contacts')->where('user_id', auth()->id())->count(),
                'assetCount'   => DB::table('assets')->where('user_id', auth()->id())->count(),
            ],
            5 => [
                'contract'     => $contract,
                'depId'        => session('onboarding_deployment_id'),
                'txId'         => session('onboarding_fast_track_tx'),
                'hasCredential'=> DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists(),
            ],
            default => [],
        };
    }
}
