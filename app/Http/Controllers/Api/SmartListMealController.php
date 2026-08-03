<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SmartListMealAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListMealRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Models\Meal;
use App\Models\SmartList;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SmartListMealController extends Controller
{
    use ApiResponse;

    public function store(SmartListMealRequest $request, SmartListMealAction $action, SmartList $smartList): JsonResponse
    {
        $this->authorize('update', $smartList);
        
        $result = $action->execute($smartList, $request->toDto());

        $statusCode = $result->isNew ? 201 : 200;
        $message = $result->isNew 
            ? 'Item added to Smart list successfully' 
            : 'Item already exists in Smart list';

        return $this->success(new SmartListResource($result->smartList), $message, $statusCode);
    }

    public function destroy(SmartList $smartList, Meal $meal, SmartListMealAction $action): JsonResponse
    {
        $this->authorize('update', $smartList);
        
        $updatedList = $action->remove($smartList, $meal->id);
        
        if ($updatedList === null) {
            return $this->error('This meal is not in the smart list', 404);
        }

        return $this->success(new SmartListResource($updatedList), 'Item removed from Smart list successfully');
    }
}