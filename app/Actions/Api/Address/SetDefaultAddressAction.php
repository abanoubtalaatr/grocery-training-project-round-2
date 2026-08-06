<?php

namespace App\Actions\Api\Address;

use App\Models\Address;
use Illuminate\Http\Request;

class SetDefaultAddressAction
{
    public function run(Request $request, Address $address)
    {
        $user = $request->user();
        if ($address->is_default) {
            return [
                'already_default' => true,
                'address' => $address
            ];
        }
        $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return [
            'already_default' => false,
            'address' => $address
        ];
    }
}
