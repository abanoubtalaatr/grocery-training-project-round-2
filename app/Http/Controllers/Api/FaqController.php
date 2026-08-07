<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FaqCollection;
use App\Http\Resources\Api\FaqResource;
use App\Models\Faq;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * Get all FAQs with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        $categories = null;
        if ($request->boolean('with_categories', false)) {
            $categories = Faq::active()
                ->distinct('category')
                ->pluck('category')
                ->filter()
                ->values();
        }

        $perPage = $request->get('per_page', 15);
        $faqs = $query->paginate($perPage);

        $data = [
            'faqs' => new FaqCollection($faqs),
            'pagination' => [
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
            ],
        ];

        if ($categories !== null) {
            $data['categories'] = $categories;
        }

        return $this->success($data, 'FAQs retrieved successfully');
    }

    /**
     * Get single FAQ
     */
    public function show(Faq $faq): JsonResponse
    {
        return $this->success(
            new FaqResource($faq),
            'FAQ retrieved successfully'
        );
    }

    /**
     * Create new FAQ (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq = Faq::create($validated);

        return $this->success(
            new FaqResource($faq),
            'FAQ created successfully',
            201
        );
    }

    /**
     * Update FAQ (admin only)
     */
    public function update(Request $request, Faq $faq): JsonResponse
    {
        $this->authorize('update', $faq);

        $validated = $request->validate([
            'question' => ['sometimes', 'required', 'string', 'max:255'],
            'answer' => ['sometimes', 'required', 'string'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $faq->update($validated);

        return $this->success(
            new FaqResource($faq),
            'FAQ updated successfully'
        );
    }

    /**
     * Delete FAQ (admin only)
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $faq->delete();

        return $this->success(null, 'FAQ deleted successfully');
    }
}
