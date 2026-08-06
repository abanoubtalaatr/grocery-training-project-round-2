<?php

namespace App\Action\Api;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute($user, Address $address): array
    {
        if ($address->user_id !== $user->id) {
            abort(404, 'Address not found');
        }

        if ($address->is_default) {
            return [
                'address' => $address,
                'message' => 'This address is already your default.',
            ];
        }

        DB::beginTransaction();

        try {
            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            $address->update(['is_default' => true]);

            DB::commit();

            return [
                'address' => $address->fresh(),
                'message' => 'Default address updated successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}