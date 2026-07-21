<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function apiKeys()
    {
        $userId       = auth()->id();
        $keys         = DB::table('tenant_api_keys')->where('user_id', $userId)->get()->keyBy('provider');
        $customModels = DB::table('tenant_custom_models')->where('user_id', $userId)->where('active', true)->get();

        // Which platform keys are configured in .env
        $platformKeys = [
            'anthropic' => !empty(config('services.claude.api_key')),
            'openai'    => !empty(config('services.openai.api_key')),
            'kimi'      => !empty(config('services.kimi.api_key')),
            'google'    => !empty(config('services.google.api_key')),
        ];

        // Workers and which model they're running
        $workers = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->get()
            ->map(fn($dep) => (object)[
                'id'          => $dep->id,
                'name'        => $dep->name,
                'status'      => $dep->status,
                'model'       => json_decode($dep->config ?? '{}', true)['ai_model'] ?? 'claude-sonnet-4-6',
                'worker_slug' => $dep->worker_slug,
            ]);

        return view('dashboard.api-keys', compact('keys', 'customModels', 'platformKeys', 'workers'));
    }

    public function storeApiKey(Request $request)
    {
        $request->validate(['provider' => 'required', 'label' => 'required', 'api_key' => 'required']);
        DB::table('tenant_api_keys')->updateOrInsert(
            ['user_id' => auth()->id(), 'provider' => $request->provider],
            ['label' => $request->label, 'api_key_encrypted' => Crypt::encryptString($request->api_key), 'active' => true, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('success', $request->provider . ' key saved.');
    }

    public function destroyApiKey(string $provider)
    {
        DB::table('tenant_api_keys')->where('user_id', auth()->id())->where('provider', $provider)->delete();
        return back()->with('success', 'Key removed.');
    }

    public function storeCustomModel(Request $request)
    {
        $request->validate(['name' => 'required', 'base_url' => 'required|url', 'model_identifier' => 'required']);
        $modelId = 'custom-' . Str::slug($request->name) . '-' . substr(md5(uniqid()), 0, 6);
        $data = [
            'user_id'           => auth()->id(),
            'name'              => $request->name,
            'model_id'          => $modelId,
            'model_identifier'  => $request->model_identifier,
            'base_url'          => rtrim($request->base_url, '/'),
            'api_key_encrypted' => $request->api_key ? Crypt::encryptString($request->api_key) : null,
            'active'            => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
        DB::table('tenant_custom_models')->insert($data);
        return back()->with('success', '"' . $request->name . '" registered. Select it from the worker model picker.');
    }

    public function destroyCustomModel(int $id)
    {
        DB::table('tenant_custom_models')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Custom model removed.');
    }

    // Account deletion lives on ProfileController::destroy() (route: profile.destroy)
    // — a 30-day scheduled soft-delete with a cancellation window, matching the
    // deletion_requested_at column and PurgeScheduledDeletionsCommand. This
    // controller previously had its own deleteAccount() that hard-deleted
    // everything immediately with no grace period — a dangerous divergent
    // duplicate reachable from the Settings page. Removed; the Settings page's
    // Danger Zone now posts to profile.destroy like the Profile page does.
}
