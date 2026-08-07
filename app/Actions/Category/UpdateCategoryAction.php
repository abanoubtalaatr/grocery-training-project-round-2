<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateCategoryRequest;

class UpdateCategoryAction
{
    public function handle(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return ApiResponse::success(
            new CategoryResource($category),
            'Category updated successfully.'
        );
    }
}
