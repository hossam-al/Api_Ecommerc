<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_banned) {
            $user->currentAccessToken()?->delete();

            return response()->json([
                'status' => false,
                'message' => 'Your account has been banned.',
            ], 403);
        }

        return $next($request);
    }
}
