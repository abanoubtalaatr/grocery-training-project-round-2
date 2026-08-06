<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\NotificationSettings\GetNotificationSettingsAction;
use App\Actions\Api\NotificationSettings\UpdateNotificationSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationSettingsRequest;
use App\Http\Resources\Api\NotificationSettingResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    use ApiTrait;

    /**
     * Get user notification settings
     */
    public function index(Request $request, GetNotificationSettingsAction $action): JsonResponse
    {
        $settings = $action->run($request->user());

        return $this->dataResponse(new NotificationSettingResource($settings), 'Notification settings retrieved successfully');
    }

    /**
     * Update notification settings.
     */
    public function update(UpdateNotificationSettingsRequest $request, UpdateNotificationSettingsAction $action): JsonResponse
    {
        $settings = $action->run($request->user(), $request->validated());

        return $this->dataResponse(new NotificationSettingResource($settings), 'Notification settings updated successfully');
    }
}
