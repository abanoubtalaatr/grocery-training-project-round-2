<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartLists\AddMealToSmartListAction;
use App\Actions\Api\SmartLists\DestroySmartListAction;
use App\Actions\Api\SmartLists\RemoveMealFromSmartListAction;
use App\Actions\Api\SmartLists\StoreSmartListAction;
use App\Actions\Api\SmartLists\UpdateSmartListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddSmartListMealRequest;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\Meal;
use App\Models\SmartList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $smartLists = SmartList::query()
            ->where('user_id', $request->user()->id)
            ->with('meals')
            ->get();

        return $this->successResponse(
            SmartListResource::collection($smartLists),
            'Smart lists retrieved successfully'
        );
    }

    public function store(SmartListRequest $request, StoreSmartListAction $storeSmartList): JsonResponse
    {
        $smartList = $storeSmartList->execute(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            new SmartListResource($smartList),
            'Wish list created successfully',
            201
        );
    }

    public function show(SmartList $smartList): JsonResponse
    {
        $this->authorize('view', $smartList);

        return $this->successResponse(
            new SmartListResource($smartList->load('meals')),
            'Smart list retrieved successfully'
        );
    }

    public function update(SmartListRequest $request, SmartList $smartList, UpdateSmartListAction $updateSmartList): JsonResponse
    {
        $this->authorize('update', $smartList);

        $smartList = $updateSmartList->execute(
            $smartList,
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(
            new SmartListResource($smartList),
            'Wish list updated successfully'
        );
    }

    public function destroy(SmartList $smartList, DestroySmartListAction $destroySmartList): JsonResponse
    {
        $this->authorize('delete', $smartList);

        $destroySmartList->execute($smartList);

        return $this->successResponse(null, 'Wish list deleted successfully');
    }

    public function addMeal(AddSmartListMealRequest $request, SmartList $smartList, AddMealToSmartListAction $addMeal): JsonResponse
    {
        $this->authorize('update', $smartList);

        $smartList = $addMeal->execute($smartList, (int) $request->validated('meal_id'));

        return $this->successResponse(
            new SmartListResource($smartList),
            'Item added to wish list successfully'
        );
    }

    public function removeMeal(SmartList $smartList, Meal $meal, RemoveMealFromSmartListAction $removeMeal): JsonResponse
    {
        $this->authorize('update', $smartList);

        $smartList = $removeMeal->execute($smartList, $meal);

        return $this->successResponse(
            new SmartListResource($smartList),
            'Item removed from wish list successfully'
        );
    }
}
