<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalHost
{
    // Skipped outside production so local dev (MAMP on localhost:8888) and
    // CI/tests never get redirected — this only matters once real DNS/SSL
    // for the canonical host is live.
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->environment('production')) {
            return $next($request);
        }

        $canonicalHost = config('app.canonical_host');

        if ($request->getHost() !== $canonicalHost || !$request->secure()) {
            return redirect()->to(
                'https://' . $canonicalHost . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
