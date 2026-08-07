<?php

namespace App\Actions\Api\Addresses;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreAddressAction
{
    public function execute(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            $data = $this->normalizePhone($data);
            $data['is_default'] = (bool) ($data['is_default'] ?? false) || $user->addresses()->count() === 0;

            return $user->addresses()->create($data);
        });
    }

    private function normalizePhone(array $data): array
    {
        $phone = trim((string) ($data['phone'] ?? ''));
        $code = trim((string) ($data['country_code'] ?? ''));

        if ($code !== '' && str_starts_with($phone, $code)) {
            $data['phone'] = substr($phone, strlen($code));
        }

        return $data;
    }
}
