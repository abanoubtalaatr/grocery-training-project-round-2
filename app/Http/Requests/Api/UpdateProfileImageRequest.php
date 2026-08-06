<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileImageRequest extends FormRequest
{
    private const PROFILE_SINGLE_IMAGE_MESSAGE = 'Only one profile image is allowed';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (count($this->allFiles()) > 1 || is_array($this->file('image'))) {
                $validator->errors()->add('image', self::PROFILE_SINGLE_IMAGE_MESSAGE);
            }
        });
    }
}
