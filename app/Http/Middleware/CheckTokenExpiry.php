<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $token = PersonalAccessToken::findToken($accessToken);

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            // نحذف التوكن المنتهي كمان (عشان ميبقاش فيه فوضى)
            $token->delete();

            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}


