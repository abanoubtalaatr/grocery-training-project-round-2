<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;

class GetBestSellsAction
{
    public function run(): Collection
    {
        return Meal::with('category')
            ->available()
            ->take(10)
            ->get();
    }
}
