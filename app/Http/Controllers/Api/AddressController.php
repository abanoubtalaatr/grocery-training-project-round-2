<?php

namespace App\Http\Controllers\Api;

use App\Actions\Address\DestroyAddressAction;
use App\Actions\Address\IndexAddressAction;
use App\Actions\Address\SetDefaultAddressAction;
use App\Actions\Address\ShowAddressAction;
use App\Actions\Address\StoreAddressAction;
use App\Actions\Address\UpdateAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        protected IndexAddressAction $indexAddressAction,
        protected ShowAddressAction $showAddressAction,
        protected StoreAddressAction $storeAddressAction,
        protected UpdateAddressAction $updateAddressAction,
        protected DestroyAddressAction $destroyAddressAction,
        protected SetDefaultAddressAction $setDefaultAddressAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $addresses = $this->indexAddressAction->execute($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Addresses retrieved successfully',
                'data' => AddressResource::collection($addresses),
                'total_count' => $addresses->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve addresses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $address = $this->showAddressAction->execute($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Address retrieved successfully',
                'data' => new AddressResource($address),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        try {
            $address = $this->storeAddressAction->execute(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => new AddressResource($address),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateAddressRequest $request, string $id): JsonResponse
    {
        try {
            $address = $this->updateAddressAction->execute(
                $request->user(),
                $id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => new AddressResource($address),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->destroyAddressAction->execute(
                $request->user(),
                $id
            );

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setDefault(Request $request, string $id): JsonResponse
    {
        try {
            $address = $this->setDefaultAddressAction->execute(
                $request->user(),
                $id
            );

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully',
                'data' => new AddressResource($address),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}