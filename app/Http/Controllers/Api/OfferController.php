<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ValidateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ValidateOfferRequest;
use App\Http\Resources\Api\OfferResource;
use App\Models\Offer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponse;

    // Get all active offers
    public function index(Request $request)
    {
        $query = Offer::active();
        
        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by minimum purchase
        if ($request->has('min_purchase')) {
            $query->where('minimum_purchase', '<=', $request->min_purchase)
                  ->orWhereNull('minimum_purchase');
        }
        
        // Featured offers only
        if ($request->boolean('featured')) {
            $query->featured();
        }
        
        // Search by title or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $offers = $query->paginate($perPage);
        
        return OfferResource::collection($offers);
    }

    // Get featured offers
    public function featured()
    {
        $offers = Offer::featured()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return OfferResource::collection($offers);
    }

    // Get offer by code
    public function showByCode($code)
    {
        $offer = Offer::where('code', $code)->firstOrFail();
        
        return new OfferResource($offer);
    }

    // Validate offer code
    public function validateOffer(ValidateOfferRequest $request, ValidateOfferAction $action)
    {
        $result = $action->execute($request->input('code'), $request->input('amount'));

        if (! $result['offer']) {
            return $this->error('Invalid offer code', 404);
        }

        return $this->success([
            'valid' => $result['valid'],
            'offer' => new OfferResource($result['offer']),
            'discount_amount' => $result['discount'],
            'message' => $result['message'],
        ], 'Offer validation result');
    }
}
