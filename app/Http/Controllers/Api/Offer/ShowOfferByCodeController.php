<?php

namespace App\Http\Controllers\Api\Offer;

use App\Actions\Api\Offer\ShowOfferByCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ShowOfferByCodeController extends Controller
{
    use ApiTrait;

    public function __invoke(string $code, ShowOfferByCodeAction $action): JsonResponse
    {
        $offer = $action->run($code);

        return $this->dataResponse(new OfferResource($offer));
    }
}
