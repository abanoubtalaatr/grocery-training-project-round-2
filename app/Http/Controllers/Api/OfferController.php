<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Offer\GetOffersAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetOffersAction $action)
    {
        $offers = $action->run($request);

        return OfferResource::collection($offers);
    }
}