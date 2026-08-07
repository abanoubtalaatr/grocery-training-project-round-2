<?php

namespace App\Actions\Admin\Support;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexSupportAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = ContactMessage::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->paginate(20)->withQueryString();
    }
}
