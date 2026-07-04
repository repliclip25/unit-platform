<?php

namespace App\Http\Controllers;

use App\Platform\Services\PlatformVerificationService;
use App\Platform\Services\UnitNotifier;
use App\Platform\Services\WorkerOnboardingService;
use App\Platform\Services\WorkerRegistry;
use App\Platform\Services\PlatformDefaults;
use App\Platform\Services\Gmail\GmailWatchService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Entry point — decides where to send the user.
     *
     * Priority order:
     *   1. Active session in progress → resume at current_step
     *   2. Intent worker in session   → show welcome then start session
     *   3. No intent                  → show worker picker
     */
    public function index()
    {
        $user = auth()->user();

        // If a worker session is already in progress, resume it
        $wos = WorkerOnboardingService::load($user->id);
        if ($wos) {
            return redirect()->route('onboarding.step', $wos->current_step);
        }

        // No session — route to worker picker or welcome
        $intentSlug = session('onboarding_intent_worker');
        if ($intentSlug) {
            return redirect()->route('onboarding.step', 'welcome');
        }

        return redirect()->route('onboarding.step', 'select-worker');
    }

    // ── Display a named step ──────────────────────────────────────────────────

    public function showStep(string $name)
    {
        $user = auth()->user();

        // Pre-session steps — no WOS required
        if (in_array($name, ['select-worker', 'welcome'])) {
            return $this->renderPreSessionStep($name);
        }

        // All other steps require an active WOS
        $wos = WorkerOnboardingService::load($user->id);

        if (!$wos) {
            return redirect()->route('onboarding');
        }

        if ($wos->status === WorkerOnboardingService::STATUS_COMPLETED) {
            return redirect()->route('dashboard');
        }

        // Auto-advance a platform step that's already been satisfied
        if (str_starts_with($name, 'verify-') && $this->isPlatformStepSatisfied($name)) {
            WorkerOnboardingService::advanceStepByName($user->id, $name);
            $wos = WorkerOnboardingService::load($user->id);
            return redirect()->route('onboarding.step', $wos->current_step);
        }

        // Enforce forward-only navigation
        $seq          = WorkerOnboardingService::getSequence($wos);
        $currentIdx   = WorkerOnboardingService::stepIndex($wos, $wos->current_step);
        $requestedIdx = WorkerOnboardingService::stepIndex($wos, $name);

        if ($requestedIdx > $currentIdx) {
            return redirect()->route('onboarding.step', $wos->current_step);
        }

        $data = $this->getStepData($name, $wos);
        $view = $this->viewName($name);

        return view($view, array_merge($data, [
            'stepName'  => $name,
            'sequence'  => $seq,
            'stepIndex' => $requestedIdx,
            'wos'       => $wos,
        ]));
    }

    // ── Handle a named step POST ──────────────────────────────────────────────

    public function handleStep(Request $request, string $name)
    {
        return match ($name) {
            'welcome'       => $this->handleWelcome($request),
            'select-worker' => $this->handleSelectWorker($request),
            'credential'    => $this->handleCredential($request),
            'gmail'         => $this->handleGmail($request),
            'memory'        => $this->handleMemory($request),
            'fast-track'    => $this->handleFastTrack($request),
            default         => redirect()->route('onboarding'),
        };
    }

    // ── Step: verify-email (read-only — driven by email link) ─────────────────

    // Legacy route kept for the custom verify screen (pre-session)
    public function verifyEmailScreen(Request $request)
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('onboarding');
        }
        return view('onboarding.verify-email', [
            'editMode' => $request->query('edit') === '1',
        ]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        $user     = auth()->user();
        $newEmail = strtolower(trim($request->email));

        $user->forceFill([
            'email'             => $newEmail,
            'email_verified_at' => null,
        ])->save();

        event(new Registered($user));

        return redirect()->route('onboarding.verify')
            ->with('email_updated', $newEmail);
    }

    // ── Complete / Skip ───────────────────────────────────────────────────────

    public function complete()
    {
        $user = auth()->user();
        $wos  = WorkerOnboardingService::load($user->id);
        if ($wos) WorkerOnboardingService::complete($wos->id);

        $user->forceFill(['onboarding_completed_at' => now()])->save();
        return redirect()->route('dashboard')->with('success', 'You\'re all set! Your workspace is ready.');
    }

    public function skip()
    {
        $user = auth()->user();
        $wos  = WorkerOnboardingService::load($user->id);
        if ($wos) WorkerOnboardingService::skip($wos->id);

        $user->forceFill(['onboarding_skipped' => true])->save();
        return redirect()->route('dashboard');
    }

    // ── Seed sample memory ────────────────────────────────────────────────────

    public function seedMemory()
    {
        $userId = auth()->id();

        // Check if sample data was already seeded
        $alreadySeeded = DB::table('clients')
            ->where('user_id', $userId)
            ->where('name', 'Acme Corp (Sample)')
            ->exists();

        if ($alreadySeeded) {
            return redirect()->route('onboarding.step', 'memory')
                ->with('success', 'Sample data is already loaded.');
        }

        $hasData = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->exists()
                || DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->exists()
                || DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->exists();

        if (!$hasData) {
            $clientId = DB::table('clients')->insertGetId([
                'user_id'    => $userId,
                'name'       => 'Acme Corp (Sample)',
                'industry'   => 'Technology',
                'notes'      => 'Sample client — replace with your real clients',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('contacts')->insert([
                ['user_id' => $userId, 'client_id' => $clientId, 'name' => 'Jane Smith', 'email' => 'jane@acmecorp.example', 'role' => 'Billing Contact', 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('assets')->insert([
                ['user_id' => $userId, 'client_id' => $clientId, 'type' => 'domain',  'name' => 'acmecorp.example',    'renewal_date' => now()->addDays(28)->toDateString(), 'notes' => 'Sample domain',  'created_at' => now(), 'updated_at' => now()],
                ['user_id' => $userId, 'client_id' => $clientId, 'type' => 'ssl',     'name' => 'acmecorp.example SSL','renewal_date' => now()->addDays(12)->toDateString(), 'notes' => 'Sample SSL cert','created_at' => now(), 'updated_at' => now()],
                ['user_id' => $userId, 'client_id' => $clientId, 'type' => 'hosting', 'name' => 'Acme Hosting Plan',   'renewal_date' => now()->addDays(5)->toDateString(),  'notes' => 'Sample hosting', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        return redirect()->route('onboarding.step', 'memory')
            ->with('success', 'Sample data loaded — your worker can now run a full live test.');
    }

    // ── Private step handlers ─────────────────────────────────────────────────

    private function handleWelcome(Request $request)
    {
        $intentSlug = session('onboarding_intent_worker');
        if (!$intentSlug) {
            return redirect()->route('onboarding.step', 'select-worker');
        }
        return $this->bootSessionAndDeploy(auth()->id(), $intentSlug);
    }

    private function handleSelectWorker(Request $request)
    {
        $request->validate(['worker_slug' => 'required|string']);
        $slug = $request->worker_slug;

        // Store intent for future page loads
        session(['onboarding_intent_worker' => $slug]);

        return $this->bootSessionAndDeploy(auth()->id(), $slug);
    }

    private function handleCredential(Request $request)
    {
        $wos        = WorkerOnboardingService::load(auth()->id());
        $workerSlug = $wos?->worker_slug ?? session('onboarding_intent_worker', 'ava');
        $contract   = \App\Platform\Services\WorkerRegistry::resolveActive($workerSlug);
        $cred       = $contract->credential();
        $isMulti    = isset($cred[0]); // multi-slot workers return [{key,...}, ...]; single-slot returns flat array

        if ($request->input('skip')) {
            if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'credential', ['skipped' => true]);
            $nextStep = $isMulti ? 'gmail' : 'memory';
            return redirect()->route('onboarding.step', $nextStep);
        }

        if ($isMulti) {
            // Multi-credential worker — redirect to the specific slot's authorize route
            $slotKey = $request->input('slot'); // 'linkedin' | 'x'
            $slot    = collect($cred)->firstWhere('key', $slotKey);
            if ($slot && isset($slot['authorize_route'])) {
                return redirect()->route($slot['authorize_route']);
            }
            // No valid slot — just advance past credential step
            if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'credential');
            return redirect()->route('onboarding.step', 'gmail');
        }

        // Single-credential worker (AVA) — existing Gmail OAuth flow
        session(['onboarding_gmail_return' => true]);
        $authorizeRoute = $cred['authorize_route'] ?? 'ava.gmail.authorize';
        return redirect()->route($authorizeRoute);
    }

    private function handleGmail(Request $request)
    {
        // Gmail delivery inbox step — used by multi-credential workers like NUX
        $wos = WorkerOnboardingService::load(auth()->id());

        if ($request->input('skip')) {
            if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'gmail', ['skipped' => true]);
            return redirect()->route('onboarding.step', 'memory');
        }

        session(['onboarding_gmail_return' => true]);

        $workerSlug     = $wos?->worker_slug ?? session('onboarding_intent_worker', 'nux');
        $contract       = \App\Platform\Services\WorkerRegistry::resolveActive($workerSlug);
        $cred           = $contract->credential();
        $gmailSlot      = collect($cred)->firstWhere('key', 'inbox');
        $authorizeRoute = $gmailSlot['authorize_route'] ?? 'nux.gmail.authorize';

        return redirect()->route($authorizeRoute);
    }

    private function handleMemory(Request $request)
    {
        $wos = WorkerOnboardingService::load(auth()->id());
        if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'memory');
        return redirect()->route('onboarding.step', 'fast-track');
    }

    private function handleFastTrack(Request $request)
    {
        $wos   = WorkerOnboardingService::load(auth()->id());
        $depId = $wos?->deployment_id ?? session('onboarding_deployment_id');
        $dep   = $depId ? DB::table('worker_deployments')->where('id', $depId)->first() : null;

        if (!$dep) {
            return redirect()->route('onboarding.step', 'select-worker');
        }

        // Resolve contract first — was previously used before being assigned (fatal bug)
        $contract = WorkerRegistry::resolveActive($dep->worker_slug);

        if (WorkerRegistry::isNull($contract)) {
            return redirect()->route('onboarding.step', 'fast-track')
                ->with('fast_track_error', 'Worker is unavailable. Please contact support.');
        }

        // Gate: Gmail must be connected before we can run the pipeline
        $hasGmail = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists();
        if (!$hasGmail) {
            return redirect()->route('onboarding.step', 'fast-track')
                ->with('fast_track_error', 'Connect your Gmail inbox first — Fast Track needs it to read and draft emails.');
        }

        $txId    = 'onb-' . auth()->id() . '-' . now()->timestamp;
        $payload = array_merge($contract->fastTrack(), [
            'fast_track' => true,
            '_queue'     => 'fast-track',
        ]);

        DB::table('transactions')->insert([
            'tx_id'         => $txId,
            'user_id'       => auth()->id(),
            'deployment_id' => $dep->id,
            'worker_slug'   => $dep->worker_slug,
            'status'        => 'received',
            'raw_input'     => json_encode($payload),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $ingestJob = $contract->ingestJobClass();
        $ingestJob::dispatch($txId)->onQueue('fast-track');

        session(['onboarding_fast_track_tx' => $txId]);

        return redirect()->route('onboarding.step', 'fast-track')
            ->with('fast_track_running', $txId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Create the deployment + billing + default rules, start WOS, redirect to first step.
     * Idempotent — reuses existing deployment if already created.
     */
    private function bootSessionAndDeploy(int $userId, string $workerSlug)
    {
        // Resolve name from live registry — never from the stale `workers` table
        $contract   = WorkerRegistry::resolve($workerSlug);
        $workerName = $contract->identity()['name'] ?? strtoupper($workerSlug);

        if (WorkerRegistry::isNull($contract)) {
            return redirect()->route('onboarding.step', 'select-worker')
                ->with('error', 'That worker is no longer available.');
        }

        $existing = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', $workerSlug)
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if ($existing) {
            $depId = $existing->id;
        } else {
            $depId = DB::table('worker_deployments')->insertGetId([
                'user_id'     => $userId,
                'worker_slug' => $workerSlug,
                'name'        => $workerName,
                'status'      => 'active',
                'config'      => json_encode(['capture_scope' => 'All incoming emails', 'capture_keywords' => []]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Seed demo memory so fast track has a real match to work with
            $clientId = DB::table('clients')->insertGetId([
                'user_id'         => $userId,
                'name'            => 'Acme Corp',
                'industry'        => 'Technology',
                'role'            => 'Client',
                'preferred_style' => 'Professional',
                'notes'           => 'Demo client — matches the fast track sample email.',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('contacts')->insert([
                'user_id'    => $userId,
                'client_id'  => $clientId,
                'name'       => 'John Smith',
                'role'       => 'IT Manager',
                'email'      => 'john@acmecorp.com',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('assets')->insert([
                'user_id'      => $userId,
                'client_id'    => $clientId,
                'name'         => 'yourdomain.com',
                'type'         => 'Domain',
                'vendor'       => 'Domain Registrar',
                'renewal_date' => now()->addDays(30)->toDateString(),
                'notes'        => 'Demo asset — matches the fast track sample renewal email.',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Copy platform default rules (AVA-specific — each worker manages its own rule tables)
            if ($workerSlug === 'ava') {
                $platformRules = DB::table('ava_rules')
                    ->whereNull('user_id')
                    ->whereNull('deployment_id')
                    ->get();

                foreach ($platformRules as $rule) {
                    DB::table('ava_rules')->insertOrIgnore([
                        'user_id'           => $userId,
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
            }

            DB::table('deployment_billing')->insert([
                'user_id'                  => $userId,
                'deployment_id'            => $depId,
                'worker_slug'              => $workerSlug,
                'status'                   => 'trial',
                'trial_transactions_used'  => 0,
                'trial_transactions_limit' => PlatformDefaults::freeTransactionsFor($workerSlug),
                'trial_ends_at'            => now()->addDays(PlatformDefaults::trialDays()),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            UnitNotifier::workerDeployed($depId);
        }

        // Start/resume WOS with fresh resolved sequence
        $wos = WorkerOnboardingService::resume($userId, $workerSlug);
        WorkerOnboardingService::attachDeployment($wos->id, $depId);

        // Reload to get updated sequence/current_step
        $wos = WorkerOnboardingService::load($userId);

        return redirect()->route('onboarding.step', $wos->current_step);
    }

    private function renderPreSessionStep(string $name)
    {
        return match ($name) {
            'welcome' => view('onboarding.steps.welcome', [
                'stepName'   => 'welcome',
                'sequence'   => [],
                'stepIndex'  => -1,
                'userName'   => auth()->user()->name,
                'intentWorker' => session('onboarding_intent_worker'),
                'intentMeta'   => $this->intentMeta(session('onboarding_intent_worker')),
            ]),
            'select-worker' => (function () {
                $user = auth()->user();

                // If email is verified at the Laravel level but platform_verifications
                // is missing the row (e.g. after a flush), auto-seed it now.
                if ($user->hasVerifiedEmail()) {
                    PlatformVerificationService::markVerified($user->id, 'email', [], 'system');
                }

                // Gate: all required platform verifications must be complete
                if (!PlatformVerificationService::isPlatformReady($user->id)) {
                    return redirect()->route('onboarding.verify')
                        ->with('info', 'Please verify your email before selecting a worker.');
                }
                $contracts = collect(WorkerRegistry::all())->keyBy(fn($c) => $c->identity()['slug']);
                // Build $workers from live contracts — never from the stale `workers` table
                $workers = $contracts->map(function ($contract) {
                    $id = $contract->identity();
                    return (object) [
                        'slug'        => $id['slug']        ?? '',
                        'name'        => $id['name']        ?? $id['slug'],
                        'description' => $id['description'] ?? '',
                    ];
                })->values();
                return view('onboarding.steps.select-worker', [
                    'stepName'  => 'select-worker',
                    'sequence'  => [],
                    'stepIndex' => -1,
                    'workers'   => $workers,
                    'contracts' => $contracts,
                ]);
            })(),
            default => redirect()->route('onboarding'),
        };
    }

    private function getStepData(string $name, object $wos): array
    {
        $slug     = $wos->worker_slug ?? session('onboarding_worker_slug', 'ava');
        $depId    = $wos->deployment_id ?? session('onboarding_deployment_id');
        $contract = WorkerRegistry::resolve($slug);

        return match ($name) {
            'verify-email' => [],

            'credential' => (function () use ($contract) {
                $cred    = $contract?->credential() ?? [];
                $isMulti = isset($cred[0]);

                if ($isMulti) {
                    // Multi-slot: check each non-Gmail slot for completion
                    $userId  = auth()->id();
                    $slots   = collect($cred)->filter(fn($s) => $s['key'] !== 'inbox');
                    $connected = DB::table('nux_oauth_tokens')
                        ->where('user_id', $userId)
                        ->pluck('platform')
                        ->toArray();

                    return [
                        'contract'        => $contract,
                        'credentialInfo'  => $cred,
                        'isMultiCredential'=> true,
                        'slots'           => $slots->values()->all(),
                        'connectedSlots'  => $connected,
                        'hasCredential'   => count($connected) > 0,
                        'credentialEmail' => null,
                    ];
                }

                return [
                    'contract'        => $contract,
                    'credentialInfo'  => $cred,
                    'isMultiCredential'=> false,
                    'slots'           => [],
                    'connectedSlots'  => [],
                    'hasCredential'   => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists(),
                    'credentialEmail' => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->value('gmail_address'),
                ];
            })(),

            'gmail' => [
                'contract'        => $contract,
                'credentialInfo'  => collect($contract?->credential() ?? [])->firstWhere('key', 'inbox') ?? [],
                'isMultiCredential'=> false,
                'slots'           => [],
                'connectedSlots'  => [],
                'hasCredential'   => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists(),
                'credentialEmail' => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->value('gmail_address'),
            ],

            'memory' => (function () use ($depId, $slug) {
                $userId   = auth()->id();
                $clients  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->get();
                $contacts = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->get()->groupBy('client_id');
                $assets   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->get()->groupBy('client_id');

                $sampleClients = $clients->map(function ($client) use ($contacts, $assets) {
                    $client->contacts = $contacts->get($client->id, collect());
                    $client->assets   = $assets->get($client->id, collect());
                    return $client;
                });

                return [
                    'depId'             => $depId,
                    'clientCount'       => $clients->count(),
                    'contactCount'      => $contacts->flatten(1)->count(),
                    'assetCount'        => $assets->flatten(1)->count(),
                    'sampleClients'     => $sampleClients,
                    'platformTemplates' => DB::table('email_templates')->whereNull('user_id')->where('worker_slug', $slug)->get(),
                ];
            })(),

            'fast-track' => [
                'contract'      => $contract,
                'depId'         => $depId,
                'txId'          => session('onboarding_fast_track_tx'),
                'hasCredential' => DB::table('user_gmail_credentials')->where('user_id', auth()->id())->exists(),
                'outcome'       => $contract?->fastTrackOutcome() ?? [],
            ],

            default => [],
        };
    }

    private function viewName(string $stepName): string
    {
        // Platform verification steps share a generic verify view keyed by type
        if (str_starts_with($stepName, 'verify-')) {
            $type = substr($stepName, 7); // strip 'verify-'
            $path = "onboarding.steps.verify-{$type}";
            // Fall back to generic platform-verify view if specific one doesn't exist
            return view()->exists($path) ? $path : 'onboarding.steps.platform-verify';
        }

        return "onboarding.steps.{$stepName}";
    }

    private function isPlatformStepSatisfied(string $stepName): bool
    {
        $type      = substr($stepName, 7); // strip 'verify-'
        $completed = PlatformVerificationService::completedTypes(auth()->id());
        return in_array($type, $completed);
    }

    private function intentMeta(?string $slug): ?array
    {
        if (!$slug) return null;

        $contract = \App\Platform\Services\WorkerRegistry::resolve($slug);
        if (\App\Platform\Services\WorkerRegistry::isNull($contract)) return null;

        $identity = $contract->identity();
        $media    = $contract->media();
        $employee = $contract->employee();
        $steps    = array_column($contract->onboardingSteps(), 'label');

        return [
            'label'        => $identity['name'] ?? $slug,
            'role'         => $employee['title'] ?? $identity['description'] ?? '',
            'introduction' => $employee['introduction'] ?? '',
            'what_i_do'    => $employee['what_i_do'] ?? [],
            'mission'      => $employee['mission'] ?? '',
            'color'        => $media['color'] ?? '#f1d362',
            'steps'        => $steps,
        ];
    }
}
