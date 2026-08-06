<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;
use Illuminate\Database\Eloquent\Collection;

class GetImportantPagesAction
{
    public function run(): Collection
    {
        return StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);
    }
}
