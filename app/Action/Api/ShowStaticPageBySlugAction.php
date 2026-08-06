<?php

namespace App\Action\Api;

use App\Models\StaticPage;

class ShowStaticPageBySlugAction
{
    public function execute(string $slug, $user = null)
    {
        $page = StaticPage::bySlug($slug)->first();

        if (! $page) {
            return null;
        }

        if (! $page->is_published && (! $user || ! ($user->is_admin ?? false))) {
            return null;
        }

        return $page;
    }
}
