<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartListAction;
use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Http\Requests\Api\CreateSmartListRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SmartListController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SmartList::class);

        $perPage = max(1, min($request->integer('per_page', 15), 100));
        $smartLists = $request->user()
            ->smartLists()
            ->with('meals')
            ->withCount('meals')
            ->paginate($perPage);
        return $this->success(SmartListResource::collection($smartLists), 'Smart lists retrieved successfully');
    }

    public function store(CreateSmartListRequest $request, SmartListAction $action): JsonResponse
    {
        $this->authorize('create', SmartList::class);
        $smartList = $action->create($request->user(), $request->toDto());
        return $this->success(new SmartListResource($smartList), 'Smart list created successfully', 201);
    }

    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);

        $smartList->load('meals')->loadCount('meals');
        return $this->success(new SmartListResource($smartList), 'Smart list retrieved successfully');
    }

    public function update(UpdateSmartListRequest $request, SmartListAction $action, SmartList $smartList): JsonResponse
    {
        $this->authorize('update', $smartList);
        $updatedList = $action->update($smartList, $request->toDto());
        return $this->success(new SmartListResource($updatedList), 'Smart list updated successfully');
    }

    public function destroy(SmartList $smartList, SmartListAction $action): JsonResponse
    {
        $this->authorize('delete', $smartList);

        $action->delete($smartList);

        return $this->success([], 'Smart list deleted successfully');
    }
}
