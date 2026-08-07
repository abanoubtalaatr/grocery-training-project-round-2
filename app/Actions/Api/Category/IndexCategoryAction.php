<?php

namespace App\Actions\Api\Category;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexCategoryAction
{
    public function handle(Request $request): JsonResponse
    {
        try {
            $categories = Category::active()
                ->ordered()
                ->withCount('meals')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'image_url' => $category->image_url,
                        'meals_count' => $category->meals_count,
                        'sort_order' => $category->sort_order,
                        'created_at' => $category->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
