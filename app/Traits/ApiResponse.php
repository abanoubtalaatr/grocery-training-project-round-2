<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{

    public function success(mixed $data = [], string $message = '', int $status = 200): JsonResponse
    {

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            $response = $data->toResponse(request())->getData(true);

            $payload = [
                'success' => true,
                'message' => $message,
                'data'    => $response['data'] ?? $response,
            ];

            if (isset($response['links'])) {
                $payload['links'] = $response['links'];
            }

            if (isset($response['meta'])) {
                $payload['meta'] = $response['meta'];
            }

            return response()->json($payload, $status);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(string $message = '', int $status = 422, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
