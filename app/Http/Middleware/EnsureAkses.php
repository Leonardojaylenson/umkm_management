<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAkses
{
    public function handle(Request $request, Closure $next, string $menu)
    {
        abort_unless($request->user()->canAccess($menu), 403, 'Anda tidak punya akses ke menu ini.');

        return $next($request);
    }
}
