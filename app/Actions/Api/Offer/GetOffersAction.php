<?php

namespace App\Actions\Api\Offer;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetOffersAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = Offer::active();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_purchase')) {
            $query->where('minimum_purchase', '<=', $request->min_purchase)
                ->orWhereNull('minimum_purchase');
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $perPage = (int) $request->get('per_page', 15);

        return $query->paginate($perPage);
    }
}
