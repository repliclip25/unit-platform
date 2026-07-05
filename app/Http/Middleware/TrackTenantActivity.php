<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackTenantActivity
{
    // Pages to ignore (high-frequency or non-meaningful)
    private const SKIP_PAGES = [
        'qa.pipeline-status',
        'qa.queue-status',
        'livewire.message',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (
            Auth::check()
            && $request->isMethod('GET')
            && !$request->ajax()
            && !$request->wantsJson()
        ) {
            $routeName = $request->route()?->getName() ?? $request->path();

            if (!in_array($routeName, self::SKIP_PAGES)) {
                $section = match(true) {
                    str_starts_with($routeName, 'workers.')  => 'workers',
                    str_starts_with($routeName, 'billing')   => 'billing',
                    str_starts_with($routeName, 'transactions') => 'transactions',
                    str_starts_with($routeName, 'settings.') => 'settings',
                    str_starts_with($routeName, 'admin.')    => 'admin',
                    $routeName === 'dashboard'               => 'dashboard',
                    default => null,
                };

                $meta = [];
                if ($depId = $request->route('id') ?? $request->route('deployment')) {
                    $meta['ref_id'] = $depId;
                }

                try {
                    DB::table('tenant_activity_log')->insert([
                        'user_id'    => Auth::id(),
                        'page'       => $routeName,
                        'section'    => $section,
                        'action'     => 'view',
                        'ip'         => $request->ip(),
                        'user_agent' => substr($request->userAgent() ?? '', 0, 512),
                        'meta'       => $meta ? json_encode($meta) : null,
                        'created_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // Never let activity logging break the page
                }

            }
        }

        return $response;
    }
}
