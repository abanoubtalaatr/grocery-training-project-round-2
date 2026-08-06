<?php

namespace App\Http\Controllers\Api;

use App\Actions\Address\DeleteAddressAction;
use App\Actions\Address\GetAllAddressesAction;
use App\Actions\Address\GetSingleAddressAction;
use App\Actions\Address\SetDefaultAddressAction;
use App\Actions\Address\StoreAddressAction;
use App\Actions\Address\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    /**
     * Get all user addresses
     */
    public function index(Request $request, GetAllAddressesAction $action): JsonResponse
    {
        $addresses = $action->execute($request->user());
        return $this->Success(AddressResource::collection($addresses), 'Addresses retrieved successfully', 200);
    }

    /**
     * Get single address
     */
    public function show(Request $request, string $id, GetSingleAddressAction $action): JsonResponse
    {
        $address = $action->execute($request->user(), $id);
        $this->authorize('view', $address);
        return $this->Success(new AddressResource($address), 'Address retrieved successfully');
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    {
        $address = $action->execute($request->user(), $request->validated());
        return $this->Success(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request, string $id, UpdateAddressAction $action): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $updatedAddress = $action->execute($address, $request->validated());
        return $this->Success(new AddressResource($updatedAddress), 'Address updated successfully');
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, string $id, DeleteAddressAction $action): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $action->execute($request->user(), $address);
        return $this->Success(null, 'Address deleted successfully');
    }
}
