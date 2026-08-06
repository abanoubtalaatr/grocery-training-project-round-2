<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class ImportantPagesAction
{
    public function execute()
    {
        return StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()
            ->get(['slug', 'title']);
    }
}
