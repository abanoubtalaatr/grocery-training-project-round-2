<?php

namespace App\Actions\Offer;

use App\Models\Offer;
use Illuminate\Http\Request;

class GetOffersAction
{
    public function execute(Request $request)
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

        return $query->paginate($request->get('per_page', 15));
    }
}