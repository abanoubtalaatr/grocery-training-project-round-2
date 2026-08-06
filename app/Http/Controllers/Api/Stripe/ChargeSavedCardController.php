<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Actions\Api\Stripe\ChargeSavedCardAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChargeSavedCardRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ChargeSavedCardController extends Controller
{
    use ApiTrait;

    public function __invoke(ChargeSavedCardRequest $request, ChargeSavedCardAction $action): JsonResponse
    {
        $paymentIntent = $action->run($request->user(), $request->validated());

        return $this->dataResponse([
            'status' => 'success',
            'payment_intent' => $paymentIntent,
        ], 'Card charged successfully');
    }
}
