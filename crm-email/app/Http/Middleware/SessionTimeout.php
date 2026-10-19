<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle($request, Closure $next)
    {
        $timeout = 900; // 15 menit (detik)

        if (Auth::check()) {
            if (session('lastActivityTime')) {
                if (time() - session('lastActivityTime') > $timeout) {
                    Auth::logout();
                    session()->flush();
                    return redirect('/login')->with('message', 'Session habis');
                }
            }
            session(['lastActivityTime' => time()]);
        }

        return $next($request);
    }
}