<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Api\Stripe\VerifyCheckoutSessionAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyCheckoutSessionController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, string $sessionId, VerifyCheckoutSessionAction $action): JsonResponse
    {
        $result = $action->run($request->user(), $sessionId);

        if (! $result['success']) {
            return $this->errorResponse($result['data'] ?? [], $result['message'], $result['status']);
        }

        return $this->dataResponse(
            $result['data'],
            $result['message']
        );
    }
}
