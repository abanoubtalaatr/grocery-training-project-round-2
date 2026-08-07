<?php

namespace App\Http\Requests\Admin\Support;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupportStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => ['required', 'in:new,read,replied,spam'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
