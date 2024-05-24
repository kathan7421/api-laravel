<?php

namespace App\Http\Middleware;

use Closure;

class PreventBrowserAccess
{
    public function handle($request, Closure $next)
    {
        if ($request->is('api/*') && $request->isMethod('get') && !$request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
