<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;

class GetTodayDealsAction
{
    public function run(): Collection
    {
        return Meal::with('category')
            ->available()
            ->withActiveDiscount()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
