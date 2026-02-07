<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate;

class CheckAllRoles
{
    protected Authenticate $auth;

    public function __construct(Authenticate $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next, ...$roles)
    {
        $this->auth->handle($request, fn() => null);

        $user = $request->user();

        if (! $user || ! $user->hasAllRoles($roles)) {
            abort(403, 'Unauthorized. Required all roles: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
