<?php

namespace App\Action\Api;

use App\Models\Faq;

class UpdateFaqAction
{
    public function execute(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq;
    }
}
