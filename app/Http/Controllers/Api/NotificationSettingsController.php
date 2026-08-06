<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetNotificationSettingsAction;
use App\Action\Api\UpdateNotificationCategoryAction;
use App\Action\Api\UpdateNotificationSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationCategoryRequest;
use App\Http\Requests\Api\UpdateNotificationSettingsRequest;
use App\Http\Resources\Api\NotificationSettingsResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetNotificationSettingsAction $action): JsonResponse
    {
        $settings = $action->execute($request->user());

        return $this->success(new NotificationSettingsResource($settings),'Notification settings retrieved successfully');
    }

    public function update(UpdateNotificationSettingsRequest $request, UpdateNotificationSettingsAction $action): JsonResponse
    {
        $user = $request->user();
        $settings = $action->execute($user, $request->validated());

        return $this->success(new NotificationSettingsResource($settings),'Notification settings updated successfully');
    }

    public function updateCategory(UpdateNotificationCategoryRequest $request, string $category, UpdateNotificationCategoryAction $action): JsonResponse
    {
        $user = $request->user();
        $settings = $action->execute($user, $category, $request->validated());

        return $this->success(new NotificationSettingsResource($settings),'Notification settings updated successfully');
    }
}
