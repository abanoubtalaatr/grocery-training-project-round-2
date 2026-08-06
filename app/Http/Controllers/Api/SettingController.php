<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Setting\GetSettingsAction;
use App\Actions\Api\Setting\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ApiTrait;

    /**
     * Get settings
     */
    public function index(GetSettingsAction $action): JsonResponse
    {
        $settings = $action->run();

        return $this->dataResponse(new SettingResource($settings));
    }

    /**
     * Update settings
     */
    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action): JsonResponse
    {
        $settings = $action->run($request->validated());

        return $this->dataResponse(
            new SettingResource($settings),
            'Settings updated successfully'
        );
    }
}