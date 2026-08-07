<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetBrandsAction
{
    public function execute()
    {
        return Meal::distinct()->pluck('brand');
    }
}