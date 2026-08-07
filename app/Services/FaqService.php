<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class FaqService
{
    /**
     * Get paginated FAQs based on filters.
     */
    public function getFilteredFaqs(array $filters): LengthAwarePaginator
    {
        $query = Faq::query();

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (filter_var($filters['active_only'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
            $query->active();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->ordered()->paginate($perPage);
    }

    /**
     * Create a new FAQ.
     */
    public function createFaq(array $data): Faq
    {
        return Faq::create($data);
    }

    /**
     * Update an existing FAQ.
     */
    public function updateFaq(Faq $faq, array $data): Faq
    {
        $faq->update($data);
        return $faq;
    }

    /**
     * Delete an FAQ.
     */
    public function deleteFaq(Faq $faq): bool
    {
        return $faq->delete();
    }

    /**
     * Get distinct active FAQ categories.
     */
    public function getCategories(): SupportCollection
    {
        return Faq::active()
            ->distinct('category')
            ->pluck('category')
            ->filter()
            ->values();
    }

    /**
     * Get active FAQs by category.
     */
    public function getByCategory(string $category): Collection
    {
        return Faq::active()
            ->category($category)
            ->ordered()
            ->get();
    }
}