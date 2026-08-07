<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\AddMealToSmartListAction;
use App\Action\Api\CreateSmartListAction;
use App\Action\Api\DeleteSmartListAction;
use App\Action\Api\GetSmartListAction;
use App\Action\Api\GetSmartListsAction;
use App\Action\Api\RemoveMealFromSmartListAction;
use App\Action\Api\UpdateSmartListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddMealToSmartListRequest;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetSmartListsAction $action): JsonResponse
    {
        $smartLists = $action->execute($request->user());

        return $this->success(SmartListResource::collection($smartLists),'Wish lists retrieved successfully');
    }

    public function store(SmartListRequest $request, CreateSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Wish list created successfully',201);
    }

    public function show(Request $request, string $id, GetSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $id);

        return $this->success(new SmartListResource($smartList),'Wish list retrieved successfully');
    }

    public function update(SmartListRequest $request, string $id, UpdateSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute(
            $request->user(),
            $id,
            $request->validated(),
            $request->file('image')
        );
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Wish list updated successfully');
    }

    public function destroy(Request $request, string $id, DeleteSmartListAction $action): JsonResponse
    {
        $action->execute($request->user(), $id);

        return $this->success(null, 'Wish list deleted successfully');
    }

    public function addMeal(AddMealToSmartListRequest $request, string $id, AddMealToSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $id, $request->meal_id);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Item added to wish list successfully');
    }

    public function removeMeal(Request $request, string $id, string $mealId, RemoveMealFromSmartListAction $action): JsonResponse
    {
        $smartList = $action->execute($request->user(), $id, $mealId);
        $smartList->load('meals');

        return $this->success(new SmartListResource($smartList),'Item removed from wish list successfully');
    }
}
