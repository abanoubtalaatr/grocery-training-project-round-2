<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('order'));
    }

    public function rules(): array
    {
        return [
            'notes' => 'required|string|min:3|max:1000',
        ];
    }
}
