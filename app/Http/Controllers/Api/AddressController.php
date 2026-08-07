<?php

namespace App\Http\Controllers\Api;

use App\Actions\Address\DeleteAddressAction;
use App\Actions\Address\SetDefaultAddressAction;
use App\Actions\Address\StoreAddressAction;
use App\Actions\Address\UpdateAddressAction;
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

    /**
     * Get all user addresses
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->allFiles() !== []) {
            return $this->validationError(
                ['files' => ['Remove file attachments from the request.']],
                'This endpoint does not accept file uploads.'
            );
        }

        $addresses = $request->user()->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'     => true,
            'message'     => 'Addresses retrieved successfully',
            'data'        => AddressResource::collection($addresses),
            'total_count' => $addresses->count(),
        ]);
    }

    /**
     * Get single address
     */
    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorize('view', $address);

        return $this->success(
            new AddressResource($address),
            'Address retrieved successfully'
        );
    }

    /**
     * Create new address
     */
    public function store(StoreAddressRequest $request, StoreAddressAction $action): JsonResponse
    {
        $address = $action->execute($request->user(), $request->validated());

        return $this->created(
            new AddressResource($address),
            'Address created successfully'
        );
    }

    /**
     * Update address
     */
    public function update(UpdateAddressRequest $request, Address $address, UpdateAddressAction $action): JsonResponse
    {
        $this->authorize('update', $address);

        $updated = $action->execute($address, $request->validated());

        return $this->success(
            new AddressResource($updated),
            'Address updated successfully'
        );
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, Address $address, DeleteAddressAction $action): JsonResponse
    {
        $this->authorize('delete', $address);

        $action->execute($request->user(), $address);

        return $this->success(null, 'Address deleted successfully');
    }

    /**
     * Set address as default
     */
    public function setDefault(Request $request, Address $address, SetDefaultAddressAction $action): JsonResponse
    {
        $this->authorize('update', $address);

        if ($address->is_default) {
            return response()->json([
                'success'         => true,
                'message'         => 'This address is already your default.',
                'already_default' => true,
                'data'            => new AddressResource($address),
            ]);
        }

        $updated = $action->execute($request->user(), $address);

        return $this->success(
            new AddressResource($updated),
            'Default address updated successfully'
        );
    }
}
