<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfferResource;
use App\Models\Offer;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponse;

    /**
     * Get all active offers with filters
     */
    public function index(Request $request): JsonResponse
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
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = $request->get('per_page', 15);
        $offers = $query->paginate($perPage);

        return $this->paginated($offers, 'Offers retrieved successfully');
    }

    /**
     * Get single offer by ID
     */
    public function show(Offer $offer): JsonResponse
    {
        return $this->success(
            new OfferResource($offer),
            'Offer retrieved successfully'
        );
    }

    /**
     * Create new offer
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Offer::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:offers,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:percentage,fixed_amount'],
            'value' => ['required', 'numeric', 'min:0'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $offer = Offer::create($validated);

        return $this->success(
            new OfferResource($offer),
            'Offer created successfully',
            201
        );
    }

    /**
     * Update offer
     */
    public function update(Request $request, Offer $offer): JsonResponse
    {
        $this->authorize('update', $offer);

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'unique:offers,code,'.$offer->id],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'type' => ['sometimes', 'string', 'in:percentage,fixed_amount'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'minimum_purchase' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $offer->update($validated);

        return $this->success(
            new OfferResource($offer),
            'Offer updated successfully'
        );
    }

    /**
     * Delete offer
     */
    public function destroy(Offer $offer): JsonResponse
    {
        $this->authorize('delete', $offer);

        $offer->delete();

        return $this->success(null, 'Offer deleted successfully');
    }
}
