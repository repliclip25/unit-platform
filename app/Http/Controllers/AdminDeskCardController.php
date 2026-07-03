<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Platform\Services\DeskCardRegistry;

class AdminDeskCardController extends Controller
{
    public function index()
    {
        $config = DB::table('platform_desk_card_config')
            ->get()
            ->keyBy('card_key');

        $staticPool = DeskCardRegistry::all();

        // Build full list: static pool + any DB-only entries
        $cards = collect($staticPool)->map(function ($def, $key) use ($config) {
            $row = $config->get($key);
            return [
                'key'         => $key,
                'tier'        => $def['tier'],
                'label'       => $row?->label       ?? $def['label'],
                'description' => $row?->description ?? $def['description'],
                'active'      => $row ? (bool) $row->active     : true,
                'default_on'  => $row ? (bool) $row->default_on : ($def['default'] ?? true),
                'sort_order'  => $row?->sort_order ?? $def['default_pos'] ?? 50,
                'dismissible' => $def['dismissible'] ?? false,
                'is_worker'   => false,
            ];
        });

        // Group by tier for display
        $tierLabels = [
            'pipeline' => 'Pipeline (worker-declared)',
            'memory'   => 'Memory',
            'growth'   => 'Growth',
            'platform' => 'Platform / Marketing',
        ];

        $grouped = $cards->groupBy('tier');

        return view('admin.desk-cards', compact('grouped', 'tierLabels', 'config'));
    }

    public function save(Request $request)
    {
        $cards = $request->input('cards', []);

        foreach ($cards as $key => $data) {
            // Only allow known keys from the registry
            if (!DeskCardRegistry::get($key)) continue;

            DB::table('platform_desk_card_config')->updateOrInsert(
                ['card_key' => $key],
                [
                    'active'      => (bool) ($data['active']     ?? true),
                    'default_on'  => (bool) ($data['default_on'] ?? true),
                    'label'       => $data['label']       ?: null,
                    'description' => $data['description'] ?: null,
                    'sort_order'  => (int)  ($data['sort_order']  ?? 50),
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }

        return back()->with('success', 'Desk card configuration saved.');
    }

    public function toggle(Request $request, string $key)
    {
        if (!DeskCardRegistry::get($key)) {
            return response()->json(['ok' => false], 422);
        }

        $current = DB::table('platform_desk_card_config')->where('card_key', $key)->first();
        $newVal  = $current ? !(bool) $current->active : false; // if not in DB, default is true → toggle to false

        DB::table('platform_desk_card_config')->updateOrInsert(
            ['card_key' => $key],
            ['active' => $newVal, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['ok' => true, 'active' => $newVal]);
    }

    public function toggleDefault(Request $request, string $key)
    {
        if (!DeskCardRegistry::get($key)) {
            return response()->json(['ok' => false], 422);
        }

        $current = DB::table('platform_desk_card_config')->where('card_key', $key)->first();
        $staticDef = DeskCardRegistry::all()[$key] ?? [];
        $currentDefault = $current ? (bool) $current->default_on : ($staticDef['default'] ?? true);
        $newVal = !$currentDefault;

        DB::table('platform_desk_card_config')->updateOrInsert(
            ['card_key' => $key],
            ['default_on' => $newVal, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['ok' => true, 'default_on' => $newVal]);
    }
}
