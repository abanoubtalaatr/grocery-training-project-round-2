<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'users' => [
                'total' => $this->resource['users']['total'],
                'new_this_month' => $this->resource['users']['new_this_month'],
                'growth_percentage' => $this->resource['users']['growth_percentage'],
            ],
            'orders' => [
                'total' => $this->resource['orders']['total'],
                'this_month' => $this->resource['orders']['this_month'],
                'growth_percentage' => $this->resource['orders']['growth_percentage'],
            ],
            'revenue' => [
                'total' => $this->resource['revenue']['total'],
                'this_month' => $this->resource['revenue']['this_month'],
                'growth_percentage' => $this->resource['revenue']['growth_percentage'],
            ],
            'products' => $this->resource['products'],
            'pending_items' => $this->resource['pending_items'],
        ];
    }
}
