<?php

namespace App\Http\Controllers\Api\Faq;

use App\Actions\Api\Faq\GetFaqCategoriesAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetFaqCategoriesController extends Controller
{
    use ApiTrait;

    public function __invoke(GetFaqCategoriesAction $action): JsonResponse
    {
        $categories = $action->run();

        return $this->dataResponse($categories);
    }
}
