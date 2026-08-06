<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Actions\Api\Stripe\CreateSetupIntentAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateSetupIntentController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, CreateSetupIntentAction $action): JsonResponse
    {
        $clientSecret = $action->run($request->user());

        return $this->dataResponse(['clientSecret' => $clientSecret]);
    }
}
