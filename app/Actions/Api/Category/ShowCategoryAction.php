<?php

namespace App\Actions\Api\Category;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class ShowCategoryAction
{
    public function handle(string $id): JsonResponse
    {
        try {
            $category = Category::with(['meals' => function ($query) {
                $query->available()->orderBy('created_at', 'desc');
            }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                    'sort_order' => $category->sort_order,
                    'meals' => $category->meals->map(function ($meal) {
                        return [
                            'id' => $meal->id,
                            'title' => $meal->title,
                            'slug' => $meal->slug,
                            'description' => $meal->description,
                            'image_url' => $meal->image_url,
                            'offer_title' => $meal->offer_title,
                            ...$meal->getApiPriceAttributes(),
                            'rating' => (float) $meal->rating,
                            'rating_count' => (int) $meal->rating_count,
                            'has_offer' => $meal->hasOffer(),
                            'is_featured' => $meal->is_featured,
                            'features' => $meal->features,
                        ];
                    }),
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
