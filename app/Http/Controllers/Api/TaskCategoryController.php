<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Api\TaskCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class TaskCategoryController extends Controller
{
    /**
     * جلب جميع التصنيفات (GET Method)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = Category::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Categories retrieved successfully via Task API',
                'data'    => TaskCategoryResource::collection($categories),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve categories',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


public function show(Category $category): JsonResponse
    {
        try {
            return response()->json([
                'status'  => true,
                'message' => 'Category retrieved successfully',
                'data'    => new TaskCategoryResource($category),
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An unexpected error occurred while fetching the category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name'        => 'required|string|max:255',
                'slug'        => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image'       => 'nullable|string',
                'is_active'   => 'nullable|boolean',
                'sort_order'  => 'nullable|integer',
            ]);

            $category = Category::create($validatedData);

            return response()->json([
                'status'  => true,
                'message' => 'Category created successfully via Task API',
                'data'    => new TaskCategoryResource($category),
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create category',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }
}