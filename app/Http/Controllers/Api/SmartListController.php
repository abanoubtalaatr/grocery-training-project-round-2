<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartListAction;
use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SmartListController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)->with('meals')->get();
        return $this->success(SmartListResource::collection($smartLists), 'Smart lists retrieved successfully');
    }

    public function store(SmartListRequest $request, SmartListAction $action): JsonResponse
    {
        $smartList = $action->handle($request->user(), $request->toDto());
        return $this->success(new SmartListResource($smartList), 'Smart list created successfully', 201);
    }

    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);
        return $this->success(new SmartListResource($smartList->load('meals')), 'Smart list retrieved successfully');
    }

    public function update(SmartListRequest $request, SmartListAction $action, SmartList $smartList): JsonResponse
    {
        $updatedList = $action->handle($request->user(), $request->toDto(), $smartList);
        return $this->success(new SmartListResource($updatedList), 'Smart list updated successfully');
    }

    public function destroy(SmartList $smartList): JsonResponse
    {
        $this->authorize('delete', $smartList);
        $smartList->delete();
        return $this->success([], 'Smart list deleted successfully');
    }

}
