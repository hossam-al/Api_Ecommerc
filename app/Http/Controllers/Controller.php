<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function respond(array $payload): JsonResponse
    {
        $statusCode = $payload['status_code'] ?? 200;

        return response()->json($payload, $statusCode);
    }
}
