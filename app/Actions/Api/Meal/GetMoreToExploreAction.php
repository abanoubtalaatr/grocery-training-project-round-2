<?php

namespace App\Actions\Api\Meal;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;

class GetMoreToExploreAction
{
    public function run(): Collection
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
