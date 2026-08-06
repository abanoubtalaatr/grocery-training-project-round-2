<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateCategoryAction;
use App\Action\Api\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->latest()
            ->paginate(10);

        return $this->success(CategoryResource::collection($categories),'Categories retrieved successfully');
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $category = $action->execute($request->validated());

        return $this->success(new CategoryResource($category),'Category created successfully',201);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success(new CategoryResource($category),'Category retrieved successfully');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): JsonResponse
    {
        $action->execute($category, $request->validated());

        return $this->success(new CategoryResource($category),'Category updated successfully');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->success(null, 'Category deleted successfully');
    }
}
