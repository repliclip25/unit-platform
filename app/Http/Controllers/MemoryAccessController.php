<?php

namespace App\Http\Controllers;

use App\Platform\Services\EmailDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MemoryAccessController extends Controller
{
    private const ALLOWED_PERMISSIONS = ['view', 'copy', 'upload', 'modify'];

    public static function generateProfileCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = 'UNIT-' . $suffix;
        } while (DB::table('users')->where('profile_code', $code)->exists());
        return $code;
    }
    private const MEMORY_TABLES       = ['clients', 'contacts', 'assets'];

    // ── My grants overview (outgoing + incoming) ──────────────────────────────

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $outgoing = DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.grantee_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.owner_user_id', $userId)
            ->whereIn('g.status', ['pending', 'accepted'])
            ->select('g.*', 'u.name as grantee_name', 'u.email as grantee_email',
                     'u.profile_code as grantee_code', 'd.name as deployment_name',
                     'd.worker_slug')
            ->orderByDesc('g.created_at')
            ->get()
            ->map(fn($g) => $this->withEventSummary($g));

        $incoming = DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.owner_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.grantee_user_id', $userId)
            ->where('g.status', 'accepted')
            ->select('g.*', 'u.name as owner_name', 'u.email as owner_email',
                     'u.profile_code as owner_code', 'd.name as deployment_name',
                     'd.worker_slug')
            ->orderByDesc('g.accepted_at')
            ->get();

        $myDeployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('status', '!=', 'decommissioned')
            ->select('id', 'name', 'worker_slug')
            ->get();

        $myProfileCode = DB::table('users')->where('id', $userId)->value('profile_code');

        return view('memory.access', compact('outgoing', 'incoming', 'myDeployments', 'myProfileCode'));
    }

    // ── Send an invite ─────────────────────────────────────────────────────────

    public function invite(Request $request)
    {
        $request->validate([
            'lookup'        => 'required|string',
            'deployment_id' => 'required|integer',
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'in:view,copy,upload,modify',
        ]);

        $ownerId      = $request->user()->id;
        $lookup       = trim($request->input('lookup'));
        $deploymentId = (int) $request->input('deployment_id');
        $permissions  = array_values(array_intersect($request->input('permissions'), self::ALLOWED_PERMISSIONS));

        // Verify the deployment belongs to this user
        $deployment = DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $ownerId)
            ->first();

        if (!$deployment) {
            return back()->withErrors(['lookup' => 'Deployment not found or does not belong to you.']);
        }

        // Resolve grantee by profile code or email (must already have a UNIT account)
        $grantee = DB::table('users')
            ->where(function ($q) use ($lookup) {
                $q->where('profile_code', strtoupper($lookup))
                  ->orWhere('email', strtolower($lookup));
            })
            ->first();

        if (!$grantee) {
            return back()->withErrors(['lookup' => 'No UNIT account found for that profile code or email.']);
        }

        if ($grantee->id === $ownerId) {
            return back()->withErrors(['lookup' => 'You cannot invite yourself.']);
        }

        // Check for existing active grant
        $existing = DB::table('memory_access_grants')
            ->where('owner_user_id', $ownerId)
            ->where('grantee_user_id', $grantee->id)
            ->where('deployment_id', $deploymentId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($existing) {
            return back()->withErrors(['lookup' => 'This person already has ' . $existing->status . ' access to this deployment.']);
        }

        $token = Str::random(64);

        DB::table('memory_access_grants')->insert([
            'owner_user_id'   => $ownerId,
            'grantee_user_id' => $grantee->id,
            'deployment_id'   => $deploymentId,
            'permissions'     => json_encode($permissions),
            'status'          => 'pending',
            'invite_token'    => $token,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Send invite email
        try {
            $owner = $request->user();
            $permissionLines = collect($permissions)->map(fn($p) => match($p) {
                'view'   => '• View memory records',
                'copy'   => '• Copy records to their own workspace',
                'upload' => '• Upload new records to your memory',
                'modify' => '• Edit existing records in your memory',
                default  => '• ' . $p,
            })->implode("\n");

            EmailDispatcher::send(
                'memory_access_invite',
                $grantee->email,
                $grantee->name,
                $grantee->id,
                [
                    '{owner_name}'       => $owner->name,
                    '{worker_name}'      => $deployment->name,
                    '{permissions_list}' => $permissionLines,
                    '{accept_url}'       => route('memory.access.accept', $token),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('[MemoryAccess] invite email failed', ['error' => $e->getMessage()]);
        }

        return back()->with('success', "Invitation sent to {$grantee->name}.");
    }

    // ── Accept via email link ─────────────────────────────────────────────────

    public function acceptShow(string $token)
    {
        $grant = DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.owner_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.invite_token', $token)
            ->where('g.status', 'pending')
            ->select('g.*', 'u.name as owner_name', 'd.name as deployment_name', 'd.worker_slug')
            ->first();

        if (!$grant) {
            return redirect()->route('dashboard')->with('error', 'This invitation link is invalid or has already been used.');
        }

        // Must be logged in as the intended grantee
        if (!auth()->check()) {
            return redirect()->route('login')->with('url.intended', route('memory.access.accept', $token));
        }

        if (auth()->id() !== (int) $grant->grantee_user_id) {
            return redirect()->route('dashboard')->with('error', 'This invitation was sent to a different account.');
        }

        return view('memory.accept', compact('grant', 'token'));
    }

    public function acceptStore(Request $request, string $token)
    {
        $userId = $request->user()->id;

        $grant = DB::table('memory_access_grants')
            ->where('invite_token', $token)
            ->where('grantee_user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$grant) {
            return redirect()->route('dashboard')->with('error', 'Invitation not found or already accepted.');
        }

        DB::table('memory_access_grants')
            ->where('id', $grant->id)
            ->update(['status' => 'accepted', 'accepted_at' => now(), 'updated_at' => now()]);

        return redirect()->route('memory.shared', $grant->id)
            ->with('success', 'Invitation accepted — you now have access to this memory.');
    }

    // ── Revoke (owner only) ───────────────────────────────────────────────────

    public function revoke(Request $request, int $grantId)
    {
        $userId = $request->user()->id;

        $affected = DB::table('memory_access_grants')
            ->where('id', $grantId)
            ->where('owner_user_id', $userId)
            ->whereIn('status', ['pending', 'accepted'])
            ->update(['status' => 'revoked', 'revoked_at' => now(), 'updated_at' => now()]);

        if (!$affected) {
            return back()->with('error', 'Grant not found or already revoked.');
        }

        return back()->with('success', 'Access revoked.');
    }

    // ── Grantee: view shared memory ────────────────────────────────────────────

    public function sharedMemory(Request $request, int $grantId)
    {
        $userId = $request->user()->id;

        $grant = $this->resolveAcceptedGrant($grantId, $userId);
        if (!$grant) abort(404);

        $permissions = json_decode($grant->permissions, true);

        // Load the owner's memory for this deployment
        $memory = $this->loadMemory($grant->owner_user_id, $grant->deployment_id);

        // Track viewed event (once per session per grant)
        $sessionKey = 'sl_viewed_' . $grantId;
        if (!session()->has($sessionKey)) {
            $this->logEvent($grantId, $userId, 'viewed', 'deployment', $grant->deployment_id);
            session()->put($sessionKey, true);
        }

        // Which records has grantee already copied from this grant?
        $copiedIds = DB::table('memory_copy_tags')
            ->where('grant_id', $grantId)
            ->where('grantee_user_id', $userId)
            ->get()
            ->groupBy('table_name')
            ->map(fn($rows) => $rows->pluck('source_record_id')->toArray());

        $granteeDeployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', $grant->worker_slug)
            ->where('status', '!=', 'decommissioned')
            ->select('id', 'name')
            ->get();

        return view('memory.shared', compact('grant', 'permissions', 'memory', 'copiedIds', 'granteeDeployments'));
    }

    // ── Grantee: copy a record ─────────────────────────────────────────────────

    public function copyRecord(Request $request, int $grantId)
    {
        $request->validate([
            'table_name'            => 'required|in:clients,contacts,assets',
            'record_id'             => 'required|integer',
            'target_deployment_id'  => 'required|integer',
        ]);

        $userId       = $request->user()->id;
        $tableName    = $request->input('table_name');
        $recordId     = (int) $request->input('record_id');
        $targetDepId  = (int) $request->input('target_deployment_id');

        $grant = $this->resolveAcceptedGrant($grantId, $userId);
        if (!$grant) abort(404);

        $permissions = json_decode($grant->permissions, true);
        if (!in_array('copy', $permissions)) abort(403, 'Copy permission not granted.');

        // Verify target deployment belongs to grantee
        $targetDep = DB::table('worker_deployments')
            ->where('id', $targetDepId)
            ->where('user_id', $userId)
            ->first();
        if (!$targetDep) abort(403);

        // Fetch the source record
        $source = DB::table($tableName)
            ->where('id', $recordId)
            ->where('user_id', $grant->owner_user_id)
            ->first();
        if (!$source) abort(404);

        // Check not already copied from this grant
        $alreadyCopied = DB::table('memory_copy_tags')
            ->where('grant_id', $grantId)
            ->where('grantee_user_id', $userId)
            ->where('table_name', $tableName)
            ->where('source_record_id', $recordId)
            ->exists();

        if ($alreadyCopied) {
            return back()->with('error', 'You already copied this record.');
        }

        // Copy the record into grantee's own memory
        $row = (array) $source;
        unset($row['id']);
        $row['user_id']       = $userId;
        $row['deployment_id'] = $targetDepId;
        $row['created_at']    = now();
        $row['updated_at']    = now();

        $newId = DB::table($tableName)->insertGetId($row);

        // Write copy tag
        DB::table('memory_copy_tags')->insert([
            'grant_id'              => $grantId,
            'grantee_user_id'       => $userId,
            'grantee_deployment_id' => $targetDepId,
            'table_name'            => $tableName,
            'record_id'             => $newId,
            'source_user_id'        => $grant->owner_user_id,
            'source_record_id'      => $recordId,
            'created_at'            => now(),
        ]);

        $this->logEvent($grantId, $userId, 'copied', $tableName, $recordId,
            "Copied to deployment #{$targetDepId}");

        return back()->with('success', 'Record copied to your ' . $targetDep->name . ' memory.');
    }

    // ── Grantee: upload a new record to owner's memory ────────────────────────

    public function uploadRecord(Request $request, int $grantId)
    {
        $request->validate([
            'table_name' => 'required|in:clients,contacts,assets',
            'data'       => 'required|array',
        ]);

        $userId    = $request->user()->id;
        $tableName = $request->input('table_name');

        $grant = $this->resolveAcceptedGrant($grantId, $userId);
        if (!$grant) abort(404);

        $permissions = json_decode($grant->permissions, true);
        if (!in_array('upload', $permissions)) abort(403, 'Upload permission not granted.');

        $data = $request->input('data');
        $data['user_id']       = $grant->owner_user_id;
        $data['deployment_id'] = $grant->deployment_id;
        $data['created_at']    = now();
        $data['updated_at']    = now();

        // Only allow safe fields
        $allowed = $this->allowedFields($tableName);
        $data    = array_intersect_key($data, array_flip($allowed));

        $newId = DB::table($tableName)->insertGetId($data);

        $this->logEvent($grantId, $userId, 'uploaded', $tableName, $newId,
            'Uploaded by grantee ' . $request->user()->name);

        return back()->with('success', 'Record added to ' . $grant->owner_name . '\'s memory.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveAcceptedGrant(int $grantId, int $userId): ?object
    {
        return DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.owner_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.id', $grantId)
            ->where('g.grantee_user_id', $userId)
            ->where('g.status', 'accepted')
            ->select('g.*', 'u.name as owner_name', 'd.name as deployment_name', 'd.worker_slug')
            ->first();
    }

    private function loadMemory(int $ownerUserId, int $deploymentId): array
    {
        $memory = [];
        foreach (self::MEMORY_TABLES as $table) {
            try {
                $memory[$table] = DB::table($table)
                    ->where('user_id', $ownerUserId)
                    ->where('deployment_id', $deploymentId)
                    ->orderByDesc('updated_at')
                    ->get();
            } catch (\Throwable) {
                $memory[$table] = collect();
            }
        }
        return $memory;
    }

    private function withEventSummary(object $grant): object
    {
        $grant->event_count = DB::table('memory_access_events')
            ->where('grant_id', $grant->id)
            ->count();
        $grant->last_action = DB::table('memory_access_events')
            ->where('grant_id', $grant->id)
            ->orderByDesc('created_at')
            ->value('created_at');
        return $grant;
    }

    private function logEvent(int $grantId, int $actorId, string $action, string $table, ?int $recordId, ?string $notes = null): void
    {
        try {
            DB::table('memory_access_events')->insert([
                'grant_id'      => $grantId,
                'actor_user_id' => $actorId,
                'action'        => $action,
                'table_name'    => $table,
                'record_id'     => $recordId,
                'notes'         => $notes,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[MemoryAccess] event log failed', ['error' => $e->getMessage()]);
        }
    }

    private function allowedFields(string $table): array
    {
        return match($table) {
            'clients'  => ['name', 'email', 'phone', 'company', 'status', 'address', 'notes', 'meta', 'deployment_id', 'user_id', 'created_at', 'updated_at'],
            'contacts' => ['name', 'email', 'phone', 'role', 'company', 'department', 'is_decision_maker', 'notes', 'meta', 'client_id', 'deployment_id', 'user_id', 'created_at', 'updated_at'],
            'assets'   => ['name', 'type', 'vendor', 'renewal_date', 'status', 'service_owner', 'notes', 'meta', 'client_id', 'deployment_id', 'user_id', 'created_at', 'updated_at'],
            default    => [],
        };
    }
}
