<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;
use Illuminate\Support\Collection;

class GetFaqCategoriesAction
{
    public function run(): Collection
    {
        return Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();
    }
}
