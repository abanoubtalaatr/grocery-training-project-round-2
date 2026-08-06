<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Action\Api\ListSmartListsAction;
use App\Action\Api\CreateSmartListAction;
use App\Action\Api\ShowSmartListAction;
use App\Action\Api\UpdateSmartListAction;
use App\Action\Api\DeleteSmartListAction;
use App\Action\Api\AddMealToSmartListAction;
use App\Action\Api\RemoveMealFromSmartListAction;

class SmartListController extends Controller
{
    public function index(Request $request, ListSmartListsAction $action)
    {
        $user = $request->user();
        $result = $action->execute($user, $request);

        return response()->json([
            'success' => true,
            'message' => 'Smart lists retrieved successfully',
            'data' => SmartListResource::collection($result),
        ]);
    }
    public function store(SmartListRequest $request, CreateSmartListAction $action)
    {
        $data = $request->validated();

        // Move uploaded file into $data so action can handle it uniformly
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $smartList = $action->execute($request->user(), $data);

        return response()->json([
            'success' => true,
            'message' => 'Wish list created successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function show(Request $request, $id, ShowSmartListAction $action)
    {
        $smartList = $action->execute($request->user(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Smart list retrieved successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }
    public function update(SmartListRequest $request, $id, UpdateSmartListAction $action)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }

        $smartList = $action->execute($request->user(), $id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Wish list updated successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function destroy(Request $request, $id, DeleteSmartListAction $action)
    {
        $action->execute($request->user(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Wish list deleted successfully',
        ]);
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(\App\Http\Requests\Api\AddMealToSmartListRequest $request, string $id, AddMealToSmartListAction $action)
    {
        $data = $request->validated();
        $smartList = $action->execute($request->user(), $id, (int) $data['meal_id']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to wish list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(Request $request, string $id, string $mealId, RemoveMealFromSmartListAction $action)
    {
        $smartList = $action->execute($request->user(), $id, $mealId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from wish list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }
}
