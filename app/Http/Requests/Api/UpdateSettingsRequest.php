<?php

namespace App\Http\Requests\Api;

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
            'facebook' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'whatsapp' => 'nullable|string|max:50',
            'tiktok' => 'nullable|url|max:255',
            'snapchat' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'store_address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'site_name' => 'nullable|string|max:100',
            'site_description' => 'nullable|string|max:1000',
            'copyright_text' => 'nullable|string|max:255',
            'store_status' => 'nullable|string|in:open,closed,maintenance',
            'maintenance_mode' => 'nullable|boolean',
            'store_hours' => 'nullable|string|max:255',
            'currency_code' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0',
            'payment_methods' => 'nullable|array',
            'shipping_note' => 'nullable|string|max:500',
            'shipping_fee' => 'nullable|numeric|min:0',
            'free_shipping_min_order' => 'nullable|numeric|min:0',
            'locale' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
        ];
    }
}
