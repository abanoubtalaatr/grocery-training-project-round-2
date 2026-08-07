<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SendBroadcastNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'type'        => ['nullable', 'string', 'max:50'],
            'target'      => ['required', 'in:all,specific'],
            'user_ids'    => ['required_if:target,specific', 'array'],
            'user_ids.*'  => ['exists:users,id'],
        ];
    }
}
