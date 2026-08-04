<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmartListResource;
use App\Models\SmartList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SmartListController extends Controller
{

    public function index(): JsonResponse
    {
        $smartLists = auth()->user()->smartLists()->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Smart lists retrieved successfully.',
            'data'    => SmartListResource::collection($smartLists)->response()->getData(true)['data'],
            'meta'    => [
                'current_page' => $smartLists->currentPage(),
                'last_page'    => $smartLists->lastPage(),
                'total'        => $smartLists->total(),
                'per_page'     => $smartLists->perPage(),
            ],
        ], Response::HTTP_OK);
    }

    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);

        return response()->json([
            'success' => true,
            'message' => 'Smart list retrieved successfully.',
            'data'    => new SmartListResource($smartList),
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['nullable', 'boolean'],
            'is_public'   => ['nullable', 'boolean'],
            'is_deleted'  => ['nullable', 'boolean'],
            'is_archived' => ['nullable', 'boolean'],
            'is_pinned'   => ['nullable', 'boolean'],
            'is_favorite' => ['nullable', 'boolean'],
        ]);

        $smartList = auth()->user()->smartLists()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Smart list created successfully.',
            'data'    => new SmartListResource($smartList),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, SmartList $smartList): JsonResponse
    {
        $this->authorize('update', $smartList);

        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['sometimes', 'boolean'],
            'is_public'   => ['sometimes', 'boolean'],
            'is_deleted'  => ['sometimes', 'boolean'],
            'is_archived' => ['sometimes', 'boolean'],
            'is_pinned'   => ['sometimes', 'boolean'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]);

        $smartList->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Smart list updated successfully.',
            'data'    => new SmartListResource($smartList->fresh()),
        ], Response::HTTP_OK);
    }

    public function destroy(SmartList $smartList): JsonResponse
    {
        $this->authorize('delete', $smartList);

        $smartList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Smart list deleted successfully.',
        ], Response::HTTP_OK);
    }
}