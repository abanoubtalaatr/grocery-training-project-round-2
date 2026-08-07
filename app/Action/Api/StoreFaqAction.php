<?php

namespace App\Action\Api;

use App\Models\Faq;

class StoreFaqAction
{
    public function execute(array $data): Faq
    {
        return Faq::create($data);
    }
}