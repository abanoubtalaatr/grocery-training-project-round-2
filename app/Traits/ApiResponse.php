<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response.
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a success response with additional payload fields at the top level.
     */
    protected function successWithPayload(
        mixed $data = null,
        array $payload = [],
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($payload !== []) {
            $response = array_merge($response, $payload);
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response.
     */
    protected function error(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($data !== null) {
            if (is_array($data)) {
                $response = array_merge($response, $data);
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated response.
     */
    protected function paginated(
        $items,
        string $message = 'Data retrieved successfully',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ], $statusCode);
    }
}