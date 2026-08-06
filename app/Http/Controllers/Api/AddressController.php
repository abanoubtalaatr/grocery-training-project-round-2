<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Address\DeleteAddressAction;
use App\Actions\Api\Address\GetAllAdressesAction;
use App\Actions\Api\Address\StoreAdressAction;
use App\Actions\Api\Address\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\Api\AdressResource;
use App\Models\Address;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiTrait;
    /**
     * Get all user addresses
     */
    public function index(Request $request, GetAllAdressesAction $action): JsonResponse
    {
        $addresses = $action->run($request);
        return $this->dataResponse(AdressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    /**
     * Get single address
     */
    public function show(Request $request, Address $address): JsonResponse
    {

        $this->authorize('view', $address);
        return $this->dataResponse(new AdressResource($address), 'Address retrieved successfully');
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAdressAction $action): JsonResponse
    {
        $address = $action->run($request);
        return $this->dataResponse(new AdressResource($address), 'Address created successfully', 201);
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request, Address $address, UpdateAddressAction $action): JsonResponse
    {
        $address = $action->run($request, $address);
        return $this->dataResponse(new AdressResource($address), 'Address updated successfully');
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address, DeleteAddressAction $action): JsonResponse
    {
        $action->run($address);
        return $this->successResponse('Address deleted successfully');
    }
}
