<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;

class DestroyFaqAction
{
    public function run(Faq $faq): bool
    {
        return (bool) $faq->delete();
    }
}
