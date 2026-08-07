<?php

namespace App\Actions\Admin\Faq;

use App\Models\Faq;

class DestroyFaqAction
{
    public function run(Faq $faq): void
    {
        $faq->delete();
    }
}
