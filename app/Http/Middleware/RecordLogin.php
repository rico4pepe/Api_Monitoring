<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordLogin
{
    /**
     * Record user login IP and timestamp.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->last_login_at) {
            // Only record on first request of the session
            $request->user()->recordLogin($request->ip());
        }

        return $next($request);
    }
}
