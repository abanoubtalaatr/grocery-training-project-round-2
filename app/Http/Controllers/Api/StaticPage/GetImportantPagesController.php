<?php

namespace App\Http\Controllers\Api\StaticPage;

use App\Actions\Api\StaticPage\GetImportantPagesAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetImportantPagesController extends Controller
{
    use ApiTrait;

    public function __invoke(GetImportantPagesAction $action): JsonResponse
    {
        $pages = $action->run();

        return $this->dataResponse($pages);
    }
}
