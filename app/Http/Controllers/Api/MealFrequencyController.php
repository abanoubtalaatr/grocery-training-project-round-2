<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Meal\GetFrequencyMealsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetFrequencyMealsRequest;
use App\Http\Resources\Api\MealFrequencyResource;
use App\Services\FrequencyService;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealFrequencyController extends Controller
{
    use ApiTrait;

    public function __invoke(GetFrequencyMealsRequest $request, GetFrequencyMealsAction $action): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return $this->errorResponse([], 'Authentication required to view frequency meals.', 401);
        }

        $frequencyType = $request->input('frequency_type', FrequencyService::FREQUENCY_WEEKLY);
        $subcategoryId = $request->input('subcategory_id');
        $subcategoryId = is_numeric($subcategoryId) ? (int) $subcategoryId : null;

        $meals = $action->run($user, $frequencyType, $subcategoryId);

        $payload = [
            'frequency_type' => $frequencyType,
            'data' => MealFrequencyResource::collection($meals),
        ];

        if ($subcategoryId !== null) {
            $payload['subcategory_id'] = $subcategoryId;
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Frequency meals retrieved successfully',
        ], $payload));
    }
}
