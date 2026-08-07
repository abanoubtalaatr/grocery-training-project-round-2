<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Notification\FormatNotificationSettingsAction;
use App\Actions\Api\Notification\UpdateNotificationCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationCategoryRequest;
use App\Http\Requests\Api\UpdateNotificationSettingsRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsController extends Controller
{
    use ApiResponse;

    /**
     * Get user notification settings
     */
    public function index(FormatNotificationSettingsAction $action): JsonResponse
    {
        try {
            $user = Auth::user();
            $settings = $user->initializeNotificationSettings();
            $formatted = $action->execute($settings);

            return $this->success($formatted);
        } catch (\Throwable $e) {
            return $this->success($action->execute(null));
        }
    }

    /**
     * Update notification settings.
     */
    public function update(UpdateNotificationSettingsRequest $request, FormatNotificationSettingsAction $action): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $settings = $user->initializeNotificationSettings();
        $settings->update($validated);

        return $this->success(
            $action->execute($settings->fresh()),
            'Notification settings updated successfully'
        );
    }

    /**
     * Update specific category settings.
     */
    public function updateCategory(
        UpdateNotificationCategoryRequest $request,
        string $category,
        UpdateNotificationCategoryAction $action,
        FormatNotificationSettingsAction $formatAction
    ): JsonResponse {
        $validated = $request->validated();
        $result = $action->execute($category, (bool) $validated['enabled']);

        if (!$result['success']) {
            return $this->error($result['message'], 400);
        }

        return $this->success(
            $formatAction->execute($result['settings']),
            'Notification settings updated successfully'
        );
    }
}
