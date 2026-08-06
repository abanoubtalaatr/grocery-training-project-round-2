<?php

namespace App\Action\Api;

use App\Models\Faq;

class CreateFaqAction
{
    public function execute(array $data): Faq
    {
        return Faq::create($data);
    }
}
