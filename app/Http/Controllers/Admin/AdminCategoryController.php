<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Category\CreateCategoryAction;
use App\Action\Admin\Category\DeleteCategoryAction;
use App\Action\Admin\Category\ToggleCategoryStatusAction;
use App\Action\Admin\Category\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminCategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::withCount('meals')->withCount('subcategories')->latest()->get();

        return $this->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $category = $action->execute($request->validated());

        return $this->success(new CategoryResource($category),'Category created successfully',201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount(['meals', 'subcategories']);
        $category->load('subcategories');

        return $this->success(new CategoryResource($category),'Category retrieved successfully');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): JsonResponse
    {
        $action->execute($category, $request->validated());

        return $this->success(new CategoryResource($category->fresh()),'Category updated successfully');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): JsonResponse
    {
        $action->execute($category);

        return $this->success(null, 'Category deleted successfully');
    }

    public function toggleStatus(Category $category, ToggleCategoryStatusAction $action): JsonResponse
    {
        $action->execute($category);

        return $this->success(new CategoryResource($category->fresh()),'Category status updated successfully');
    }
}
