<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSmartListRequest;
use App\Http\Requests\Api\UpdateSmartListRequest;
use App\Http\Resources\Api\SmartListListResource;
use App\Models\SmartList;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    /**
     * Get all smart lists for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $smartLists = $request->user()->smartLists()->paginate(10);

        return $this->paginated($smartLists, 'Smart lists retrieved successfully');
    }

    /**
     * Get single smart list
     */
    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);

        return $this->success(
            new SmartListListResource($smartList),
            'Smart list retrieved successfully'
        );
    }

    /**
     * Create new smart list
     */
    public function store(StoreSmartListRequest $request): JsonResponse
    {
        $smartList = $request->user()->smartLists()->create($request->validated());

        return $this->success(
            new SmartListListResource($smartList),
            'Smart list created successfully',
            201
        );
    }

    /**
     * Update smart list
     */
    public function update(UpdateSmartListRequest $request, SmartList $smartList): JsonResponse
    {
        $this->authorize('update', $smartList);

        $smartList->update($request->validated());

        return $this->success(
            new SmartListListResource($smartList),
            'Smart list updated successfully'
        );
    }

    /**
     * Delete smart list
     */
    public function destroy(SmartList $smartList): JsonResponse
    {
        $this->authorize('delete', $smartList);

        $smartList->delete();

        return $this->success(null, 'Smart list deleted successfully');
    }
}

