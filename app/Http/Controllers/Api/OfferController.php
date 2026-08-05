<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListOffersRequest;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Action\Offer\ListOffersAction;
use App\Action\Offer\FeaturedOffersAction;
use App\Action\Offer\FindOfferByCodeAction;
use App\Action\Offer\ValidateOfferAction;
use App\Http\Resources\Api\OfferResource;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    public function index(ListOffersRequest $request, ListOffersAction $action): JsonResponse
    {
        $paginator = $action->handle($request->validated());

        return OfferResource::collection($paginator)->response();
    }

    public function featured(FeaturedOffersAction $action): JsonResponse
    {
        $offers = $action->handle();

        return OfferResource::collection($offers)->response();
    }

    public function showByCode(string $code, FindOfferByCodeAction $action): JsonResponse
    {
        $offer = $action->handle($code);

        return (new OfferResource($offer))->response();
    }

    public function validateOffer(ValidateOfferRequest $request, ValidateOfferAction $action): JsonResponse
    {
        $data = $request->validated();

        $result = $action->handle($data['code'], isset($data['amount']) ? (float) $data['amount'] : null);

        if (! $result['offer']) {
            return response()->json([
                'valid' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'valid' => $result['valid'],
            'offer' => new OfferResource($result['offer']),
            'discount_amount' => $result['discount_amount'],
            'message' => $result['message'],
        ]);
    }
}
