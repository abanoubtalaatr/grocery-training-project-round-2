<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AddressResource;
use App\Models\Address;
use App\Traits\ApiResponseTrait;
use App\Traits\FormatAddressTrait;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    use FormatAddressTrait;
    use ApiResponseTrait;
    public function setDefault(Request $request ,string $id)
    {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            if ($address->is_default) {
                return $this->successResponse('This address is already your default',$this->formatAddress($address));
            }
            

            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
            return $this->successResponse('Default address updated successfully',$this->formatAddress($address));
    }
}