<?php

namespace App\Actions\Api\Category;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateCategoryAction
{
    public function handle(string $id, Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Not implemented'], 501);
    }
}
