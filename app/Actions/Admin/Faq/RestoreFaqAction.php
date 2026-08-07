<?php

namespace App\Actions\Admin\Faq;

use App\Models\Faq;

class RestoreFaqAction
{
    public function run(int $id): Faq
    {
        $faq = Faq::withTrashed()->findOrFail($id);
        $faq->restore();

        return $faq;
    }
}
