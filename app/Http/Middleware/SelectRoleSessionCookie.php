<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gives each role area its own browser session cookie.
 * This middleware must run globally before Laravel's StartSession middleware.
 */
class SelectRoleSessionCookie
{
    public function handle(Request $request, Closure $next)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';

        $suffix = match ($firstSegment) {
            'admin' => 'admin',
            'sales' => 'sales',
            'customer' => 'customer',
            default => null,
        };

        if ($suffix !== null) {
            $baseCookie = (string) config('session.cookie', 'laravel-session');
            config(['session.cookie' => $baseCookie . '_' . $suffix]);
        }

        return $next($request);
    }
}
