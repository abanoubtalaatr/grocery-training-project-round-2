<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Requests\Api\AddMealRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Traits\ApiResponseTrait;
use App\Services\SmartListService;

class SmartListController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected SmartListService $smartLists)
    {
    }

    public function index(Request $request)
    {
        $smartLists = $this->smartLists->listForUser($request->user()->id);

        return $this->successResponse(
            'Smart lists retrieved successfully',
            SmartListResource::collection($smartLists)
        );
    }

    public function store(SmartListRequest $request)
    {
        $smartList = $this->smartLists->create(
            $request->validated(),
            $request->user()->id,
            $request->file('image'),
            $request->input('meal_ids', [])
        );

        return $this->successResponse('Smart list created successfully', new SmartListResource($smartList));
    }

    public function show(Request $request, int $id)
    {
        $smartList = $this->smartLists->findForUser($request->user()->id, $id, withMeals: true);

        return $this->successResponse('Smart list retrieved successfully', new SmartListResource($smartList));
    }

    public function update(SmartListRequest $request, int $id)
    {
        $smartList = $this->smartLists->findForUser($request->user()->id, $id);

        $smartList = $this->smartLists->update(
            $smartList,
            $request->validated(),
            $request->file('image'),
            $request->has('meal_ids') ? $request->input('meal_ids', []) : null
        );

        return $this->successResponse('Smart list updated successfully', new SmartListResource($smartList));
    }

    public function destroy(Request $request, int $id)
    {
        $smartList = $this->smartLists->findForUser($request->user()->id, $id);
        $this->smartLists->delete($smartList);

        return $this->successResponse('Smart list deleted successfully');
    }

    /**
     * Add a meal to a smart list.
     */
    public function addMeal(AddMealRequest $request, int $id)
    {
        $smartList = $this->smartLists->findForUser($request->user()->id, $id);
        $smartList = $this->smartLists->addMeal($smartList, (int) $request->validated('meal_id'));

        return $this->successResponse('Meal added to smart list successfully', new SmartListResource($smartList));
    }

    /**
     * Remove a meal from a smart list.
     */
    public function removeMeal(Request $request, int $id, int $mealId)
    {
        $smartList = $this->smartLists->findForUser($request->user()->id, $id);
        $smartList = $this->smartLists->removeMeal($smartList, $mealId);

        return $this->successResponse('Meal removed from smart list successfully', new SmartListResource($smartList));
    }
}