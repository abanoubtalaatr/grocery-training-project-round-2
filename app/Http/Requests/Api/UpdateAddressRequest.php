<?php

namespace App\Http\Requests\Api;

class UpdateAddressRequest extends StoreAddressRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach ($rules as $field => $fieldRules) {
            $rules[$field] = array_values(array_filter($fieldRules, fn ($rule) => $rule !== 'required'));

            if (!in_array('nullable', $rules[$field], true) && in_array($field, ['label', 'country_code', 'building_number', 'floor', 'apartment', 'landmark', 'state', 'postal_code', 'country', 'notes', 'latitude', 'longitude'], true)) {
                array_unshift($rules[$field], 'nullable');
            }

            array_unshift($rules[$field], 'sometimes');
        }

        return $rules;
    }
}
