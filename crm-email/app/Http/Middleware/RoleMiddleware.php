<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next, $role = null)
{
    // kalau belum login → biarkan auth yang handle
    if (!auth()->check()) {
        return $next($request);
    }

    // cek role
    if ($role && auth()->user()->role != $role) {
        return redirect('/dashboard'); // jangan 403 biar tidak error
    }

    return $next($request);
}
}

