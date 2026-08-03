<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryDetailsResource;
use App\Http\Requests\Api\Category\CategoryMealsRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MealDetailsResource;
use App\Interfaces\CategoryServiceInterface;
class CategoryController extends Controller
{
protected CategoryServiceInterface $categoryService;

  public function __construct(CategoryServiceInterface $categoryService)
{
    $this->categoryService = $categoryService;
}
    /**
     * Get all categories
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $categories = $this->categoryService->index();

            return ApiResponse::success(
                CategoryResource::collection($categories),
                'Categories retrieved successfully'
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Failed to retrieve categories',
                $e->getMessage()
            );
        }
    }

    /**
     * Get single category with meals
     */
    public function show(string $id): JsonResponse
    {
        try {

            $category = $this->categoryService->show($id);

            return ApiResponse::success(
                new CategoryDetailsResource($category),
                'Category retrieved successfully'
            );

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                'Category not found',
                null,
                404
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                'Failed to retrieve category',
                $e->getMessage()
            );
        }
    }

    /**
     * Get meals by category (paginated)
     */
    public function meals(
        string $id,
        CategoryMealsRequest $request
    ): JsonResponse {
        try {
            $result = $this->categoryService->meals($id, $request);

            $category = $result['category'];
            $paginator = $result['paginator'];
            $paginator->setCollection(
                MealDetailsResource::collection(
                    $paginator->getCollection()
                )->collection
            );

            $total = $paginator->total();
            return ApiResponse::success(
                array_merge([
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'meals' => $paginator->items(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $total,
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                    ],
                ], $total === 0
                    ? ['empty_message' => 'No products match the applied filters. Try adjusting your filters.']
                    : []),
                $total === 0
                ? 'No products match your filters.'
                : 'Meals retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error(
                'Category not found',
                null,
                404
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to retrieve meals',
                $e->getMessage(),
                500
            );
        }
    }
}
