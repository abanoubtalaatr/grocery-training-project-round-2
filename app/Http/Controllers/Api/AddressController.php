<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\StoreAddressAction;
use App\Action\Api\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Http\Resources\Api\AdressResource;

class AddressController extends Controller
{
    use ApiResponse;
    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $addresses = $user->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($address) {
                return $this->formatAddress($address);
            });

        return $this->success(AdressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    /**
     * Get single address
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return $this->success(new AdressResource($address), 'Address retrieved successfully');
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    {
        $address = $action->execute($request->validated());

        return $this->success(new AdressResource($address), 'Address created successfully', 201);
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request, Address $address, UpdateAddressAction $action): JsonResponse
    {
        $action->execute($address, $request->validated());

        return $this->success(new AdressResource($address), 'Address updated successfully');
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorize('delete', $address);
        $address->delete();
        
        return $this->success(null, 'Address deleted successfully');
    }
}
