<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Http\Responses\ApiResponse;

class DestroyCategoryAction
{
    public function handle(Category $category)
    {
        $category->delete();

        return ApiResponse::success(
            null,
            'Category deleted successfully.'
        );
    }
}
