<?php

namespace App\Actions\Admin;

use App\Models\Meal;

class MealDeleteAction
{
    public function execute(Meal $meal): void
    {
        $meal->delete();
    }
}
