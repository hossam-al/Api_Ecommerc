<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->role_id !== 1) {
            if (!$request->expectsJson()) {
                abort(403);
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Super Admin only.'
            ], 403);
        }

        return $next($request);
    }
}
