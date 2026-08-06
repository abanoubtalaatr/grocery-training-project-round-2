<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\StoreAddressAction;
use App\Action\Api\UpdateAddressAction;
use App\Http\Resources\Api\AddressResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Models\Address;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
            $user = $request->user();
            $addresses = $user->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse('Addresses retrieved successfully',AddressResource::collection($addresses));
    }

    /**
     * Get single address
    */
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorize('view', $address);
        return $this->successResponse('Address retrieved successfully', new AddressResource($address));
    }
    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request , StoreAddressAction $action): JsonResponse
    {
        $address = $action->execute($request->validated());

        return $this->successResponse('Address created successfully',new AddressResource($address), 201);
        
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request,UpdateAddressAction $action , Address $address): JsonResponse
    {
        $address = $action->execute($address,$request->validated());

        return $this->successResponse( 'Address updated successfully',new AddressResource($address));
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address): JsonResponse
    {
            $this->authorize('delete', $address);
            $address->delete();

            return $this->successResponse( 'Address deleted successfully',null);
    }

}
