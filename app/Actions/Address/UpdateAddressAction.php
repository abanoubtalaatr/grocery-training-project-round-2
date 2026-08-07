<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateAddressAction
{
    public function execute(User $user, string $id, array $data): Address
    {
        return DB::transaction(function () use ($user, $id, $data) {

            $address = $user->addresses()->findOrFail($id);

            if (
                isset($data['phone'], $data['country_code']) &&
                $data['country_code'] !== '' &&
                str_starts_with(trim($data['phone']), $data['country_code'])
            ) {
                $data['phone'] = substr(
                    trim($data['phone']),
                    strlen($data['country_code'])
                );
            }

            $address->update($data);

            return $address->fresh();
        });
    }
}