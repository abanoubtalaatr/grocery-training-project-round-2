<?php

namespace App\Actions\Admin\Faq;

use App\Models\Faq;

class UpdateFaqAction
{
    public function run(Faq $faq, array $data): Faq
    {
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : false;
        $faq->update($data);

        return $faq;
    }
}
