<?php

namespace App\Http\Controllers\Api\Setting;

use App\Actions\Api\Setting\GetPublicSettingsAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetPublicSettingsController extends Controller
{
    use ApiTrait;

    public function __invoke(GetPublicSettingsAction $action): JsonResponse
    {
        $publicSettings = $action->run();

        return $this->dataResponse($publicSettings);
    }
}
