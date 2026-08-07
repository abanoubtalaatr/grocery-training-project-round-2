<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:processing,shipping,out_for_delivery,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
