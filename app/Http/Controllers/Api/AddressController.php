<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Addresses\DestroyAddressAction;
use App\Actions\Api\Addresses\SetDefaultAddressAction;
use App\Actions\Api\Addresses\StoreAddressAction;
use App\Actions\Api\Addresses\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(
            AddressResource::collection($addresses),
            'Addresses retrieved successfully',
            200,
            ['total_count' => $addresses->count()]
        );
    }

    public function show(Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return $this->successResponse(
            new AddressResource($address),
            'Address retrieved successfully'
        );
    }

    public function store(StoreAddressRequest $request, StoreAddressAction $storeAddress): JsonResponse
    {
        $address = $storeAddress->execute($request->user(), $request->validated());

        return $this->successResponse(
            new AddressResource($address),
            'Address created successfully',
            201
        );
    }

    public function update(UpdateAddressRequest $request, Address $address, UpdateAddressAction $updateAddress): JsonResponse
    {
        $this->authorize('update', $address);

        $address = $updateAddress->execute($address, $request->validated());

        return $this->successResponse(
            new AddressResource($address),
            'Address updated successfully'
        );
    }

    public function destroy(Address $address, DestroyAddressAction $destroyAddress): JsonResponse
    {
        $this->authorize('delete', $address);

        $destroyAddress->execute($address);

        return $this->successResponse(null, 'Address deleted successfully');
    }

    public function setDefault(Address $address, SetDefaultAddressAction $setDefaultAddress): JsonResponse
    {
        $this->authorize('update', $address);

        $wasDefault = $address->is_default;
        $address = $setDefaultAddress->execute($address);

        return $this->successResponse(
            new AddressResource($address),
            $wasDefault ? 'This address is already your default.' : 'Default address updated successfully',
            200,
            ['already_default' => $wasDefault]
        );
    }
}
