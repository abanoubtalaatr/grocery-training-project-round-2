<?php

namespace App\Http\Controllers\Api\Offer;

use App\Actions\Api\Offer\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ValidateOfferController extends Controller
{
    use ApiTrait;

    public function __invoke(ValidateOfferRequest $request, ValidateOfferAction $action): JsonResponse
    {
        $result = $action->run(
            $request->validated()['code'],
            $request->validated()['amount'] ?? null
        );

        if (! $result['offer']) {
            return $this->errorResponse([], $result['message'], 404);
        }

        return $this->dataResponse([
            'valid' => $result['valid'],
            'offer' => new OfferResource($result['offer']),
            'discount_amount' => $result['discount_amount'],
            'message' => $result['message'],
        ]);
    }
}
