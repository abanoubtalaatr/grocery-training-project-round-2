<?php

namespace App\Actions\Admin\Faq;

use App\Models\Faq;

class StoreFaqAction
{
    public function run(array $data): Faq
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;
        $data['order']     = $data['order'] ?? 0;

        return Faq::create($data);
    }
}
