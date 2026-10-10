<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware permission granular untuk route admin platform.
 *
 * Pemakaian: ->middleware('platform.can:manage_announcements')
 * Super-admin (flag is_platform_admin) selalu lolos lewat User::hasPlatformPermission().
 */
class EnsurePlatformPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPlatformPermission($permission)) {
            abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
        }

        return $next($request);
    }
}
