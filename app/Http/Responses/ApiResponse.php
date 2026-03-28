<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => empty($data) ? new \stdClass() : $data,
        ]);
    }

    public static function error(string $message, array $data = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => empty($data) ? new \stdClass() : $data,
        ], $status);
    }
}
