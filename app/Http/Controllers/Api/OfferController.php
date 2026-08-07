<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Offers\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Offer::active()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('min_purchase'), function ($query) use ($request) {
                $query->where('minimum_purchase', '<=', $request->input('min_purchase'))
                    ->orWhereNull('minimum_purchase');
            })
            ->when($request->boolean('featured'), fn ($query) => $query->featured())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });

        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $offers = $query->orderBy($orderBy, $orderDirection)
            ->paginate($request->integer('per_page', 15));

        return $this->paginatedResponse(
            $offers,
            OfferResource::collection($offers),
            'Offers retrieved successfully'
        );
    }

    public function featured(): JsonResponse
    {
        $offers = Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse(
            OfferResource::collection($offers),
            'Featured offers retrieved successfully'
        );
    }

    public function showByCode(string $code): JsonResponse
    {
        return $this->successResponse(
            new OfferResource(Offer::where('code', $code)->firstOrFail()),
            'Offer retrieved successfully'
        );
    }

    public function validateOffer(ValidateOfferRequest $request, ValidateOfferAction $validateOffer): JsonResponse
    {
        $result = $validateOffer->execute($request->validated());

        if (!$result['valid'] && $result['status'] === 404) {
            return $this->errorResponse($result['message'], 404);
        }

        return $this->successResponse([
            'valid' => $result['valid'],
            'offer' => new OfferResource($result['offer']),
            'discount_amount' => $result['discount_amount'],
        ], $result['message']);
    }
}
