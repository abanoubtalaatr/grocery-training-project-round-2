<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;

class GetHotMealsAction
{
    public function run(): Collection
    {
        return Meal::with('category')
            ->available()
            ->hot()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
