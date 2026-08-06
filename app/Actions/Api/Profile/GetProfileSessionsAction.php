<?php

namespace App\Actions\Api\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetProfileSessionsAction
{
    public function run(User $user): Collection
    {
        return $user->tokens()->get();
    }
}
