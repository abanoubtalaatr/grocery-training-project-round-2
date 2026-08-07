<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name'        => ['nullable', 'string', 'max:255'],
            'support_email'    => ['nullable', 'email', 'max:255'],
            'support_phone'    => ['nullable', 'string', 'max:50'],
            'shipping_fee'     => ['nullable', 'numeric', 'min:0'],
            'tax_rate'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency_symbol'  => ['nullable', 'string', 'max:10'],
            'store_is_open'    => ['nullable', 'boolean'],
        ];
    }
}
