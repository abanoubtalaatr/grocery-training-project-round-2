<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Address\AddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    public function __construct(protected AddressAction $addressAction) {}

    /**
     * Get all user addresses.
     */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            AddressResource::collection($addresses),
            'Addresses retrieved successfully',
        );
    }

    /**
     * Get single address.
     */
    public function show(Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return $this->success(
            new AddressResource($address),
            'Address retrieved successfully',
        );
    }

    /**
     * Create a new address.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressAction->store($request);

        return $this->success(
            new AddressResource($address),
            'Address created successfully',
        );
    }

    /**
     * Update an address.
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address = $this->addressAction->update($request, $address);

        return $this->success(
            new AddressResource($address),
            'Address updated successfully',
        );
    }

    /**
     * Delete an address.
     */
    public function destroy(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $this->addressAction->delete($address);

        return $this->success(message: 'Address deleted successfully');
    }

    /**
     * Set an address as the default address.
     */
    public function setDefault(Address $address): JsonResponse
    {
        $this->authorize('set-default', $address);

        $wasAlreadyDefault = $address->is_default;
        $address = $this->addressAction->setDefault($address);

        return $this->success(
            new AddressResource($address),
            $wasAlreadyDefault
                ? 'This address is already your default'
                : 'Default address updated successfully',
        );
    }
}