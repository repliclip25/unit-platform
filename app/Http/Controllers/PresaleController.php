<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PresaleController extends Controller
{
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

        return redirect()->route('presale.dashboard');
    }

    public function dashboard(): View
    {
        $user = auth()->user();
        abort_unless($user->isPresale(), 404);

        $profile    = DB::table('brand_profiles')->where('user_id', $user->id)->first();
        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();
        $assets     = DB::table('brand_assets')->where('user_id', $user->id)->orderByDesc('uploaded_at')->get();

        return view('presale.dashboard', compact('profile', 'credential', 'assets'));
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

        return redirect()->route('presale.dashboard')->with('success', 'Brand profile saved.');
    }
}
