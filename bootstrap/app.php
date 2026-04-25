<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\CheckTokenExpiry;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\OptionalSanctumAuth;
use App\Http\Middleware\SellerMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware خاص بالويب
        // $middleware->web(append: [
        //     EncryptCookies::class,
        // ]);

        // // Middleware خاص بالـ API
        // $middleware->api(append: [
        //     CheckTokenExpiry::class,
        // ]);
        $middleware->alias([
            'super_admin' => SuperAdminMiddleware::class,
            'seller' => SellerMiddleware::class,
            'not_banned' => EnsureUserIsNotBanned::class,
            'optional_sanctum' => OptionalSanctumAuth::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $shouldReturnJson = function (Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        };

        $exceptions->render(function (ValidationException $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            $modelName = class_basename($e->getModel());

            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => $modelName . ' not found',
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            $previous = $e->getPrevious();
            $message = 'Resource not found';

            if ($previous instanceof ModelNotFoundException) {
                $message = class_basename($previous->getModel()) . ' not found';
            }

            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => $message,
            ], 404);
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($shouldReturnJson) {
            if (!$shouldReturnJson($request)) {
                return null;
            }

            $statusCode = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            return response()->json([
                'status' => false,
                'status_code' => $statusCode,
                'message' => $statusCode >= 500 ? 'Server error' : ($e->getMessage() ?: 'Request failed'),
            ], $statusCode);
        });
    })
    ->create();
