<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Address\SetDefaultAddressAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AdressResource;
use App\Models\Address;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    use ApiTrait;
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Address $address, SetDefaultAddressAction $action)
    {
        $data = $action->run($request, $address);
        if ($data['already_default']) {
            return $this->dataResponse(new AdressResource($data['address']), 'This address is already your default.');
        }
        return $this->dataResponse(new AdressResource($data['address']), 'Default address updated successfully');
    }
}
