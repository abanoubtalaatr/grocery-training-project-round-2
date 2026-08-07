<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'recent_limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'top_purchases_limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}