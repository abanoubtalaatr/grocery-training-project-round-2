<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Support\Collection;

class GetMealRecommendationsAction
{
    public function run(int $limit): Collection
    {
        $featuredMeals = Meal::with('category')
            ->available()
            ->featured()
            ->whereNotNull('discount_price')
            ->inRandomOrder()
            ->limit((int) ceil($limit / 2))
            ->get();

        $randomMeals = Meal::with('category')
            ->available()
            ->whereNotIn('id', $featuredMeals->pluck('id'))
            ->inRandomOrder()
            ->limit($limit - $featuredMeals->count())
            ->get();

        $recommendations = $featuredMeals->merge($randomMeals)->shuffle()->take($limit);

        return $recommendations->map(function ($meal) {
            $meal->recommendation_reason = $this->getRecommendationReason($meal);
            return $meal;
        });
    }

    private function getRecommendationReason($meal): string
    {
        if ($meal->is_featured && $meal->discount_price) {
            return 'Featured with special offer';
        }

        if ($meal->is_featured) {
            return 'Featured meal';
        }

        if ($meal->discount_price) {
            return 'Special offer';
        }

        return 'Popular choice';
    }
}
