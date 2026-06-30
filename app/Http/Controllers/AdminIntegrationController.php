<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminIntegrationController extends Controller
{
    public function index()
    {
        $rows = DB::table('platform_integrations')->orderBy('sort_order')->get()->map(function ($r) {
            $r->env_keys = json_decode($r->env_keys ?? '[]', true);
            $r->meta     = json_decode($r->meta ?? '{}', true);
            $r->status   = $this->resolveStatus($r);
            return $r;
        });

        $platform = $rows->where('scope', 'platform')->values();
        $workers  = $rows->where('scope', 'worker')->groupBy('worker_slug');

        // Go-live checklist: anything with a local_url that differs from production_url
        $goLive = $rows->filter(fn($r) => $r->local_url && $r->production_url && $r->local_url !== $r->production_url)->values();

        return view('admin.integrations', compact('platform', 'workers', 'goLive'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'label'          => 'required|string|max:100',
            'description'    => 'nullable|string|max:2000',
            'local_url'      => 'nullable|string|max:500',
            'production_url' => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:2000',
        ]);

        DB::table('platform_integrations')->where('id', $id)->update([
            'label'          => $request->label,
            'description'    => $request->description ?: null,
            'local_url'      => $request->local_url ?: null,
            'production_url' => $request->production_url ?: null,
            'notes'          => $request->notes ?: null,
            'updated_at'     => now(),
        ]);

        return back()->with('int_success', 'Integration updated.');
    }

    public function store(Request $request)
    {
        $validTypes = 'oauth,webhook,pubsub,api_key,callback_url,smtp,database,storage,sdk,websocket,sftp';

        $request->validate([
            'scope'          => 'required|in:platform,worker',
            'worker_slug'    => 'nullable|string|max:50',
            'service'        => 'required|string|max:100',
            'label'          => 'required|string|max:100',
            'description'    => 'nullable|string|max:2000',
            'type'           => 'required|in:' . $validTypes,
            'local_url'      => 'nullable|string|max:500',
            'production_url' => 'nullable|string|max:500',
            'env_keys'       => 'nullable|string',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $envKeys = array_filter(array_map('trim', explode(',', $request->env_keys ?? '')));

        DB::table('platform_integrations')->insert([
            'scope'          => $request->scope,
            'worker_slug'    => $request->worker_slug ?: null,
            'service'        => $request->service,
            'label'          => $request->label,
            'description'    => $request->description ?: null,
            'type'           => $request->type,
            'local_url'      => $request->local_url ?: null,
            'production_url' => $request->production_url ?: null,
            'env_keys'       => json_encode(array_values($envKeys)),
            'meta'           => '{}',
            'notes'          => $request->notes ?: null,
            'sort_order'     => DB::table('platform_integrations')->max('sort_order') + 10,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('int_success', 'Integration added.');
    }

    public function destroy(int $id)
    {
        DB::table('platform_integrations')->where('id', $id)->delete();
        return back()->with('int_success', 'Integration removed.');
    }

    private function resolveStatus(object $r): string
    {
        if (empty($r->env_keys)) return 'no_keys';

        $allSet    = true;
        $someSet   = false;

        foreach ($r->env_keys as $key) {
            $val = env($key);
            if ($val) {
                $someSet = true;
            } else {
                $allSet = false;
            }
        }

        if ($allSet)  return 'configured';
        if ($someSet) return 'partial';
        return 'missing';
    }
}
