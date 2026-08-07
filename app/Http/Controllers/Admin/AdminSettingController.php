<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Setting\GetSettingsAction;
use App\Action\Admin\Setting\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Resources\Admin\SettingResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    use ApiResponse;

    public function index(GetSettingsAction $action): JsonResponse
    {
        $settings = $action->execute();

        return $this->success(new SettingResource($settings),'Settings retrieved successfull');
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action): JsonResponse
    {
        $settings = $action->execute($request->validated(), $request);

        return $this->success(new SettingResource($settings),'Settings updated successfully');
    }
}
