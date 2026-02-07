<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate;

class CheckRole
{
    protected Authenticate $auth;

    public function __construct(Authenticate $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Force Laravel to resolve authenticated user first
        $this->auth->handle($request, fn() => null);

        $user = $request->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized. Required roles: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
