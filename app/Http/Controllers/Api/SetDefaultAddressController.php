<?php

namespace App\Http\Controllers\Api;

use App\Actions\Address\SetDefaultAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    use ApiResponse;

    /**
     * Set address as default
     */
    public function __invoke(Request $request, string $id, SetDefaultAddressAction $action): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);

        if ($address->is_default) {
            return $this->Success(new AddressResource($address), 'This address is already your default.');
        }

        $updatedAddress = $action->execute($request->user(), $address);
        
        return $this->Success(new AddressResource($updatedAddress), 'Default address updated successfully');
    }
}
