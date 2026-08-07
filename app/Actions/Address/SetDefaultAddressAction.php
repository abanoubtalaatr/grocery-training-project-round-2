<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetDefaultAddressAction
{
    public function execute(User $user, string $id): Address
    {
        return DB::transaction(function () use ($user, $id) {

            $address = $user->addresses()->findOrFail($id);

            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update([
                    'is_default' => false,
                ]);

            $address->update([
                'is_default' => true,
            ]);

            return $address->fresh();
        });
    }
}