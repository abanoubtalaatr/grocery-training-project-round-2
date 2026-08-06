<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;

class StoreStaticPageAction
{
    public function run(array $data): StaticPage
    {
        return StaticPage::create($data);
    }
}
