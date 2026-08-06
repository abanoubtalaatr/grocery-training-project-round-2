<?php

namespace App\Http\Requests\Api;

use App\Services\FrequencyService;
use Illuminate\Foundation\Http\FormRequest;

class GetFrequencyMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frequency_type' => 'nullable|string|in:' . implode(',', FrequencyService::VALID_TYPES),
            'subcategory_id' => 'nullable|integer',
        ];
    }
}
