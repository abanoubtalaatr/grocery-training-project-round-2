<?php

namespace App\Actions\Api\Category;

use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Models\Category;

class CategoryAction
{
    /**
     * Get all active categories.
     */
    public function index()
    {
        return Category::active()
            ->ordered()
            ->withCount('meals')
            ->get();
    }

    /**
     * Load a category with its available meals.
     */
    public function show(Category $category): Category
    {
        return $category->load([
            'meals' => fn ($query) => $query
                ->available()
                ->latest(),
        ]);
    }

    /**
     * Create a category.
     */
    public function store(StoreCategoryRequest $request): Category
    {
        return Category::create($request->validated());
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): Category
    {
        $category->update($request->validated());

        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }
}
