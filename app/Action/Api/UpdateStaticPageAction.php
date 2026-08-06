<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class UpdateStaticPageAction
{
    public function execute(StaticPage $staticPage, array $data): StaticPage
    {
        $staticPage->update($data);

        return $staticPage;
    }
}
