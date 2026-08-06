<?php

namespace App\Http\Controllers\Api;

use App\Actions\Category\DeleteCategoryAction;
use App\Actions\Category\GetAllCategoriesAction;
use App\Actions\Category\GetSingleCategoryAction;
use App\Actions\Category\StoreCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
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

    /**
     * Display a listing of active categories with meals count.
     */
    public function index(GetAllCategoriesAction $action): JsonResponse
    {
        $categories = $action->execute();
        
        return $this->Success(CategoryResource::collection($categories), 'Categories retrieved successfully');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request, StoreCategoryAction $action): JsonResponse
    {
        $category = $action->execute($request->validated());
        
        return $this->Success(new CategoryResource($category), 'Category created successfully', 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, string $id, GetSingleCategoryAction $action): JsonResponse
    {
        $category = $action->execute($id);
        
        if ($request->user() && $request->user()->cannot('view', $category)) {
            return $this->Error(null, 'This action is unauthorized.', 403);
        }
        
        return $this->Success(new CategoryResource($category), 'Category retrieved successfully');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id, UpdateCategoryAction $action): JsonResponse
    {
        $category = Category::findOrFail($id);
        $updatedCategory = $action->execute($category, $request->validated());
        return $this->Success(new CategoryResource($updatedCategory), 'Category updated successfully');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(string $id, DeleteCategoryAction $action): JsonResponse
    {
        $category = Category::findOrFail($id);
        $action->execute($category);
        return $this->Success(null, 'Category deleted successfully');
    }
}