<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedMethods = config('cors.allowed_methods', ['*']);
        $allowedHeaders = config('cors.allowed_headers', ['*']);
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $exposedHeaders = config('cors.exposed_headers', []);
        $maxAge = config('cors.max_age', 0);
        $supportsCredentials = config('cors.supports_credentials', false);

        $methodsHeader = in_array('*', $allowedMethods) ? 'GET,POST,PUT,PATCH,DELETE,OPTIONS' : implode(',', $allowedMethods);
        $headersHeader = in_array('*', $allowedHeaders) ? 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-XSRF-TOKEN' : implode(',', $allowedHeaders);

        if (in_array('*', $allowedOrigins)) {
            $originHeader = '*';
        } else {
            $requestOrigin = $request->headers->get('Origin');
            $originHeader = ($requestOrigin && in_array($requestOrigin, $allowedOrigins)) ? $requestOrigin : ($allowedOrigins[0] ?? '');
        }

        $corsHeaders = [
            'Access-Control-Allow-Origin' => $originHeader,
            'Access-Control-Allow-Methods' => $methodsHeader,
            'Access-Control-Allow-Headers' => $headersHeader,
        ];

        if (!empty($exposedHeaders)) {
            $corsHeaders['Access-Control-Expose-Headers'] = implode(',', $exposedHeaders);
        }

        if ($maxAge) {
            $corsHeaders['Access-Control-Max-Age'] = (string) $maxAge;
        }

        if ($supportsCredentials) {
            $corsHeaders['Access-Control-Allow-Credentials'] = 'true';
        }

        // Handle preflight requests quickly
        if ($request->getMethod() === 'OPTIONS') {
            return response()->noContent(204, $corsHeaders);
        }

        $response = $next($request);

        foreach ($corsHeaders as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
