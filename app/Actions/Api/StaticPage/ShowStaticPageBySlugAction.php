<?php

namespace App\Actions\Api\StaticPage;

use App\Models\StaticPage;

class ShowStaticPageBySlugAction
{
    public function run(string $slug): ?StaticPage
    {
        $page = StaticPage::bySlug($slug)->first();

        if (! $page) {
            return null;
        }

        if (! $page->is_published && (! request()->user() || ! request()->user()->is_admin)) {
            return null;
        }

        return $page;
    }
}
