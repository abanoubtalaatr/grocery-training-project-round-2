<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Traits\FormatAddressTrait;
use Illuminate\Http\Request;

class SetDefaultAddressController extends Controller
{
    use FormatAddressTrait;
    public function setDefault(Request $request ,string $id)
    {
            $user = $request->user();
            $address = $user->addresses()->findOrFail($id);

            if ($address->is_default) {
                return response()->json([
                    'success' => true,
                    'message' => 'This address is already your default.',
                    'already_default' => true,
                    'data' => $this->formatAddress($address),
                ]);
            }

            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully',
                'data' => $this->formatAddress($address->fresh()),
            ]);
    }
}