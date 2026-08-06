<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Support\Collection;

class GetMealBrandsAction
{
    public function run(): Collection
    {
        return Meal::distinct()->pluck('brand');
    }
}
