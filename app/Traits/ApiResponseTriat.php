<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
 protected function successResponse(mixed $data=null,string $message,int $statusCode=Response::HTTP_OK):JsonResponse
 {
return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
 }

 protected function errorResponse(
        string $message = 'Error',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}