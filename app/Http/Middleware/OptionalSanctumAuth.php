<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestUser = $request->user();

        if ($requestUser?->is_banned) {
            $request->setUserResolver(fn (?string $guard = null) => null);
        } elseif ($requestUser) {
            return $next($request);
        }

        $sanctumUser = Auth::guard('sanctum')->user();

        if ($sanctumUser && !$sanctumUser->is_banned) {
            Auth::shouldUse('sanctum');

            $request->setUserResolver(
                fn (?string $guard = null) => $guard ? Auth::guard($guard)->user() : $sanctumUser
            );
        }

        return $next($request);
    }
}
