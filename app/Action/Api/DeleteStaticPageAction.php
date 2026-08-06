<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class DeleteStaticPageAction
{
    public function execute(StaticPage $staticPage): void
    {
        $staticPage->delete();
    }
}
