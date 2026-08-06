<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class UpdateStaticPageAction
{
    public function execute(StaticPage $staticPage, array $data): void
    {
        $staticPage->update($data);
    }
}