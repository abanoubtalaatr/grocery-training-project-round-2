<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $status = 200, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'success' => true,
            'message' => $message,
        ], $extra);

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function errorResponse(string $message, int $status = 400, mixed $errors = null, array $extra = []): JsonResponse
    {
        $payload = array_merge([
            'success' => false,
            'message' => $message,
        ], $extra);

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator, mixed $data, string $message = 'Success', int $status = 200, array $extra = []): JsonResponse
    {
        return $this->successResponse($data, $message, $status, array_merge($extra, [
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]));
    }
}
