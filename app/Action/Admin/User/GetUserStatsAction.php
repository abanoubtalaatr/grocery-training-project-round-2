<?php

namespace App\Action\Admin\User;

use App\Models\User;
use Carbon\Carbon;

class GetUserStatsAction
{
    public function execute(): array
    {
        $total = User::count();
        $active = User::whereNotNull('email_verified_at')->count();
        $inactive = User::whereNull('email_verified_at')->count();

        $thisMonth = Carbon::now()->startOfMonth();
        $newThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'new_this_month' => $newThisMonth,
        ];
    }
}
