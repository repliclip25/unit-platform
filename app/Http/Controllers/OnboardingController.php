<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerOnboardingService;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
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
            return redirect()->route('hire.ava.assignment')
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

        return redirect()->route('hire.ava.assignment')
            ->with('success', 'Sample data loaded — your worker can now run a full live test.');
    }

    public function publicIntentMeta(?string $slug): ?array { return $this->intentMeta($slug); }

    public function showAvaOnShift(Request $request)
    {
        $userId     = auth()->id();
        $deployment = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->orderByDesc('created_at')
            ->first();

        $credential = $deployment
            ? DB::table('user_gmail_credentials')->where('id', $deployment->credential_id)->first()
            : null;

        $watchTxId = $request->query('watch') ?? session('ava_onshift_tx');

        $user      = auth()->user();
        $firstName = explode(' ', trim($user->name))[0];

        // Today's summary stats for the success card
        $todayStart = now()->startOfDay()->toDateTimeString();
        $todayStats = [
            'detected' => DB::table('transactions')->where('user_id', $userId)->where('created_at', '>=', $todayStart)->count(),
            'drafted'  => DB::table('transactions')->where('user_id', $userId)->where('created_at', '>=', $todayStart)->whereIn('status', ['draft_ready','approved','sent'])->count(),
            'awaiting' => DB::table('transactions')->where('user_id', $userId)->where('created_at', '>=', $todayStart)->where('status', 'draft_ready')->count(),
        ];

        return view('onboarding.ava.step-5-onshift', compact('deployment', 'credential', 'watchTxId', 'firstName', 'todayStats'));
    }

    public function runAvaOnShift(Request $request)
    {
        $userId     = auth()->id();
        $deployment = DB::table('worker_deployments')
            ->where('user_id', $userId)->where('worker_slug', 'ava')
            ->orderByDesc('created_at')->first();

        if (!$deployment) {
            return redirect()->route('hire.ava.onshift')->with('error', 'No AVA deployment found.');
        }

        $credential = DB::table('user_gmail_credentials')
            ->where('id', $deployment->credential_id)->first();

        if (!$credential) {
            return redirect()->route('hire.ava.onshift')->with('error', 'No Gmail account connected.');
        }

        // Build scenario from user's real memory (first asset + contact), fallback to generic
        $firstAsset   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('created_at')->first();
        $firstContact = $firstAsset
            ? DB::table('contacts')->where('user_id', $userId)->where('client_id', $firstAsset->client_id)->whereNull('deleted_at')->first()
            : DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('created_at')->first();
        $firstClient  = $firstAsset
            ? DB::table('clients')->where('id', $firstAsset->client_id)->first()
            : null;

        $assetName    = $firstAsset?->name    ?? 'yourdomain.com';
        $assetType    = $firstAsset?->type    ?? 'Domain';
        $contactName  = $firstContact?->name  ?? auth()->user()->name;
        $contactEmail = $firstContact?->email ?? auth()->user()->email;
        $clientName   = $firstClient?->name   ?? null;

        // Upsert scenario with real data
        $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $deployment->id)->first();
        $scenarioData = [
            'deployment_id'     => $deployment->id,
            'user_id'           => $userId,
            'scenario_title'    => ($assetType ?? 'Renewal') . ' Demo',
            'sender_name'       => 'Renewal Notices',
            'sender_email'      => 'renewals@notices.example.com',
            'asset_name'        => $assetName,
            'asset_type'        => ucfirst($assetType),
            'contact_name'      => $contactName,
            'renewal_price'     => '$0.00',
            'days_until_expiry' => 14,
            'updated_at'        => now(),
        ];
        if (!$scenario) {
            DB::table('fast_track_scenarios')->insert(array_merge($scenarioData, ['created_at' => now()]));
            $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $deployment->id)->first();
        } else {
            DB::table('fast_track_scenarios')->where('id', $scenario->id)->update($scenarioData);
            $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $deployment->id)->first();
        }

        $expiryDate  = now()->addDays($scenario->days_until_expiry)->format('F j, Y');
        $sampleEmail = implode("\n", [
            "From: {$scenario->sender_name} <{$scenario->sender_email}>",
            "To: {$credential->gmail_address}",
            "Subject: {$scenario->asset_type} Renewal Notice — {$scenario->asset_name} expires in {$scenario->days_until_expiry} days",
            "",
            "Dear {$scenario->contact_name},",
            "",
            "This is a reminder that your {$scenario->asset_type} {$scenario->asset_name} is due for renewal on {$expiryDate}.",
            "",
            "{$scenario->asset_type}: {$scenario->asset_name}",
            "Renewal Date: {$expiryDate}",
            "Renewal Price: {$scenario->renewal_price}",
            "Contact Email: " . $contactEmail,
            "",
            "Please renew before it expires.",
            "",
            "Thank you,",
            $scenario->sender_name,
        ]);

        $txService = app(\App\Platform\Services\TransactionService::class);
        $tx = $txService->create('ava-renewal-coordinator', [
            'source'             => 'fast_track_test',
            'fast_track'         => true,
            'onboarding_demo'    => true,
            'user_id'            => $userId,
            'deployment_id'      => $deployment->id,
            'credential_id'      => $credential->id,
            'fast_track_from'    => "{$scenario->sender_name} <{$scenario->sender_email}>",
            'fast_track_subject' => "{$scenario->asset_type} Renewal Notice — {$scenario->asset_name} expires in {$scenario->days_until_expiry} days",
            'fast_track_body'    => $sampleEmail,
        ]);

        $contract     = WorkerRegistry::resolveActive($deployment->worker_slug);
        $fastTrackJob = $contract->fastTrackJobClass() ?: $contract->ingestJobClass();
        $fastTrackJob::dispatch($tx->tx_id)->onQueue($txService->queueForTx($tx));

        return redirect()->route('hire.ava.onshift', ['watch' => $tx->tx_id]);
    }

    public function showAvaAssignment()
    {
        $userId   = auth()->id();
        $contract = \App\Platform\Services\WorkerRegistry::resolve('ava');

        $persona    = DB::table('users')->where('id', $userId)->value('persona');
        $allPersonas = $contract?->personas() ?? [];
        $personaDef  = ($persona && isset($allPersonas[$persona])) ? $allPersonas[$persona] : null;

        $mc = $personaDef['memory_copy'] ?? [
            'client_noun'        => 'client',
            'client_noun_plural' => 'clients',
            'asset_noun'         => 'asset',
            'example_client'     => 'Acme Corp',
            'example_asset'      => 'Service Agreement',
        ];
        $assetTypeOptions = $personaDef['asset_types'] ?? ['other' => 'Other'];

        $clientCount  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->count();
        $contactCount = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->count();
        $assetCount   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->count();

        $recentClients = DB::table('clients')
            ->where('user_id', $userId)->whereNull('deleted_at')
            ->orderByDesc('created_at')->limit(5)->get();

        $platformTemplates = DB::table('email_templates')
            ->where('user_id', $userId)->orWhereNull('user_id')
            ->get();

        return view('onboarding.ava.step-4-assignment', compact(
            'mc', 'assetTypeOptions', 'clientCount', 'contactCount',
            'assetCount', 'recentClients', 'platformTemplates', 'persona', 'personaDef'
        ));
    }

    public function quickAddAvaMemory(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'client_name'   => 'required|string|max:120',
            'contact_name'  => 'required|string|max:120',
            'contact_email' => 'required|email|max:200',
            'asset_name'    => 'required|string|max:200',
            'asset_type'    => 'nullable|string|max:60',
            'renewal_date'  => 'nullable|date',
        ]);

        $userId = auth()->id();

        $clientId = DB::table('clients')
            ->where('user_id', $userId)->whereNull('deleted_at')
            ->where('name', $request->client_name)->value('id');

        if (!$clientId) {
            $clientId = DB::table('clients')->insertGetId([
                'user_id' => $userId, 'name' => $request->client_name,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $contactExists = DB::table('contacts')
            ->where('user_id', $userId)->where('client_id', $clientId)
            ->where('email', $request->contact_email)->whereNull('deleted_at')->exists();

        if (!$contactExists) {
            DB::table('contacts')->insert([
                'user_id' => $userId, 'client_id' => $clientId,
                'name' => $request->contact_name, 'email' => $request->contact_email,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('assets')->insert([
            'user_id' => $userId, 'client_id' => $clientId,
            'name' => $request->asset_name,
            'type' => $request->asset_type ?: 'other',
            'renewal_date' => $request->renewal_date ?: null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return redirect()->route('hire.ava.assignment')
            ->with('quick_add_success', $request->client_name);
    }

    public function advanceAvaMemory()
    {
        $wos = WorkerOnboardingService::load(auth()->id());
        if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'memory');
        return redirect()->route('hire.ava.onshift');
    }

    public function showAvaOrientation()
    {
        $contract = \App\Platform\Services\WorkerRegistry::resolve('ava');
        $personas = $contract?->personas() ?? [];
        $current  = \Illuminate\Support\Facades\DB::table('users')->where('id', auth()->id())->value('persona');
        return view('onboarding.ava.step-3-orientation', compact('personas', 'current'));
    }

    public function saveAvaPersona(\Illuminate\Http\Request $request)
    {
        $persona  = $request->input('persona');
        $contract = \App\Platform\Services\WorkerRegistry::resolve('ava');
        $allowed  = array_keys($contract?->personas() ?? []);

        if (!in_array($persona, $allowed)) {
            return back()->withErrors(['persona' => 'Please select your business type to continue.']);
        }

        $userId = auth()->id();

        // Find or create the AVA deployment for this user
        $deployment = DB::table('worker_deployments')
            ->where('user_id', $userId)->where('worker_slug', 'ava')
            ->orderByDesc('created_at')->first();

        if (!$deployment) {
            // Auto-provision: find Gmail credential connected during Step 2
            $credential = DB::table('user_gmail_credentials')
                ->where('user_id', $userId)->orderByDesc('created_at')->first();

            $depId = DB::table('worker_deployments')->insertGetId([
                'user_id'       => $userId,
                'worker_slug'   => 'ava',
                'name'          => 'AVA — Renewal Coordinator',
                'status'        => 'active',
                'credential_id' => $credential?->id,
                'persona'       => $persona,
                'config'        => json_encode([]),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // Provision trial billing row
            DB::table('deployment_billing')->insert([
                'user_id'                   => $userId,
                'deployment_id'             => $depId,
                'worker_slug'               => 'ava',
                'status'                    => 'trial',
                'trial_transactions_used'   => 0,
                'trial_transactions_limit'  => 10,
                'created_at'                => now(),
                'updated_at'                => now(),
            ]);
        } else {
            $depId = $deployment->id;
            DB::table('worker_deployments')->where('id', $depId)->update(['persona' => $persona]);
        }

        $this->seedPersonaRules($depId, $userId, $contract, $persona);

        DB::table('users')->where('id', $userId)->update(['persona' => $persona]);

        $wos = WorkerOnboardingService::load($userId);
        if ($wos) WorkerOnboardingService::advanceStep($wos->id, 'persona');

        return redirect()->route('hire.ava.assignment');
    }

    public function saveOrientation(\Illuminate\Http\Request $request)
    {
        $fields = ['business_basics', 'customers', 'renewal_process', 'communication_style', 'knowledge_resources', 'faq_objections'];
        $data   = array_filter($request->only($fields), fn($v) => !is_null($v) && $v !== '');

        // Store in session so Step 4 can also read it before deployment exists
        session(['ava_orientation' => $data]);

        // Persist to deployment if one already exists for this user
        $deployment = \Illuminate\Support\Facades\DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->where('worker_slug', 'ava')
            ->orderByDesc('created_at')
            ->first();

        if ($deployment) {
            \Illuminate\Support\Facades\DB::table('worker_deployments')
                ->where('id', $deployment->id)
                ->update(['orientation_data' => json_encode($data)]);
        }

        return redirect()->route('hire.ava.assignment');
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
