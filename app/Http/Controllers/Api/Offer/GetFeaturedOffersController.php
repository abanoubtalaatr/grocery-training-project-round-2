<?php

namespace App\Http\Controllers\Api\Offer;

use App\Actions\Api\Offer\GetFeaturedOffersAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetFeaturedOffersController extends Controller
{
    use ApiTrait;

    public function __invoke(GetFeaturedOffersAction $action): JsonResponse
    {
        $offers = $action->run();

        return $this->dataResponse(OfferResource::collection($offers));
    }
}
