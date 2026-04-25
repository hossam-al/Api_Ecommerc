<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || (int) $user->role_id !== 2) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Seller only.',
            ], 403);
        }

        return $next($request);
    }
}
