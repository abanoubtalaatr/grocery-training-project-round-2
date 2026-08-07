<?php

namespace App\Actions\Api\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class DestroyAddressAction
{
    public function execute(Address $address): void
    {
        DB::transaction(function () use ($address) {
            $user = $address->user;
            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $newDefault = $user->addresses()->first();
                $newDefault?->update(['is_default' => true]);
            }
        });
    }
}
