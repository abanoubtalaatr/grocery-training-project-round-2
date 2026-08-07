<?php

namespace App\Actions\Api\Addresses;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class UpdateAddressAction
{
    public function execute(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $address->fill($this->normalizePhone($address, $data))->save();

            return $address->fresh();
        });
    }

    private function normalizePhone(Address $address, array $data): array
    {
        if (!array_key_exists('phone', $data)) {
            return $data;
        }

        $phone = trim((string) $data['phone']);
        $code = trim((string) ($data['country_code'] ?? $address->country_code ?? ''));

        if ($code !== '' && str_starts_with($phone, $code)) {
            $data['phone'] = substr($phone, strlen($code));
        }

        return $data;
    }
}
