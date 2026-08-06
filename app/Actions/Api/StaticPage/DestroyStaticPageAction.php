<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;

class DestroyStaticPageAction
{
    public function run(StaticPage $staticPage): bool
    {
        return (bool) $staticPage->delete();
    }
}
