<?php

namespace App\Actions\Api\SmartLists;

use App\Models\SmartList;
use Illuminate\Support\Facades\DB;

class DestroySmartListAction
{
    public function execute(SmartList $smartList): void
    {
        DB::transaction(function () use ($smartList) {
            $smartList->meals()->detach();
            $smartList->delete();
        });
    }
}
