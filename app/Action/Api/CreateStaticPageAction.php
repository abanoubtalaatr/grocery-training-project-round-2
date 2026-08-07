<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class CreateStaticPageAction
{
    public function execute(array $data): StaticPage
    {
        return StaticPage::create($data);
    }
}