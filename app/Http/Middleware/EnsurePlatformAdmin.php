<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super-admin (flag lama) atau user platform yang punya minimal satu role.
        if (! $user || ! ((bool) $user->is_platform_admin || $user->hasAnyPlatformRole())) {
            abort(403);
        }

        return $next($request);
    }
}
