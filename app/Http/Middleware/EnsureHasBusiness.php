<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHasBusiness
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->id_business) {
            return redirect()->route('usaha.create')
                ->with('warning', 'Lengkapi profil usaha Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
