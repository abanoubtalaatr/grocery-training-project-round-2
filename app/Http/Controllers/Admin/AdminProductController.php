<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Product\CreateProductAction;
use App\Action\Admin\Product\DeleteProductAction;
use App\Action\Admin\Product\DeleteProductImageAction;
use App\Action\Admin\Product\GetProductsAction;
use App\Action\Admin\Product\GetProductStatsAction;
use App\Action\Admin\Product\ToggleProductAvailabilityAction;
use App\Action\Admin\Product\ToggleProductFeaturedAction;
use App\Action\Admin\Product\UpdateProductAction;
use App\Action\Admin\Product\UploadProductImagesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetProductsAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success(
            [
                'products' => ProductResource::collection($result['products']),
                'pagination' => $result['pagination'],
            ],
            'Products retrieved successfully'
        );
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->execute($request->validated(), $request->file('images'));

        return $this->success(new ProductResource($product->load(['category', 'subcategory'])),'Product created successfully',201);
    }

    public function show(Meal $product): JsonResponse
    {
        return $this->success(new ProductResource($product->load(['category', 'subcategory', 'reviews.user'])),'Product retrieved successfully');
    }

    public function update(UpdateProductRequest $request, Meal $product, UpdateProductAction $action): JsonResponse
    {
        $action->execute($product, $request->validated());

        return $this->success(new ProductResource($product->fresh()->load(['category', 'subcategory'])),'Product updated successfully');
    }

    public function destroy(Meal $product, DeleteProductAction $action): JsonResponse
    {
        $action->execute($product);

        return $this->success(null, 'Product deleted successfully');
    }

    public function toggleAvailability(Meal $product, ToggleProductAvailabilityAction $action): JsonResponse
    {
        $action->execute($product);

        return $this->success(new ProductResource($product->fresh()),'Product availability updated successfully');
    }

    public function toggleFeatured(Meal $product, ToggleProductFeaturedAction $action): JsonResponse
    {
        $action->execute($product);

        return $this->success(new ProductResource($product->fresh()),'Product featured status updated successfully');
    }

    public function uploadImages(Request $request, Meal $product, UploadProductImagesAction $action): JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $images = $action->execute($product, $request->file('images'));

        return $this->success(['images' => $images],'Images uploaded successfully');
    }

    public function deleteImage(Meal $product, string $image, DeleteProductImageAction $action): JsonResponse
    {
        $action->execute($product, $image);

        return $this->success(null, 'Image deleted successfully');
    }

    public function stats(GetProductStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return $this->success($stats, 'Product statistics retrieved successfully');
    }
}
