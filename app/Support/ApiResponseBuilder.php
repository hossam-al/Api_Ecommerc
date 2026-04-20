<?php

namespace App\Support;

class ApiResponseBuilder
{
    public static function success(
        string $message,
        mixed $data = null,
        int $statusCode = 200,
        array $extra = []
    ): array {
        $payload = [
            'status' => true,
            'status_code' => $statusCode,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return array_merge($payload, $extra);
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        array $extra = []
    ): array {
        return array_merge([
            'status' => false,
            'status_code' => $statusCode,
            'message' => $message,
        ], $extra);
    }
}
