<?php

namespace App\Action\Api;

use App\Models\Faq;

class ListFaqsAction
{
    public function execute(array $params = [], int $perPage = 15)
    {
        $query = Faq::query();

        if (! empty($params['category'])) {
            $query->where('category', $params['category']);
        }

        if ($params['active_only'] ?? true) {
            $query->active();
        }

        if (! empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $query->ordered();

        return $query->paginate($perPage);
    }
}
