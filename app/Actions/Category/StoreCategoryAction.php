<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Str;
use App\Http\Requests\StoreCategoryRequest;

class StoreCategoryAction
{
    public function handle(StoreCategoryRequest $request)
    {
        $category = Category::create([
            ...$request->validated(),
            'slug' => Str::slug($request->name),
        ]);

        return ApiResponse::success(
            new CategoryResource($category),
            'Category created successfully.',
            201
        );
    }
}
