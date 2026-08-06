<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateStripeCheckoutSessionAction;
use App\Action\Api\VerifyStripeSessionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateStripeCheckoutSessionRequest;
use App\Http\Resources\Api\StripeCheckoutResource;
use App\Http\Resources\Api\StripeVerificationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    use ApiResponse;

    public function store(CreateStripeCheckoutSessionRequest $request, CreateStripeCheckoutSessionAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return $this->success(
            new StripeCheckoutResource($result),
            'Checkout session created'
        );
    }

    public function verifySession(Request $request, string $sessionId, VerifyStripeSessionAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $sessionId);

        return $this->success(new StripeVerificationResource($result),$result['message']);
    }
}
