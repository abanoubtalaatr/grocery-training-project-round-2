<?php

namespace App\Actions\Admin\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexUserAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = User::withCount(['orders'])
            ->withSum('orders', 'total');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            if ($role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($role === 'customer') {
                $query->where('is_admin', false);
            }
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
    }
}
