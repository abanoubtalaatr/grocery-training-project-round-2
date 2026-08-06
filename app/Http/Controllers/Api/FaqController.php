<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Faq\DestroyFaqAction;
use App\Actions\Api\Faq\GetFaqsAction;
use App\Actions\Api\Faq\StoreFaqAction;
use App\Actions\Api\Faq\UpdateFaqAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFaqRequest;
use App\Http\Requests\Api\UpdateFaqRequest;
use App\Http\Resources\FaqCollection;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetFaqsAction $action): JsonResponse
    {
        $result = $action->run($request);

        $response = [
            'faqs' => new FaqCollection($result['faqs']),
        ];

        if ($result['categories'] !== null) {
            $response['categories'] = $result['categories'];
        }

        return $this->dataResponse($response);
    }

    public function store(StoreFaqRequest $request, StoreFaqAction $action): JsonResponse
    {
        $faq = $action->run($request->validated());

        return $this->dataResponse(
            new FaqResource($faq),
            'FAQ created successfully',
            201
        );
    }

    public function show(Faq $faq): JsonResponse
    {
        return $this->dataResponse(new FaqResource($faq));
    }

    public function update(UpdateFaqRequest $request, Faq $faq, UpdateFaqAction $action): JsonResponse
    {
        $faq = $action->run($faq, $request->validated());

        return $this->dataResponse(
            new FaqResource($faq),
            'FAQ updated successfully'
        );
    }

    public function destroy(Faq $faq, DestroyFaqAction $action): JsonResponse
    {
        $action->run($faq);

        return $this->successResponse('FAQ deleted successfully');
    }
}
