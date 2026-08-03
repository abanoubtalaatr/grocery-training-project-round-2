<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\SmartList;
use App\Services\SmartListService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SmartListService $smartListService
    ) {}

    /**
     * Display a listing of the user's smart lists.
     */
    public function index(Request $request)
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)
            ->with('meals')
            ->get();

        return $this->success(
            SmartListResource::collection($smartLists),
            'Smart lists retrieved successfully'
        );
    }

    /**
     * Store a newly created smart list.
     */
    public function store(SmartListRequest $request)
    {
        $smartList = $this->smartListService->create(
            $request->user()->id,
            $request->validated()
        );

        return $this->success(
            new SmartListResource($smartList),
            'Wish list created successfully',
            201
        );
    }

    /**
     * Display the specified smart list.
     */
    public function show(SmartList $smartList)
    {
        $this->authorize('view', $smartList);

        return $this->success(
            new SmartListResource($smartList->load('meals')),
            'Smart list retrieved successfully'
        );
    }

    /**
     * Update the specified smart list.
     */
    public function update(SmartListRequest $request, SmartList $smartList)
    {
        $this->authorize('update', $smartList);

        $smartList = $this->smartListService->update(
            $smartList,
            $request->validated()
        );

        return $this->success(
            new SmartListResource($smartList),
            'Wish list updated successfully'
        );
    }

    /**
     * Remove the specified smart list.
     */
    public function destroy(SmartList $smartList)
    {
        $this->authorize('delete', $smartList);

        $this->smartListService->delete($smartList);

        return $this->success(null, 'Wish list deleted successfully');
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(Request $request, SmartList $smartList)
    {
        $this->authorize('update', $smartList);

        $request->validate([
            'meal_id' => ['required', 'exists:meals,id'],
        ]);

        $smartList = $this->smartListService->addMeal(
            $smartList,
            (int) $request->meal_id
        );

        return $this->success(
            new SmartListResource($smartList),
            'Item added to wish list successfully'
        );
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(SmartList $smartList, string $mealId)
    {
        $this->authorize('update', $smartList);

        $smartList = $this->smartListService->removeMeal(
            $smartList,
            (int) $mealId
        );

        return $this->success(
            new SmartListResource($smartList),
            'Item removed from wish list successfully'
        );
    }
}