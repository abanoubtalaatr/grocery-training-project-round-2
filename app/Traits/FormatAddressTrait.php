<?php

namespace App\Traits;

use App\Models\Address;

trait FormatAddressTrait
{
    protected function formatAddress(Address $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label ?? null,
            'address_line' => $address->address_line ?? null,
            'city' => $address->city ?? null,
            'state' => $address->state ?? null,
            'zip' => $address->zip ?? null,
            'is_default' => (bool) $address->is_default,
        ];
    }
}