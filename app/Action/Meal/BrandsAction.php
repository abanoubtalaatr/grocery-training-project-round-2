<?php

namespace App\Action\Meal;

use App\Models\Meal;

class BrandsAction
{
    public function handle()
    {
        return Meal::distinct()->pluck('brand');
    }
}
