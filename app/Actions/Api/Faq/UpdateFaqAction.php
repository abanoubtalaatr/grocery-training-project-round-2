<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;

class UpdateFaqAction
{
    public function run(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq;
    }
}
