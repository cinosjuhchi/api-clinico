<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            Auth::user()->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
