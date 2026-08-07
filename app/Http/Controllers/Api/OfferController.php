<?php

namespace App\Http\Controllers\Api;

use App\Actions\Offer\FeaturedOffersAction;
use App\Actions\Offer\GetOffersAction;
use App\Actions\Offer\ShowOfferByCodeAction;
use App\Actions\Offer\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        protected GetOffersAction $getOffersAction,
        protected FeaturedOffersAction $featuredOffersAction,
        protected ShowOfferByCodeAction $showOfferByCodeAction,
        protected ValidateOfferAction $validateOfferAction,
    ) {}

    /**
     * Get all active offers
     */
    public function index(Request $request)
    {
        return OfferResource::collection(
            $this->getOffersAction->execute($request)
        );
    }

    /**
     * Get featured offers
     */
    public function featured()
    {
        return OfferResource::collection(
            $this->featuredOffersAction->execute()
        );
    }

    /**
     * Get offer by code
     */
    public function showByCode(string $code)
    {
        return new OfferResource(
            $this->showOfferByCodeAction->execute($code)
        );
    }

    /**
     * Validate offer
     */
    public function validateOffer(ValidateOfferRequest $request)
    {
        $result = $this->validateOfferAction->execute(
            $request->validated()
        );

        if (! $result['offer']) {
            return response()->json([
                'valid' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'valid' => $result['valid'],
            'offer' => new OfferResource($result['offer']),
            'discount_amount' => $result['discount'],
            'message' => $result['message'],
        ]);
    }
}