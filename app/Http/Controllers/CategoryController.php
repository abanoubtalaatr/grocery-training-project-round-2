<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest()->paginate(10);

        return ApiResponse::success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create(
            $this->prepareData($request->safe()->all())
        );

        return ApiResponse::success(
            new CategoryResource($category),
            'Category created successfully.',
            201
        );
    }

    public function show(Category $category)
    {
        return ApiResponse::success(
            new CategoryResource($category),
            'Category retrieved successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update(
            $this->prepareData($request->safe()->all())
        );

        return ApiResponse::success(
            new CategoryResource($category->fresh()),
            'Category updated successfully.'
        );
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return ApiResponse::success(
            null,
            'Category deleted successfully.'
        );
    }

    /**
     * Prepare category data before saving.
     */
    private function prepareData(array $data): array
    {
        if (!empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}