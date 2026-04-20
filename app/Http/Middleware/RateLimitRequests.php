<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitRequests
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->ip();

        // 5 attempts per 15 minutes
        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'status' => false,
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.'
            ], 429);
        }

        $response = $next($request);

        // If the request fails (invalid credentials), increment the counter
        if ($response->getStatusCode() === 401) {
            $this->limiter->hit($key, 15 * 60); // 15 minutes
        }

        return $response;
    }
}
