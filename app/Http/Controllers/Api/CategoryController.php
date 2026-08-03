<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all categories
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        $categories = Category::active()
            ->ordered()
            ->withCount([
                'meals' => fn($query) => $query->available(),
            ])
            ->paginate($perPage);

        return $this->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }
    /**
     * Get single category with meals
     */
    public function show(Category $category): JsonResponse
    {
        $category->load([
            'meals' => function ($query) {
                $query->available()->latest();
            },
        ])
            ->loadCount([
                'meals' => function ($query) {
                    $query->available();
                },
            ]);

        return $this->success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }
    
}
