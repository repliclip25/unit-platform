<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Platform\Services\GoogleDrive\DriveService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PresaleController extends Controller
{
    // Starting point only — customers add, rename, or remove categories freely
    // from the Memory Training page, so this is not a fixed taxonomy.
    private const DEFAULT_CATEGORIES = ['Images', 'Videos', 'Presentations', 'Mockups', 'Avatars'];

    public function create(): View
    {
        return view('presale.signup');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company'  => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name'                    => $request->name,
            'email'                   => $request->email,
            'password'                => Hash::make($request->password),
            'presale_worker'          => 'brand-video',
            'onboarding_completed_at' => now(),
        ]);

        event(new Registered($user));
        Auth::login($user);

        DB::table('fast_track_leads')->insertOrIgnore([
            'name'        => $user->name,
            'email'       => $user->email,
            'worker_slug' => 'brand-video',
            'source'      => 'presale_signup',
            'user_id'     => $user->id,
            'subscribed'  => true,
            'flags'       => json_encode(['company' => $request->input('company')]),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        foreach (self::DEFAULT_CATEGORIES as $i => $name) {
            DB::table('brand_memory_categories')->insert([
                'user_id'    => $user->id,
                'name'       => $name,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('presale.memory');
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

        $quota = null;
        if ($credential) {
            try {
                $quota = app(DriveService::class, ['credential' => $credential])->getStorageQuota();
            } catch (\Throwable $e) {
                // Quota is a nice-to-have — a stale/revoked token shouldn't break the whole page,
                // but it's still worth knowing about (usually means the refresh token died).
                Log::warning('Presale Drive quota lookup failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
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

        return view('presale.memory', compact('profile', 'credential', 'categories', 'assets', 'quota', 'steps', 'coveragePct', 'assetCountsByCategory'));
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
