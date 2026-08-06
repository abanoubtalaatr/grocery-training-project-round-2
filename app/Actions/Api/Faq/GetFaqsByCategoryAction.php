<?php

namespace App\Actions\Api\Faq;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class GetFaqsByCategoryAction
{
    public function run(string $category): Collection
    {
        return Faq::active()
            ->category($category)
            ->ordered()
            ->get();
    }
}
