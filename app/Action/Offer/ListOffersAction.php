<?php

namespace App\Action\Offer;

use App\Models\Offer;

class ListOffersAction
{
    public function handle(array $filters)
    {
        $query = Offer::active();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['min_purchase'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('minimum_purchase', '<=', $filters['min_purchase'])
                  ->orWhereNull('minimum_purchase');
            });
        }

        if (!empty($filters['featured'])) {
            $query->featured();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';

        $query->orderBy($orderBy, $orderDirection);

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 50);

        return $query->paginate($perPage);
    }
}
