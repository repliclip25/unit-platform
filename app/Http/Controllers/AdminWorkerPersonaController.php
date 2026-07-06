<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWorkerPersonaController extends Controller
{
    public function index(string $slug)
    {
        $worker   = DB::table('workers')->where('slug', $slug)->firstOrFail();
        $contract = WorkerRegistry::resolve($slug);
        $personas = $contract->personas();

        $adoptionCounts = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->whereNotNull('persona')
            ->selectRaw('persona, COUNT(*) as total')
            ->groupBy('persona')
            ->pluck('total', 'persona')
            ->all();

        $totalDeployments = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->count();

        $noPersonaCount = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->whereNull('persona')
            ->count();

        $platformRuleCounts = DB::table('ava_rules')
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->whereNotNull('persona')
            ->selectRaw('persona, COUNT(*) as total')
            ->groupBy('persona')
            ->pluck('total', 'persona')
            ->all();

        $contractRuleCounts = [];
        foreach ($personas as $key => $p) {
            $contractRuleCounts[$key] = count($p['capture_rules'] ?? []);
        }

        // Raw DB rows for edit forms
        $personaRows = DB::table('worker_personas')
            ->where('worker_slug', $slug)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');

        return view('admin.worker-personas', compact(
            'worker', 'slug', 'personas',
            'adoptionCounts', 'totalDeployments', 'noPersonaCount',
            'platformRuleCounts', 'contractRuleCounts', 'personaRows'
        ));
    }

    public function store(string $slug, Request $request)
    {
        DB::table('workers')->where('slug', $slug)->firstOrFail();

        $request->validate([
            'key'     => 'required|alpha_dash|max:50',
            'label'   => 'required|max:100',
            'tagline' => 'required|max:200',
            'detail'  => 'required',
        ]);

        $key = strtolower($request->key);

        if (DB::table('worker_personas')->where('worker_slug', $slug)->where('key', $key)->exists()) {
            return back()->withErrors(['key' => 'A persona with this key already exists.']);
        }

        $maxOrder = DB::table('worker_personas')->where('worker_slug', $slug)->max('sort_order') ?? -1;

        DB::table('worker_personas')->insert([
            'worker_slug'   => $slug,
            'key'           => $key,
            'label'         => $request->label,
            'tagline'       => $request->tagline,
            'detail'        => $request->detail,
            'icon'          => $request->input('icon', 'grid'),
            'asset_types'   => json_encode($this->parseKeyValue($request->input('asset_types', ''))),
            'examples'      => json_encode($this->parseLines($request->input('examples', ''))),
            'memory_copy'   => json_encode([
                'client_noun'    => $request->input('client_noun', 'client'),
                'asset_noun'     => $request->input('asset_noun', 'asset'),
                'example_client' => $request->input('example_client', ''),
                'example_asset'  => $request->input('example_asset', ''),
            ]),
            'nudge_copy'    => json_encode($this->buildNudgeCopy($request)),
            'capture_rules' => json_encode([]),
            'is_active'     => true,
            'sort_order'    => $maxOrder + 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return back()->with('success', "Persona "{$request->label}" created.");
    }

    public function update(string $slug, int $id, Request $request)
    {
        $row = DB::table('worker_personas')->where('id', $id)->where('worker_slug', $slug)->firstOrFail();

        $request->validate([
            'label'   => 'required|max:100',
            'tagline' => 'required|max:200',
            'detail'  => 'required',
        ]);

        DB::table('worker_personas')->where('id', $id)->update([
            'label'       => $request->label,
            'tagline'     => $request->tagline,
            'detail'      => $request->detail,
            'icon'        => $request->input('icon', $row->icon),
            'asset_types' => json_encode($this->parseKeyValue($request->input('asset_types', ''))),
            'examples'    => json_encode($this->parseLines($request->input('examples', ''))),
            'memory_copy' => json_encode([
                'client_noun'    => $request->input('client_noun', 'client'),
                'asset_noun'     => $request->input('asset_noun', 'asset'),
                'example_client' => $request->input('example_client', ''),
                'example_asset'  => $request->input('example_asset', ''),
            ]),
            'nudge_copy'  => json_encode($this->buildNudgeCopy($request)),
            'is_active'   => $request->boolean('is_active', true),
            'updated_at'  => now(),
        ]);

        return back()->with('success', "Persona "{$row->label}" updated.");
    }

    public function destroy(string $slug, int $id)
    {
        $row = DB::table('worker_personas')->where('id', $id)->where('worker_slug', $slug)->firstOrFail();

        $inUse = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->where('persona', $row->key)
            ->count();

        if ($inUse > 0) {
            return back()->withErrors(['delete' => "Cannot remove "{$row->label}" — {$inUse} deployment(s) are using this persona."]);
        }

        DB::table('worker_personas')->where('id', $id)->delete();

        return back()->with('success', "Persona "{$row->label}" removed.");
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function parseLines(string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }

    private function parseKeyValue(string $raw): array
    {
        $result = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $result[trim($k)] = trim($v);
            } else {
                $slug = strtolower(preg_replace('/\s+/', '_', $line));
                $result[$slug] = $line;
            }
        }
        return $result;
    }

    private function buildNudgeCopy(Request $request): array
    {
        $copy = [];
        foreach (['d1', 'd3', 'd7'] as $day) {
            $subject = $request->input("nudge_{$day}_subject", '');
            $body    = $request->input("nudge_{$day}_body", '');
            if ($subject || $body) {
                $copy[$day] = ['subject' => $subject, 'body' => $body];
            }
        }
        return $copy;
    }
}
