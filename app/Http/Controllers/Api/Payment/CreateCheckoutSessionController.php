<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Api\Stripe\CreateCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateStripeCheckoutSessionRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class CreateCheckoutSessionController extends Controller
{
    use ApiTrait;

    public function __invoke(CreateStripeCheckoutSessionRequest $request, CreateCheckoutSessionAction $action): JsonResponse
    {
        $result = $action->run($request->user(), $request->validated());

        if (! $result['success']) {
            return $this->errorResponse([], $result['message'], $result['status']);
        }

        return $this->dataResponse(
            $result['data'],
            'Checkout session created. Open checkout_url in your WebView.'
        );
    }
}
