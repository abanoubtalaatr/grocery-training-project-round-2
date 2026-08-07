<?php

namespace App\Actions\Admin\Meal;

use App\Models\Meal;

class DestroyMealAction
{
    public function run(Meal $meal): void
    {
        $meal->delete(); // Soft delete (uses SoftDeletes trait)
    }
}
