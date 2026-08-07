<?php

namespace App\Actions\Api\Address;

use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Models\Address;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\DB;

class AddressAction
{
    public function store(StoreAddressRequest $request): Address
    {
        $data = $this->preparePayload($request->validated(), $request->user(), true);

        return $request->user()->addresses()->create($data);
    }

    public function update(UpdateAddressRequest $request, Address $address): Address
    {
        $address->update($this->preparePayload($request->validated(), $request->user(), false));

        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $wasDefault = $address->is_default;
        $user = $address->user;

        DB::transaction(function () use ($address, $user, $wasDefault): void {
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->oldest()->first()?->update(['is_default' => true]);
            }
        });
    }

    public function setDefault(Address $address): Address
    {
        if (! $address->is_default) {
            $address->update(['is_default' => true]);
        }

        return $address->fresh();
    }

    private function preparePayload(array $data, User $user, bool $isCreate): array
    {
        if ($isCreate) {
            $data['is_default'] = ($data['is_default'] ?? false) || $user->addresses()->doesntExist();
        }

        return PhoneNormalizer::normalize($data);
    }
}
