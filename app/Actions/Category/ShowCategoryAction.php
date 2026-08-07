<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;

class ShowCategoryAction
{
    public function handle(Category $category)
    {
        return ApiResponse::success(
            new CategoryResource($category),
            'Category retrieved successfully.'
        );
    }
}
