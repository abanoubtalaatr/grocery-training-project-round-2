<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request)
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

    public function show(Category $category)
    {
        return ApiResponse::success(
            new CategoryResource($category),
            'Category retrieved successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
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

    public function destroy(Category $category)
    {
        $category->delete();

        return ApiResponse::success(
            null,
            'Category deleted successfully.'
        );
    }
}