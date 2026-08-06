<?php

namespace App\Action\Api;

use App\Models\Faq;

class DeleteFaqAction
{
    public function execute(Faq $faq): void
    {
        $faq->delete();
    }
}
