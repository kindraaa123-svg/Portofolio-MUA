<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticatedWith403
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'Silakan login terlebih dahulu untuk mengakses halaman admin.');
        }

        return $next($request);
    }
}
