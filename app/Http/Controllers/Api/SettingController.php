<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetPublicSettingsAction;
use App\Action\Api\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Http\Resources\Api\PublicSettingsResource;
use App\Http\Resources\Api\SettingResource;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $settings = Setting::getSettings();

        return $this->success(new SettingResource($settings),'Settings retrieved successfully');
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action): JsonResponse
    {
        $settings = $action->execute(Setting::getSettings(), $request->validated(), $request);

        return $this->success(new SettingResource($settings),'Settings updated successfully');
    }

    public function publicSettings(GetPublicSettingsAction $action): JsonResponse
    {
        $settings = $action->execute();

        return $this->success(new PublicSettingsResource($settings),'Public settings retrieved successfully');
    }
}
