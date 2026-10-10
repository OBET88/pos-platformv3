<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('app.force_https')) {
            return $next($request);
        }

        if ($request->isSecure() || $request->header('x-forwarded-proto') === 'https') {
            return $next($request);
        }

        return redirect()->to('https://'.$request->getHttpHost().$request->getRequestUri(), 301);
    }
}
