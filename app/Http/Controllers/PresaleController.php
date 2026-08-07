<?php

namespace App\Http\Controllers;

use App\Platform\Services\GoogleDrive\DriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PresaleController extends Controller
{
    // Starting point only — customers add, rename, or remove categories freely
    // from the Memory Training page, so this is not a fixed taxonomy.
    public const DEFAULT_CATEGORIES = ['Images', 'Videos', 'Presentations', 'Mockups', 'Avatars'];

    /**
     * Presale signup goes through the platform's one shared registration
     * form (RegisteredUserController), not a separate page — this is what
     * that controller calls to check a `?worker=` intent against the
     * presale roster before branching a new account into the presale path.
     */
    public static function isPresaleWorker(?string $slug): bool
    {
        return $slug && in_array($slug, PresaleWorkerController::slugs(), true);
    }

    public static function seedDefaultCategories(int $userId): void
    {
        foreach (self::DEFAULT_CATEGORIES as $i => $name) {
            DB::table('brand_memory_categories')->insert([
                'user_id'    => $userId,
                'name'       => $name,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function memory(): View
    {
        $user = auth()->user();
        abort_unless($user->isPresale(), 404);

        $profile    = DB::table('brand_profiles')->where('user_id', $user->id)->first();
        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();
        $categories = DB::table('brand_memory_categories')->where('user_id', $user->id)->orderBy('sort_order')->get();
        $assets     = DB::table('brand_assets')
            ->leftJoin('brand_memory_categories', 'brand_assets.category_id', '=', 'brand_memory_categories.id')
            ->where('brand_assets.user_id', $user->id)
            ->orderByDesc('brand_assets.uploaded_at')
            ->select('brand_assets.*', 'brand_memory_categories.name as category_name')
            ->get();

        // How many items live in each folder — the actual signal of how much
        // training data we have access to, not just that the folder exists.
        $assetCountsByCategory = $assets->countBy('category_id');
        $assetsByCategory      = $assets->groupBy('category_id');

        $quota       = null;
        $driveNotice = null;
        if ($credential) {
            try {
                $drive = app(DriveService::class, ['credential' => $credential]);
                $quota = $drive->getStorageQuota();

                // Detects a folder trashed/deleted outside UNIT and recreates it —
                // ensureFolder() already self-heals; comparing the id before/after
                // tells us whether that happened, so we can say something about it
                // rather than silently swapping the folder out from under the tenant.
                $previousFolderId = $credential->root_folder_id;
                $folder = $drive->ensureFolder($profile?->business_name ?? $user->name);
                if ($previousFolderId && $folder['id'] !== $previousFolderId) {
                    $driveNotice = "Your Drive brand folder was moved or deleted, so we created a new one automatically. Anything uploaded to the old folder stays there — it won't be moved.";
                }
                // Reflect the current folder without a second DB round-trip.
                $credential->root_folder_id  = $folder['id'];
                $credential->root_folder_url = $folder['url'];
            } catch (\Throwable $e) {
                // A stale/revoked token shouldn't break the whole page, but it's
                // still worth knowing about (usually means the refresh token died).
                Log::warning('Presale Drive check failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        // These four checklist items are the sidebar's "steps" data, and drive
        // the Memory Coverage % on the right panel — same shape as any worker's
        // hire-flow steps, just standing in for a worker that isn't built yet.
        $stepDone = [
            'profile'  => !empty($profile?->business_name),
            'drive'    => (bool) $credential,
            'folders'  => $categories->contains(fn ($c) => !empty($c->drive_folder_id)),
            'assets'   => $assets->isNotEmpty(),
        ];
        $doneCount = count(array_filter($stepDone));
        $coveragePct = (int) round(($doneCount / count($stepDone)) * 100);

        $stepMeta = [
            'profile' => ['label' => 'Business Profile', 'desc' => 'Name, voice, colors'],
            'drive'   => ['label' => 'Connect Drive',    'desc' => 'Where your assets live'],
            'folders' => ['label' => 'Folders',           'desc' => 'Organize by type'],
            'assets'  => ['label' => 'Upload Assets',     'desc' => 'Logos, videos, images'],
        ];
        $reachedActive = false;
        $steps = [];
        $i = 0;
        foreach ($stepMeta as $key => $meta) {
            $i++;
            if ($stepDone[$key]) {
                $state = 'done';
            } elseif (!$reachedActive) {
                $state = 'active';
                $reachedActive = true;
            } else {
                $state = 'pending';
            }
            $steps[] = ['label' => $meta['label'], 'desc' => $meta['desc'], 'state' => $state, 'num' => $i];
        }

        return view('presale.memory', compact('profile', 'credential', 'categories', 'assets', 'quota', 'steps', 'coveragePct', 'assetCountsByCategory', 'assetsByCategory', 'driveNotice'));
    }

    public function saveProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isPresale(), 404);

        $data = $request->validate([
            'business_name'    => ['nullable', 'string', 'max:255'],
            'tagline'          => ['nullable', 'string', 'max:255'],
            'brand_voice'      => ['nullable', 'string', 'max:5000'],
            'primary_color'    => ['nullable', 'string', 'max:20'],
            'secondary_color'  => ['nullable', 'string', 'max:20'],
            'reference_links'  => ['nullable', 'string', 'max:5000'],
            'notes'            => ['nullable', 'string', 'max:5000'],
        ]);

        DB::table('brand_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            $data + ['updated_at' => now(), 'created_at' => now()]
        );

        return redirect()->route('presale.memory')->with('success', 'Brand profile saved.');
    }
}
