<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class UserIndexAction
{
    public function execute(Request $request): array
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('is_admin')) {
            $isAdmin = $request->boolean('is_admin');
            $query->where('is_admin', $isAdmin);
        }

        if ($request->filled('email_verified')) {
            $emailVerified = $request->boolean('email_verified');
            $query->where('email_verified', $emailVerified);
        }

        if ($request->filled('phone_verified')) {
            $phoneVerified = $request->boolean('phone_verified');
            $query->where('phone_verified', $phoneVerified);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'username') {
            $query->orderBy('username', $sortOrder);
        } elseif ($sortBy === 'email') {
            $query->orderBy('email', $sortOrder);
        } elseif ($sortBy === 'loyalty_points') {
            $query->orderBy('loyalty_points', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $users = $query->paginate(15)->withQueryString();

        return compact('users');
    }
}
