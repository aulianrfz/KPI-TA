<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticte
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::guard($guards)->guest()) {
            return redirect()->route('login'); // <- penting: redirect ke login jika belum login
        }

        return $next($request);
    }

}
