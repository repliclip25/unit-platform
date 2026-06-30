<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectGuestWorkerPage
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() && preg_match('#^workers/([^/]+)$#', $request->path(), $m)) {
            return redirect('/w/' . $m[1], 302);
        }

        return $next($request);
    }
}
