<?php

namespace App\Actions\Address;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DestroyAddressAction
{
    public function execute(User $user, string $id): void
    {
        DB::transaction(function () use ($user, $id) {

            $address = $user->addresses()->findOrFail($id);

            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $newDefault = $user->addresses()->first();

                if ($newDefault) {
                    $newDefault->update([
                        'is_default' => true,
                    ]);
                }
            }
        });
    }
}