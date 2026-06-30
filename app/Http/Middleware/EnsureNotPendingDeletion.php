<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotPendingDeletion
{
    // Routes the pending-deletion user is allowed to access
    private const ALLOWED_ROUTES = [
        'profile.show',
        'profile.cancel-deletion',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->deletion_requested_at) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (in_array($routeName, self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        // Redirect to profile where they see the cancellation banner
        return redirect()->route('profile.show');
    }
}
