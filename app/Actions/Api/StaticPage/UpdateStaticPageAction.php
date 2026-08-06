<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;

class UpdateStaticPageAction
{
    public function run(StaticPage $staticPage, array $data): StaticPage
    {
        $staticPage->update($data);

        return $staticPage;
    }
}
