<?php

namespace App\Http\Controllers\Api\StaticPage;

use App\Actions\Api\StaticPage\ShowStaticPageBySlugAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class ShowStaticPageBySlugController extends Controller
{
    use ApiTrait;

    public function __invoke(string $slug, ShowStaticPageBySlugAction $action): JsonResponse
    {
        $page = $action->run($slug);

        if (! $page) {
            return $this->errorResponse([], 'Page not found', 404);
        }

        return $this->dataResponse(new StaticPageResource($page));
    }
}
