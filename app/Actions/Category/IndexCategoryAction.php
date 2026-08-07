<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class IndexCategoryAction
{
    public function handle(Request $request)
    {
        $categories = Category::query()
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }
}
