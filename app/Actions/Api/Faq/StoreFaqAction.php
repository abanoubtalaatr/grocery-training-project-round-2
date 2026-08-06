<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;

class StoreFaqAction
{
    public function run(array $data): Faq
    {
        return Faq::create($data);
    }
}
