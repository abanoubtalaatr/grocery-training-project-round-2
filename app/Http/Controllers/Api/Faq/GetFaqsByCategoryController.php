<?php

namespace App\Http\Controllers\Api\Faq;

use App\Actions\Api\Faq\GetFaqsByCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetFaqsByCategoryController extends Controller
{
    use ApiTrait;

    public function __invoke(string $category, GetFaqsByCategoryAction $action): JsonResponse
    {
        $faqs = $action->run($category);

        return $this->dataResponse(FaqResource::collection($faqs));
    }
}
