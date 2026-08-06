<?php

namespace App\Actions\Api\SpecialNote;

use App\Models\SpecialNote;
use Illuminate\Database\Eloquent\Collection;

class GetSpecialNotesAction
{
    public function run(): Collection
    {
        return SpecialNote::all();
    }
}
