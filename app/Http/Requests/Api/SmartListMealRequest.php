<?php

namespace App\Http\Requests\Api;

use App\DTOs\Api\SmartListMealData;
use Illuminate\Foundation\Http\FormRequest;

class SmartListMealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meal_id'=>['required','integer','exists:meals,id'],
        ];
    }

     public function toDto(): SmartListMealData
    {
        return SmartListMealData::fromValidated($this->validated());
    }

}
