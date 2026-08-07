<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressController extends Controller
{
    public function __invoke(Request $request, Address $address)
    {
        try {
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

            DB::beginTransaction();

            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully',
                'data' => $this->formatAddress($address->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}