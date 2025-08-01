<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // public function handle(Request $request, Closure $next, $role): Response
    // {
    //     if (auth()->check() && auth()->user()->role === $role) {
    //         return $next($request);
    //     }

    //     abort(403, 'Maaf halaman ini tidak bisa diakses anda');
    // }

    public function handle(Request $request, Closure $next, $role): Response
    {
        if (
            auth()->check() && (
                auth()->user()->role === $role ||
                auth()->user()->role === 'superadmin'
            )
        ) {
            return $next($request);
        }

        abort(403, 'Maaf halaman ini tidak bisa diakses anda');
    }
}
