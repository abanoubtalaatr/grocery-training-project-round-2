<?php

namespace App\Actions\Admin;

use App\Models\Meal;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class MealStoreAction
{
    public function execute(array $data): Meal
    {
        // Accept array of validated data
        $meal = Meal::create(Arr::except($data, []));

        return $meal;
    }
}
