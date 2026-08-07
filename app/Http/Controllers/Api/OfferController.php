<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetFeaturedOffersAction;
use App\Action\Api\GetOfferByCodeAction;
use App\Action\Api\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use App\Models\Offer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Offer::active();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_purchase')) {
            $query->where('minimum_purchase', '<=', $request->min_purchase)
                ->orWhereNull('minimum_purchase');
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 15);
        $offers = $query->paginate($perPage);

        return $this->success(OfferResource::collection($offers),'Offers retrieved successfully');
    }

    public function featured(GetFeaturedOffersAction $action): JsonResponse
    {
        $offers = $action->execute();

        return $this->success(OfferResource::collection($offers),'Featured offers retrieved successfully');
    }

    public function showByCode(string $code, GetOfferByCodeAction $action): JsonResponse
    {
        $offer = $action->execute($code);

        return $this->success(new OfferResource($offer),'Offer retrieved successfully');
    }

    public function validateOffer(ValidateOfferRequest $request, ValidateOfferAction $action): JsonResponse
    {
        $result = $action->execute($request->code, $request->amount);

        return $this->success(
            [
                'valid' => $result['valid'],
                'offer' => $result['offer'] ? new OfferResource($result['offer']) : null,
                'discount_amount' => $result['discount_amount'],
            ],
            $result['message']
        );
    }
}
