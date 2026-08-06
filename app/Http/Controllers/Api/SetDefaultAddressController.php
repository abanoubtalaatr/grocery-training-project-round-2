<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\SetDefaultAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, Address $address, SetDefaultAddressAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $address);

        return $this->success( new AddressResource($result['address']),$result['message']);
    }
}
