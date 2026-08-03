<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiTrait
{

    public function successResponse(string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => (object)[],
                'data' => (object)[],
            ],
            $statusCode
        );
    }
    public function errorResponse($errors, string $message = "", int $statusCode = 400): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => $errors,
                'data' => (object)[],
            ],
            $statusCode
        );
    }

    public function dataResponse($data, string $message = "", int $statusCode = 200)
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => (object)[],
                'data' => (object)$data,
            ],
            $statusCode
        );
    }
}
