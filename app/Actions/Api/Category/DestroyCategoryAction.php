<?php

namespace App\Actions\Api\Category;

use Illuminate\Http\JsonResponse;

class DestroyCategoryAction
{
    public function handle(string $id): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
