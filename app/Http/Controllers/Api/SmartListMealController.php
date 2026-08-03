<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartListMealAction;
use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListMealRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\Meal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SmartListMealController extends Controller
{
    use ApiResponse;

    public function store(SmartListMealRequest $request, SmartListMealAction $action, SmartList $smartList): JsonResponse
    {
        $updatedList = $action->execute($smartList, $request->toDto());
        return $this->success(new SmartListResource($updatedList), 'Item added to Smart list successfully', 201);
    }


    public function destroy(SmartList $smartList, Meal $meal, SmartListMealAction $action): JsonResponse
    {
        $this->authorize('update', $smartList);
        $updatedList = $action->remove($smartList, $meal->id);
        return $this->success(new SmartListResource($updatedList), 'Item removed from Smart list successfully');
    }
}
