<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSelfLearnController extends Controller
{
    public function index()
    {
        $entries = DB::table('platform_self_learn')
            ->orderBy('page_key')
            ->get();

        // Attach engagement stats per entry
        $stats = DB::table('user_self_learn_events')
            ->selectRaw('page_key, event, version, COUNT(DISTINCT user_id) as users, COUNT(*) as total')
            ->groupBy('page_key', 'event', 'version')
            ->get()
            ->groupBy('page_key');

        $dismissedLegacy = DB::table('user_self_learn_dismissed')
            ->selectRaw('page_key, COUNT(DISTINCT user_id) as users')
            ->groupBy('page_key')
            ->pluck('users', 'page_key');

        $entries = $entries->map(function ($entry) use ($stats, $dismissedLegacy) {
            $pageStats  = $stats->get($entry->page_key, collect());
            $shownRow   = $pageStats->where('event', 'shown')->first();
            $dismissRow = $pageStats->where('event', 'dismissed')->first();

            $shown     = (int) ($shownRow->users ?? 0);
            $dismissed = (int) ($dismissRow->users ?? 0) + (int) ($dismissedLegacy[$entry->page_key] ?? 0);
            $rate      = $shown > 0 ? round(($dismissed / $shown) * 100) : null;

            $entry->stats = [
                'shown'        => $shown,
                'dismissed'    => $dismissed,
                'dismiss_rate' => $rate,
            ];
            return $entry;
        });

        return view('admin.self-learn', compact('entries'));
    }

    public function update(Request $request, string $pageKey)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'body'  => 'required|string|max:2000',
        ]);

        $exists = DB::table('platform_self_learn')->where('page_key', $pageKey)->exists();

        if ($exists) {
            DB::table('platform_self_learn')
                ->where('page_key', $pageKey)
                ->update([
                    'title'      => $request->title,
                    'body'       => $request->body,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('platform_self_learn')->insert([
                'page_key'   => $pageKey,
                'title'      => $request->title,
                'body'       => $request->body,
                'active'     => true,
                'version'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', "Self Learn updated for {$pageKey}.");
    }

    public function toggle(string $pageKey)
    {
        DB::table('platform_self_learn')
            ->where('page_key', $pageKey)
            ->update([
                'active'     => DB::raw('NOT active'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Visibility toggled.');
    }

    public function bumpVersion(string $pageKey)
    {
        // Increment version — all existing dismissals (old version) become stale,
        // so every user sees the updated content again.
        DB::table('platform_self_learn')
            ->where('page_key', $pageKey)
            ->update([
                'version'    => DB::raw('version + 1'),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Version bumped — all users will see this again.');
    }
}
