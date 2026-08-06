<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Http\Resources\Api\AdressResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, Address $address)
    {
        $user = $request->user();

        // ensure the address belongs to the authenticated user
        $address = $user->addresses()->where('id', $address->id)->firstOrFail();

        if ($address->is_default) {
            return $this->success(new AdressResource($address), 'This address is already your default');
        }

        DB::transaction(function () use ($user, $address) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return $this->success(new AdressResource($address->fresh()), 'Default address updated successfully');
    }
}