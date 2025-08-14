<?php

namespace MattYeend\GuestToUserHelper\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class AssignGuestIdentifier
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            $key = config('guest-upgrade.session_key');
            if (!session()->has($key)) {
                session([$key => (string) Str::uuid()]);
            }
        }

        return $next($request);
    }
}
