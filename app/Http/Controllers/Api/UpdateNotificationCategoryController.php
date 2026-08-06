<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\NotificationSettings\UpdateNotificationCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationCategoryRequest;
use App\Http\Resources\Api\NotificationSettingResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class UpdateNotificationCategoryController extends Controller
{
    use ApiTrait;

    public function __invoke(UpdateNotificationCategoryRequest $request, string $category, UpdateNotificationCategoryAction $action): JsonResponse
    {
        try {
            $settings = $action->run($request->user(), $category, (bool) $request->validated('enabled'));

            return $this->dataResponse(new NotificationSettingResource($settings), 'Notification settings updated successfully');
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse([], $e->getMessage(), 400);
        }
    }
}
