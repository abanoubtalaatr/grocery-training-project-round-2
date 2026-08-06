<?php

namespace App\Http\Controllers\Api;
use App\Action\Api\AddMealToSmartListAction;
use App\Action\Api\CreateSmartListAction;
use App\Action\Api\DeleteSmartListAction;
use App\Action\Api\RemoveMealFromSmartListAction;
use App\Action\Api\UpdateSmartListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)
            ->with('meals')
            ->get();

        return $this->success(SmartListResource::collection($smartLists),'Smart lists retrieved successfully');
    }

    public function store(SmartListRequest $request, CreateSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $request->validated(), $request);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Wish list created successfully',201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $smartList = SmartList::where('user_id', $request->user()->id)
            ->with('meals')
            ->findOrFail($id);

        return $this->success(new SmartListResource($smartList),'Smart list retrieved successfully');
    }

    public function update(SmartListRequest $request, string $id, UpdateSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $id, $request->validated(), $request);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Wish list updated successfully');
    }

    public function destroy(Request $request, string $id, DeleteSmartListAction $action): JsonResponse
    {
        $action->execute($request->user(), $id);

        return $this->success(null, 'Wish list deleted successfully');
    }

    public function addMeal(Request $request, string $id, AddMealToSmartListAction $action): JsonResponse
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);

        $smartList = $action->execute($request->user(), $id, $request->meal_id);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Item added to wish list successfully');
    }

    public function removeMeal(Request $request, string $id, string $mealId, RemoveMealFromSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $id, $mealId);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList), 'Item removed from wish list successfully');
    }
}
