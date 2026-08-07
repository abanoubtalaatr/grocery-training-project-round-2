<?php

namespace App\Actions\Admin;

use App\Models\Meal;
use Illuminate\Support\Arr;

class MealUpdateAction
{
    public function execute(Meal $meal, array $data): Meal
    {
        $meal->update(Arr::except($data, []));

        return $meal->refresh();
    }
}
