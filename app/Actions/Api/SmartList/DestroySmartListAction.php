<?php

namespace App\Actions\Api\SmartList;

use App\Models\SmartList;
use App\Traits\Media;
use Illuminate\Support\Facades\Auth;

class DestroySmartListAction
{
    use Media;

    public function run(int $id): void
    {
        $smartList = SmartList::where('user_id', Auth::id())
            ->findOrFail($id);

        $smartList->meals()->detach();

        if ($smartList->image) {
            $this->deletePhoto($smartList->image);
        }

        $smartList->delete();
    }
}