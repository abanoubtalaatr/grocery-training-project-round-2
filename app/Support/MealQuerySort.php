<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class MealQuerySort
{
    public const ALLOWED_FIELDS = ['created_at', 'price', 'rating', 'title', 'sold_count'];

    public static function apply(Builder $query, string $sortBy, string $sortOrder): Builder
    {
        if ($sortBy === 'price') {
            return $query->orderByRaw('COALESCE(discount_price, price) ' . $sortOrder);
        }

        return $query->orderBy($sortBy, $sortOrder);
    }
}